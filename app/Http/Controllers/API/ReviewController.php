<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Get reviews for a specific gig.
     *
     * @param  string  $gigId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGigReviews(string $gigId)
    {
        $reviews = Review::where('gig_id', $gigId)
            ->with(['reviewer', 'reviewee'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json([
            'reviews' => $reviews
        ]);
    }
    
    /**
     * Get reviews for a specific user.
     *
     * @param  string  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserReviews(string $userId)
    {
        $reviews = Review::where('reviewee_id', $userId)
            ->with(['reviewer', 'gig'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json([
            'reviews' => $reviews
        ]);
    }
    
    /**
     * Create a review for a completed order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function createReview(Request $request, string $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        // Check if the order is completed
        if (!$order->is_completed) {
            return response()->json([
                'message' => 'Cannot review an incomplete order'
            ], 400);
        }
        
        // Check if the user is authorized to review this order
        $user = Auth::user();
        if ($user->id !== $order->buyer_id) {
            return response()->json([
                'message' => 'Only buyers can leave reviews'
            ], 403);
        }
        
        // Check if a review already exists for this order
        $existingReview = Review::where('order_id', $orderId)->first();
        if ($existingReview) {
            return response()->json([
                'message' => 'A review already exists for this order'
            ], 400);
        }
        
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $review = new Review();
        $review->order_id = $orderId;
        $review->gig_id = $order->gig_id;
        $review->reviewer_id = $user->id;
        $review->reviewee_id = $order->seller_id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->save();
        
        // Update the gig's average rating
        $this->updateGigAverageRating($order->gig_id);
        
        return response()->json([
            'message' => 'Review created successfully',
            'review' => $review->load(['reviewer', 'reviewee'])
        ], 201);
    }
    
    /**
     * Update a review.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $reviewId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateReview(Request $request, string $reviewId)
    {
        $review = Review::findOrFail($reviewId);
        
        // Check if the user is authorized to update this review
        $user = Auth::user();
        if ($user->id !== $review->reviewer_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'sometimes|required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        if ($request->has('rating')) {
            $review->rating = $request->rating;
        }
        
        if ($request->has('comment')) {
            $review->comment = $request->comment;
        }
        
        $review->save();
        
        // Update the gig's average rating
        $this->updateGigAverageRating($review->gig_id);
        
        return response()->json([
            'message' => 'Review updated successfully',
            'review' => $review->load(['reviewer', 'reviewee'])
        ]);
    }
    
    /**
     * Delete a review.
     *
     * @param  string  $reviewId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteReview(string $reviewId)
    {
        $review = Review::findOrFail($reviewId);
        
        // Check if the user is authorized to delete this review
        $user = Auth::user();
        if ($user->id !== $review->reviewer_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $gigId = $review->gig_id;
        $review->delete();
        
        // Update the gig's average rating
        $this->updateGigAverageRating($gigId);
        
        return response()->json([
            'message' => 'Review deleted successfully'
        ]);
    }
    
    /**
     * Update the average rating for a gig.
     *
     * @param  string  $gigId
     * @return void
     */
    private function updateGigAverageRating(string $gigId)
    {
        $averageRating = Review::where('gig_id', $gigId)->avg('rating');
        $gig = \App\Models\Gig::find($gigId);
        $gig->average_rating = $averageRating ?? 0;
        $gig->save();
    }
} 