<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Gig permissions
            'view gigs',
            'create_gig',  // Used in middleware
            'edit_gig',    // Used in middleware
            'delete_gig',  // Used in middleware
            'approve gigs',
            
            // Order permissions
            'view orders',
            'create orders',
            'edit orders',
            'cancel orders',
            'complete orders',
            
            // Review permissions
            'view reviews',
            'create_review',  // Used in middleware
            'edit_review',    // Used in middleware
            'delete_review',  // Used in middleware
            'moderate reviews',
            
            // Message permissions
            'view messages',
            'send messages',
            'delete messages',
            
            // Transaction permissions
            'view transactions',
            'process refunds',
            
            // Admin permissions
            'access admin panel',
            'view audit logs',
            'manage settings',
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles if they don't exist and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        $moderatorRole = Role::firstOrCreate(['name' => 'moderator']);
        $moderatorRole->syncPermissions([
            'view users',
            'view gigs',
            'approve gigs',
            'view orders',
            'view reviews',
            'moderate reviews',
            'view messages',
            'view transactions',
            'access admin panel',
            'view audit logs',
        ]);

        $sellerRole = Role::firstOrCreate(['name' => 'seller']);
        $sellerRole->syncPermissions([
            'view gigs',
            'create_gig',
            'edit_gig',
            'delete_gig',
            'view orders',
            'complete orders',
            'view reviews',
            'create_review',
            'edit_review',
            'view messages',
            'send messages',
            'view transactions',
        ]);

        $buyerRole = Role::firstOrCreate(['name' => 'buyer']);
        $buyerRole->syncPermissions([
            'view gigs',
            'view orders',
            'create orders',
            'cancel orders',
            'view reviews',
            'create_review',
            'edit_review',
            'delete_review',
            'view messages',
            'send messages',
            'view transactions',
        ]);

        // Create a default user role with minimal permissions
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->syncPermissions([
            'view gigs',
            'view reviews',
        ]);

        // Create an admin user if it doesn't exist
        $admin = User::where('email', 'admin@example.com')->first();
        
        if (!$admin) {
            $admin = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
        }
        
        // Assign admin role to the admin user
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
} 