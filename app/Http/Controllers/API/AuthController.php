<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Gig;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Register a new user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign default user role
        $user->assignRole('user');

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Login user and create token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Logout user (revoke token)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * List all users (admin only)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listUsers(Request $request)
    {
        $users = User::with('roles')->paginate(15);
        
        return response()->json([
            'users' => $users
        ]);
    }

    /**
     * Show user details (admin only)
     * 
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function showUser(User $user)
    {
        $user->load(['roles', 'permissions']);
        
        return response()->json([
            'user' => $user
        ]);
    }

    /**
     * Update user role (admin only)
     * 
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserRole(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Sync roles
        $user->syncRoles($request->roles);

        return response()->json([
            'message' => 'User roles updated successfully',
            'user' => $user->load('roles')
        ]);
    }

    /**
     * Delete user (admin only)
     * 
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUser(User $user)
    {
        // Prevent deleting yourself
        if (Auth::id() === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get system statistics (admin only)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function systemStats()
    {
        $stats = [
            'total_users' => User::count(),
            'total_gigs' => Gig::count(),
            'total_orders' => Order::count(),
            'users_by_role' => [],
            'recent_registrations' => User::latest()->take(5)->get(),
            'recent_orders' => Order::with('user', 'gig')->latest()->take(5)->get(),
        ];

        // Get user counts by role
        $roles = Role::all();
        foreach ($roles as $role) {
            $stats['users_by_role'][$role->name] = $role->users()->count();
        }

        // Monthly user registrations for the past 6 months
        $stats['monthly_registrations'] = DB::table('users')
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'stats' => $stats
        ]);
    }
}
