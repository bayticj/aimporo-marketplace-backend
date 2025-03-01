<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Gig;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Order::query();
        
        // Filter by role (buyer or seller)
        if ($request->has('role') && $request->role === 'buyer') {
            $query->where('buyer_id', $user->id);
        } elseif ($request->has('role') && $request->role === 'seller') {
            $query->where('seller_id', $user->id);
        } else {
            // Default: show both buyer and seller orders
            $query->where(function($q) use ($user) {
                $q->where('buyer_id', $user->id)
                  ->orWhere('seller_id', $user->id);
            });
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Sort orders
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        // Load relationships
        $query->with(['gig', 'buyer', 'seller']);
        
        $orders = $query->paginate(10);
        
        return response()->json([
            'orders' => $orders
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gig_id' => 'required|exists:gigs,id',
            'requirements' => 'nullable|string',
            'buyer_instructions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the gig
        $gig = Gig::findOrFail($request->gig_id);
        
        // Check if gig is active
        if (!$gig->is_active) {
            return response()->json([
                'message' => 'This gig is not currently available'
            ], 400);
        }
        
        // Create the order
        $order = new Order();
        $order->gig_id = $gig->id;
        $order->buyer_id = Auth::id();
        $order->seller_id = $gig->user_id;
        $order->total_amount = $gig->price;
        $order->status = 'pending';
        $order->delivery_date = now()->addDays($gig->delivery_time);
        $order->requirements = $request->requirements;
        $order->buyer_instructions = $request->buyer_instructions;
        $order->revisions_allowed = 3; // Default value, can be customized
        $order->revisions_used = 0;
        $order->is_completed = false;
        $order->save();
        
        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order->load(['gig', 'buyer', 'seller'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with(['gig', 'buyer', 'seller', 'messages', 'review'])->findOrFail($id);
        
        // Check if user is authorized to view this order
        $user = Auth::user();
        if ($user->id !== $order->buyer_id && $user->id !== $order->seller_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        
        return response()->json([
            'order' => $order
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order = Order::findOrFail($id);
        
        // Check if user is authorized to update this order
        $user = Auth::user();
        if ($user->id !== $order->buyer_id && $user->id !== $order->seller_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|string|in:pending,in_progress,delivered,completed,cancelled,disputed',
            'seller_notes' => 'nullable|string',
            'is_completed' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Update order status
        if ($request->has('status')) {
            // Validate status transitions
            $validTransition = $this->validateStatusTransition($order->status, $request->status, $user->id === $order->buyer_id);
            
            if (!$validTransition) {
                return response()->json([
                    'message' => 'Invalid status transition'
                ], 400);
            }
            
            $order->status = $request->status;
            
            // If status is completed, update completion fields
            if ($request->status === 'completed') {
                $order->is_completed = true;
                $order->completed_at = now();
            }

        }
        
        // Update other fields
        if ($request->has('seller_notes')) {
            $order->seller_notes = $request->seller_notes;
        }
        
        $order->save();
        
        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order->load(['gig', 'buyer', 'seller'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);
        
        // Check if user is authorized to delete this order
        $user = Auth::user();
        if ($user->id !== $order->buyer_id && $user->id !== $order->seller_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Only allow deletion of pending orders
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending orders can be deleted'
            ], 400);
        }
        
        $order->delete();
        
        return response()->json([
            'message' => 'Order deleted successfully'
        ]);
    }
    
    /**
     * Validate status transition based on current status and user role.
     */
    private function validateStatusTransition($currentStatus, $newStatus, $isBuyer)
    {
        $validTransitions = [
            'pending' => ['in_progress', 'cancelled'],
            'in_progress' => ['delivered', 'cancelled', 'disputed'],
            'delivered' => ['completed', 'disputed'],
            'completed' => [],
            'cancelled' => [],
            'disputed' => ['completed', 'cancelled'],
        ];
        
        // Role-based restrictions
        $buyerOnlyTransitions = ['completed', 'disputed'];
        $sellerOnlyTransitions = ['in_progress', 'delivered'];
        
        // Check if transition is valid
        if (!in_array($newStatus, $validTransitions[$currentStatus])) {
            return false;
        }
        
        // Check role-based restrictions
        if ($isBuyer && in_array($newStatus, $sellerOnlyTransitions)) {
            return false;
        }
        
        if (!$isBuyer && in_array($newStatus, $buyerOnlyTransitions)) {
            return false;
        }
        
        return true;
    }
}

