<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Get messages for a specific order.
     *
     * @param  string  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderMessages(string $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        // Check if user is authorized to view messages for this order
        $user = Auth::user();
        if ($user->id !== $order->buyer_id && $user->id !== $order->seller_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $messages = Message::where('order_id', $orderId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();
        
        return response()->json([
            'messages' => $messages
        ]);
    }
    
    /**
     * Send a message in an order conversation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request, string $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        // Check if user is authorized to send messages for this order
        $user = Auth::user();
        if ($user->id !== $order->buyer_id && $user->id !== $order->seller_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'attachment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Determine recipient ID
        $recipientId = ($user->id === $order->buyer_id) ? $order->seller_id : $order->buyer_id;
        
        $message = new Message();
        $message->order_id = $orderId;
        $message->sender_id = $user->id;
        $message->recipient_id = $recipientId;
        $message->content = $request->content;
        $message->attachment = $request->attachment;
        $message->is_read = false;
        $message->save();
        
        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message->load('sender')
        ], 201);
    }
    
    /**
     * Mark a message as read.
     *
     * @param  string  $messageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(string $messageId)
    {
        $message = Message::findOrFail($messageId);
        
        // Check if user is authorized to mark this message as read
        $user = Auth::user();
        if ($user->id !== $message->recipient_id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $message->is_read = true;
        $message->save();
        
        return response()->json([
            'message' => 'Message marked as read'
        ]);
    }
    
    /**
     * Get unread message count for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        
        $unreadCount = Message::where('recipient_id', $user->id)
            ->where('is_read', false)
            ->count();
        
        return response()->json([
            'unread_count' => $unreadCount
        ]);
    }
    
    /**
     * Get all conversations for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConversations()
    {
        $user = Auth::user();
        
        // Get all orders where the user is either buyer or seller
        $orders = Order::where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id)
            ->with(['gig', 'buyer', 'seller'])
            ->get();
        
        $conversations = [];
        
        foreach ($orders as $order) {
            // Get the other user in the conversation
            $otherUserId = ($user->id === $order->buyer_id) ? $order->seller_id : $order->buyer_id;
            $otherUser = User::find($otherUserId);
            
            // Get the last message in this conversation
            $lastMessage = Message::where('order_id', $order->id)
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Get unread count for this conversation
            $unreadCount = Message::where('order_id', $order->id)
                ->where('recipient_id', $user->id)
                ->where('is_read', false)
                ->count();
            
            $conversations[] = [
                'order_id' => $order->id,
                'gig_title' => $order->gig->title,
                'other_user' => $otherUser,
                'last_message' => $lastMessage,
                'unread_count' => $unreadCount,
                'updated_at' => $lastMessage ? $lastMessage->created_at : $order->created_at
            ];
        }
        
        // Sort conversations by last message time (most recent first)
        usort($conversations, function($a, $b) {
            return $b['updated_at']->timestamp - $a['updated_at']->timestamp;
        });
        
        return response()->json([
            'conversations' => $conversations
        ]);
    }
} 