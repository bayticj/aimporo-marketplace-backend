<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Gig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GigController extends Controller
{
    /**
     * Display a listing of gigs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Gig::query()->with('user')->where('is_active', true);
        
        // Apply filters if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        $gigs = $query->paginate(12);
        
        return response()->json([
            'gigs' => $gigs,
        ]);
    }

    /**
     * Store a newly created gig.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'subcategory' => 'nullable|string',
            'price' => 'required|numeric|min:1',
            'delivery_time' => 'required|integer|min:1',
            'requirements' => 'nullable|string',
            'location' => 'nullable|string',
            'thumbnail' => 'nullable|string',
            'images' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $gig = new Gig($request->all());
        $gig->user_id = Auth::id();
        $gig->save();

        return response()->json([
            'message' => 'Gig created successfully',
            'gig' => $gig
        ], 201);
    }

    /**
     * Display the specified gig.
     *
     * @param  \App\Models\Gig  $gig
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Gig $gig)
    {
        $gig->load('user');
        
        return response()->json([
            'gig' => $gig
        ]);
    }

    /**
     * Update the specified gig.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Gig  $gig
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Gig $gig)
    {
        // Check if the authenticated user owns the gig
        if ($gig->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category' => 'sometimes|required|string',
            'subcategory' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:1',
            'delivery_time' => 'sometimes|required|integer|min:1',
            'requirements' => 'nullable|string',
            'location' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'thumbnail' => 'nullable|string',
            'images' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $gig->update($request->all());

        return response()->json([
            'message' => 'Gig updated successfully',
            'gig' => $gig
        ]);
    }

    /**
     * Remove the specified gig.
     *
     * @param  \App\Models\Gig  $gig
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Gig $gig)
    {
        // Check if the authenticated user owns the gig
        if ($gig->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $gig->delete();

        return response()->json([
            'message' => 'Gig deleted successfully'
        ]);
    }
}
