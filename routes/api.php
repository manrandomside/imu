<?php

// use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\InterestController;
// use App\Http\Controllers\Api\CategoryController;
// use App\Http\Controllers\Api\SwipeController;
// use App\Http\Controllers\Api\ConnectionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ========================================
// SIMPLE TEST ROUTES
// ========================================
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working!',
        'app' => 'I MATCH U'
    ]);
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'env' => app()->environment()
    ]);
});

Route::get('/user', function (Request $request) {
    return response()->json([
        'message' => 'User endpoint works',
        'method' => 'GET'
    ]);
});

/*
// ========================================
// AUTHENTICATION ROUTES (Public) - COMMENTED OUT
// ========================================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// ========================================
// PROTECTED ROUTES (Require Authentication) - COMMENTED OUT
// ========================================
Route::middleware('auth:sanctum')->group(function () {

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::delete('/account', [AuthController::class, 'deleteAccount']);
    });

    // Get current user
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()->toApiArray()
        ]);
    });

    // Interests routes
    Route::prefix('interests')->group(function () {
        Route::get('/', [InterestController::class, 'index']); // Get all interests
        Route::get('/categories', [InterestController::class, 'getByCategories']); // Get interests grouped by category
        Route::get('/popular', [InterestController::class, 'getPopular']); // Get popular interests
        Route::get('/search', [InterestController::class, 'search']); // Search interests
    });

    // Categories routes (for swipe categories: Friends, Jobs, PKM, etc.)
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']); // Get all categories
        Route::get('/active', [CategoryController::class, 'getActive']); // Get active categories only
        Route::get('/{category}', [CategoryController::class, 'show']); // Get specific category
        Route::get('/{category}/stats', [CategoryController::class, 'getStats']); // Get category statistics
    });

    // Swipe routes
    Route::prefix('swipe')->group(function () {
        Route::get('/potential/{category}', [SwipeController::class, 'getPotentialMatches']); // Get users to swipe
        Route::post('/action', [SwipeController::class, 'swipeAction']); // Perform swipe (like/pass)
        Route::get('/history', [SwipeController::class, 'getSwipeHistory']); // Get user's swipe history
        Route::get('/statistics', [SwipeController::class, 'getSwipeStatistics']); // Get swipe stats
    });

    // Connection routes
    Route::prefix('connections')->group(function () {
        Route::get('/', [ConnectionController::class, 'getUserConnections']); // Get user's connections
        Route::get('/pending', [ConnectionController::class, 'getPendingConnections']); // Get pending connections
        Route::get('/category/{category}', [ConnectionController::class, 'getConnectionsByCategory']); // Get connections by category
        Route::post('/{connection}/accept', [ConnectionController::class, 'acceptConnection']); // Accept connection
        Route::post('/{connection}/block', [ConnectionController::class, 'blockConnection']); // Block connection
        Route::get('/statistics', [ConnectionController::class, 'getConnectionStatistics']); // Get connection stats
    });

    // Chat routes (coming soon)
    Route::prefix('chat')->group(function () {
        // Routes akan ditambahkan nanti untuk chat functionality
    });

    // Community routes (coming soon)
    Route::prefix('community')->group(function () {
        // Routes akan ditambahkan nanti untuk community features
    });
});
*/

// ========================================
// FALLBACK ROUTE
// ========================================
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found'
    ], 404);
});