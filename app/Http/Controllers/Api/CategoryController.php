<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories
     */
    public function index()
    {
        try {
            $categories = Category::getWithStats()
                                ->map(function($category) {
                                    return $category->toApiArray();
                                });

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get only active categories
     */
    public function getActive()
    {
        try {
            $categories = Category::getActiveCategories()
                                ->map(function($category) {
                                    return $category->toApiArray();
                                });

            return response()->json([
                'success' => true,
                'message' => 'Active categories retrieved successfully',
                'data' => $categories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve active categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific category by slug
     */
    public function show($slug)
    {
        try {
            $category = Category::findBySlug($slug);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Category retrieved successfully',
                'data' => $category->toApiArray()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category statistics
     */
    public function getStats($slug)
    {
        try {
            $category = Category::findBySlug($slug);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $stats = [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
                'swipes' => [
                    'total' => $category->getTotalSwipes(),
                    'likes' => $category->getTotalLikes(),
                    'passes' => $category->getTotalPasses(),
                    'like_percentage' => $category->getLikePercentage(),
                ],
                'connections' => [
                    'total' => $category->getTotalConnections(),
                    'connection_rate' => $category->getConnectionRate(),
                ],
                'activity' => [
                    'active_users_week' => $category->getActiveUsers(7)->count(),
                    'active_users_month' => $category->getActiveUsers(30)->count(),
                    'is_popular' => $category->isPopular(),
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Category statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}