<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use Illuminate\Http\Request;

class InterestController extends Controller
{
    /**
     * Get all interests
     */
    public function index()
    {
        try {
            $interests = Interest::withUserCount()
                                ->orderBy('name')
                                ->get()
                                ->map(function($interest) {
                                    return $interest->toApiArray();
                                });

            return response()->json([
                'success' => true,
                'message' => 'Interests retrieved successfully',
                'data' => $interests
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve interests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get interests grouped by category
     */
    public function getByCategories()
    {
        try {
            $groupedInterests = Interest::getGroupedByCategory();
            
            $result = [];
            foreach ($groupedInterests as $category => $interests) {
                $result[] = [
                    'category' => $category,
                    'category_display' => ucfirst($category ?: 'Other'),
                    'interests' => $interests->map(function($interest) {
                        return $interest->toApiArray();
                    })
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Interests by categories retrieved successfully',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve interests by categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get popular interests
     */
    public function getPopular(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            
            $popularInterests = Interest::popular($limit)
                                      ->get()
                                      ->map(function($interest) {
                                          return $interest->toApiArray();
                                      });

            return response()->json([
                'success' => true,
                'message' => 'Popular interests retrieved successfully',
                'data' => $popularInterests
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve popular interests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search interests by name
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required'
                ], 422);
            }

            $interests = Interest::search($query)
                               ->map(function($interest) {
                                   return $interest->toApiArray();
                               });

            return response()->json([
                'success' => true,
                'message' => 'Search results retrieved successfully',
                'data' => $interests,
                'query' => $query
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}