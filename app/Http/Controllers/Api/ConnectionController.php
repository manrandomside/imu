<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Connection;
use App\Models\Category;
use Illuminate\Http\Request;

class ConnectionController extends Controller
{
    /**
     * Get user's connections
     */
    public function getUserConnections(Request $request)
    {
        try {
            $user = $request->user();
            $status = $request->get('status'); // 'accepted', 'pending', 'blocked'
            $categoryId = $request->get('category_id');
            $limit = $request->get('limit', 20);

            $connections = Connection::getUserConnections($user->id, $categoryId, $status);

            if ($limit) {
                $connections = $connections->take($limit);
            }

            $connectionsData = $connections->map(function($connection) use ($user) {
                return $connection->toApiArray($user->id);
            });

            return response()->json([
                'success' => true,
                'message' => 'Connections retrieved successfully',
                'data' => $connectionsData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve connections',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending connections
     */
    public function getPendingConnections(Request $request)
    {
        try {
            $user = $request->user();

            $pendingConnections = Connection::getUserConnections($user->id, null, 'pending');

            $connectionsData = $pendingConnections->map(function($connection) use ($user) {
                return $connection->toApiArray($user->id);
            });

            return response()->json([
                'success' => true,
                'message' => 'Pending connections retrieved successfully',
                'data' => $connectionsData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pending connections',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get connections by category
     */
    public function getConnectionsByCategory(Request $request, $categorySlug)
    {
        try {
            $user = $request->user();

            // Find category
            $category = Category::findBySlug($categorySlug);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $connections = Connection::getUserConnections($user->id, $category->id);

            $connectionsData = $connections->map(function($connection) use ($user) {
                return $connection->toApiArray($user->id);
            });

            return response()->json([
                'success' => true,
                'message' => 'Connections by category retrieved successfully',
                'data' => [
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ],
                    'connections' => $connectionsData,
                    'total_count' => $connectionsData->count(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve connections by category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept a connection
     */
    public function acceptConnection(Request $request, $connectionId)
    {
        try {
            $user = $request->user();
            $connection = Connection::find($connectionId);

            if (!$connection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection not found'
                ], 404);
            }

            // Check if user is part of this connection
            if (!$connection->involvesUser($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to modify this connection'
                ], 403);
            }

            // Accept the connection
            $connection->accept();

            return response()->json([
                'success' => true,
                'message' => 'Connection accepted successfully',
                'data' => $connection->toApiArray($user->id)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept connection',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Block a connection
     */
    public function blockConnection(Request $request, $connectionId)
    {
        try {
            $user = $request->user();
            $connection = Connection::find($connectionId);

            if (!$connection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection not found'
                ], 404);
            }

            // Check if user is part of this connection
            if (!$connection->involvesUser($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to modify this connection'
                ], 403);
            }

            // Block the connection
            $connection->block();

            return response()->json([
                'success' => true,
                'message' => 'Connection blocked successfully',
                'data' => $connection->toApiArray($user->id)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to block connection',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get connection statistics
     */
    public function getConnectionStatistics(Request $request)
    {
        try {
            $user = $request->user();

            // Get all user's connections
            $allConnections = $user->allConnections();

            // Count by status
            $acceptedCount = $allConnections->where('status', 'accepted')->count();
            $pendingCount = $allConnections->where('status', 'pending')->count();
            $blockedCount = $allConnections->where('status', 'blocked')->count();
            $totalCount = $allConnections->count();

            // Group by category
            $connectionsByCategory = $allConnections->groupBy('category.name')
                                                   ->map(function($categoryConnections) {
                                                       return [
                                                           'total' => $categoryConnections->count(),
                                                           'accepted' => $categoryConnections->where('status', 'accepted')->count(),
                                                           'pending' => $categoryConnections->where('status', 'pending')->count(),
                                                           'blocked' => $categoryConnections->where('status', 'blocked')->count(),
                                                       ];
                                                   });

            // Recent connections
            $recentConnections = $allConnections->where('connected_at', '>=', now()->subWeek())->count();
            $monthlyConnections = $allConnections->where('connected_at', '>=', now()->subMonth())->count();

            // Average match score
            $averageMatchScore = $allConnections->where('match_score', '>', 0)->avg('match_score');

            $stats = [
                'overall' => [
                    'total_connections' => $totalCount,
                    'accepted_connections' => $acceptedCount,
                    'pending_connections' => $pendingCount,
                    'blocked_connections' => $blockedCount,
                    'acceptance_rate' => $totalCount > 0 ? round(($acceptedCount / $totalCount) * 100, 1) : 0,
                ],
                'by_category' => $connectionsByCategory,
                'activity' => [
                    'connections_this_week' => $recentConnections,
                    'connections_this_month' => $monthlyConnections,
                ],
                'match_quality' => [
                    'average_match_score' => $averageMatchScore ? round($averageMatchScore, 2) : 0,
                    'high_quality_matches' => $allConnections->where('match_score', '>=', 0.7)->count(),
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Connection statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve connection statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}