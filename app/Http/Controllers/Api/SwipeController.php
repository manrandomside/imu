<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Category;
use App\Models\Swipe;
use App\Models\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SwipeController extends Controller
{
    /**
     * Get potential matches for swiping in a specific category
     */
    public function getPotentialMatches(Request $request, $categorySlug)
    {
        try {
            $user = $request->user();
            $limit = $request->get('limit', 10);

            // Find category
            $category = Category::findBySlug($categorySlug);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            // Get potential matches
            $potentialMatches = $user->getPotentialMatches($category->id, $limit);

            // Format response with match scores
            $matches = $potentialMatches->map(function($match) use ($user) {
                return [
                    'user' => $match->toApiArray(),
                    'match_score' => $user->calculateMatchScore($match),
                    'common_interests' => $user->interests()->whereIn('interests.id', 
                        $match->interests()->pluck('interests.id'))->get()->map(function($interest) {
                            return [
                                'id' => $interest->id,
                                'name' => $interest->name,
                                'icon' => $interest->icon,
                            ];
                        }),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Potential matches retrieved successfully',
                'data' => [
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ],
                    'matches' => $matches,
                    'total_count' => $matches->count(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get potential matches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Perform swipe action (like or pass)
     */
    public function swipeAction(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'swiped_user_id' => 'required|exists:users,id',
                'category_id' => 'required|exists:categories,id',
                'action' => 'required|in:like,pass',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $swipedUser = User::find($data['swiped_user_id']);

            // Check if user is trying to swipe themselves
            if ($user->id === $swipedUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot swipe yourself'
                ], 422);
            }

            // Check if already swiped
            $existingSwipe = Swipe::where('swiper_id', $user->id)
                                  ->where('swiped_id', $swipedUser->id)
                                  ->where('category_id', $data['category_id'])
                                  ->first();

            if ($existingSwipe) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already swiped this user in this category'
                ], 422);
            }

            // Create swipe record
            $swipe = Swipe::create([
                'swiper_id' => $user->id,
                'swiped_id' => $swipedUser->id,
                'category_id' => $data['category_id'],
                'action' => $data['action'],
                'swiped_at' => now(),
            ]);

            $response = [
                'success' => true,
                'message' => 'Swipe action recorded successfully',
                'data' => [
                    'swipe' => [
                        'id' => $swipe->id,
                        'action' => $swipe->action,
                        'swiped_at' => $swipe->swiped_at,
                    ],
                    'is_match' => false,
                    'connection' => null,
                ]
            ];

            // Check for mutual like (match)
            if ($data['action'] === 'like') {
                $mutualSwipe = Swipe::where('swiper_id', $swipedUser->id)
                                   ->where('swiped_id', $user->id)
                                   ->where('category_id', $data['category_id'])
                                   ->where('action', 'like')
                                   ->first();

                if ($mutualSwipe) {
                    // Create connection
                    $connection = Connection::createConnection(
                        $user->id,
                        $swipedUser->id,
                        $data['category_id']
                    );

                    $response['data']['is_match'] = true;
                    $response['data']['connection'] = [
                        'id' => $connection->id,
                        'status' => $connection->status,
                        'match_score' => $connection->match_score,
                        'connected_at' => $connection->connected_at,
                        'other_user' => $swipedUser->toApiArray(),
                    ];
                    $response['message'] = 'It\'s a match! Connection created successfully';
                }
            }

            return response()->json($response, 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Swipe action failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's swipe history
     */
    public function getSwipeHistory(Request $request)
    {
        try {
            $user = $request->user();
            $categoryId = $request->get('category_id');
            $action = $request->get('action'); // 'like' or 'pass'
            $limit = $request->get('limit', 20);

            $query = $user->sentSwipes()->with(['swiped', 'category']);

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            if ($action) {
                $query->where('action', $action);
            }

            $swipes = $query->orderBy('swiped_at', 'desc')
                           ->limit($limit)
                           ->get()
                           ->map(function($swipe) {
                               return [
                                   'id' => $swipe->id,
                                   'action' => $swipe->action,
                                   'swiped_at' => $swipe->swiped_at,
                                   'category' => [
                                       'id' => $swipe->category->id,
                                       'name' => $swipe->category->name,
                                       'slug' => $swipe->category->slug,
                                   ],
                                   'swiped_user' => $swipe->swiped->toApiArray(),
                               ];
                           });

            return response()->json([
                'success' => true,
                'message' => 'Swipe history retrieved successfully',
                'data' => $swipes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve swipe history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's swipe statistics
     */
    public function getSwipeStatistics(Request $request)
    {
        try {
            $user = $request->user();

            $totalSwipes = $user->sentSwipes()->count();
            $totalLikes = $user->sentSwipes()->where('action', 'like')->count();
            $totalPasses = $user->sentSwipes()->where('action', 'pass')->count();

            // Calculate like percentage
            $likePercentage = $totalSwipes > 0 ? round(($totalLikes / $totalSwipes) * 100, 1) : 0;

            // Get swipes by category
            $swipesByCategory = $user->sentSwipes()
                                   ->selectRaw('category_id, action, COUNT(*) as count')
                                   ->with('category')
                                   ->groupBy('category_id', 'action')
                                   ->get()
                                   ->groupBy('category.name')
                                   ->map(function($categorySwipes) {
                                       $result = ['likes' => 0, 'passes' => 0, 'total' => 0];
                                       foreach ($categorySwipes as $swipe) {
                                           $result[$swipe->action . 's'] = $swipe->count;
                                           $result['total'] += $swipe->count;
                                       }
                                       return $result;
                                   });

            // Get total connections created from swipes
            $totalConnections = $user->allConnections()->count();
            $connectionRate = $totalLikes > 0 ? round(($totalConnections / $totalLikes) * 100, 1) : 0;

            $stats = [
                'overall' => [
                    'total_swipes' => $totalSwipes,
                    'total_likes' => $totalLikes,
                    'total_passes' => $totalPasses,
                    'like_percentage' => $likePercentage,
                    'total_connections' => $totalConnections,
                    'connection_rate' => $connectionRate,
                ],
                'by_category' => $swipesByCategory,
                'recent_activity' => [
                    'swipes_last_week' => $user->sentSwipes()
                        ->where('swiped_at', '>=', now()->subWeek())->count(),
                    'swipes_last_month' => $user->sentSwipes()
                        ->where('swiped_at', '>=', now()->subMonth())->count(),
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Swipe statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve swipe statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}