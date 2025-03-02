<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\GigController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\AuditController;

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

// CORS preflight
Route::options('/{any}', function() {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization');
})->where('any', '.*');

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Gig routes - public access for viewing
Route::get('/gigs', [GigController::class, 'index']);
Route::get('/gigs/{gig}', [GigController::class, 'show']);

// Public review routes
Route::get('/reviews/gig/{gigId}', [ReviewController::class, 'getGigReviews']);
Route::get('/reviews/user/{userId}', [ReviewController::class, 'getUserReviews']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Gig management - requires create_gig permission
    Route::post('/gigs', [GigController::class, 'store'])->middleware('permission:create_gig');
    
    // Gig owner can update and delete their own gigs (controller should verify ownership)
    Route::put('/gigs/{gig}', [GigController::class, 'update'])->middleware('permission:edit_gig');
    Route::delete('/gigs/{gig}', [GigController::class, 'destroy'])->middleware('permission:delete_gig');
    
    // Order management
    Route::resource('/orders', OrderController::class);
    
    // Message system
    Route::get('/messages/conversations', [MessageController::class, 'getConversations']);
    Route::get('/messages/unread-count', [MessageController::class, 'getUnreadCount']);
    Route::get('/messages/order/{orderId}', [MessageController::class, 'getOrderMessages']);
    Route::post('/messages/order/{orderId}', [MessageController::class, 'sendMessage']);
    Route::put('/messages/{messageId}/read', [MessageController::class, 'markAsRead']);
    
    // Review system
    Route::post('/reviews/order/{orderId}', [ReviewController::class, 'createReview'])->middleware('permission:create_review');
    Route::put('/reviews/{reviewId}', [ReviewController::class, 'updateReview'])->middleware('permission:edit_review');
    Route::delete('/reviews/{reviewId}', [ReviewController::class, 'deleteReview'])->middleware('permission:delete_review');
    
    // Audit routes - only accessible by admin and moderator roles
    Route::middleware('role:admin,moderator')->group(function () {
        Route::get('/audits', [AuditController::class, 'index']);
        Route::get('/audits/user', [AuditController::class, 'userAudits']);
        Route::get('/audits/{model}/{id}', [AuditController::class, 'modelAudits']);
    });
    
    // Admin-only routes
    Route::middleware('role:admin')->group(function () {
        // User management routes
        Route::get('/admin/users', [AuthController::class, 'listUsers']);
        Route::get('/admin/users/{user}', [AuthController::class, 'showUser']);
        Route::put('/admin/users/{user}/role', [AuthController::class, 'updateUserRole']);
        Route::delete('/admin/users/{user}', [AuthController::class, 'deleteUser']);
        
        // System statistics
        Route::get('/admin/stats', [AuthController::class, 'systemStats']);
    });
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
}); 