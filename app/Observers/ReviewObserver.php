<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\Gig;

class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        $this->updateGigRating($review->gig_id);
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {
        $this->updateGigRating($review->gig_id);
    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
        $this->updateGigRating($review->gig_id);
    }

    /**
     * Handle the Review "restored" event.
     */
    public function restored(Review $review): void
    {
        $this->updateGigRating($review->gig_id);
    }

    /**
     * Handle the Review "force deleted" event.
     */
    public function forceDeleted(Review $review): void
    {
        $this->updateGigRating($review->gig_id);
    }

    /**
     * Update the average rating of a gig.
     */
    private function updateGigRating(int $gigId): void
    {
        $gig = Gig::findOrFail($gigId);
        
        $averageRating = $gig->reviews()
            ->where('is_public', true)
            ->avg('rating');
            
        $reviewsCount = $gig->reviews()
            ->where('is_public', true)
            ->count();
            
        $gig->update([
            'average_rating' => $averageRating ?? 0,
            'reviews_count' => $reviewsCount,
        ]);
    }
} 