<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        UserProfile::create([
            'user_id' => $admin->id,
            'account_type' => 'both',
            'is_verified_seller' => true,
            'bio' => 'Administrator of the marketplace platform.',
            'title' => 'System Administrator',
            'skills' => json_encode(['Administration', 'Customer Support', 'Technical Support']),
            'languages' => json_encode(['English']),
        ]);

        // Create seller user
        $seller = User::create([
            'name' => 'Seller User',
            'email' => 'seller@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        UserProfile::create([
            'user_id' => $seller->id,
            'account_type' => 'seller',
            'is_verified_seller' => true,
            'bio' => 'Professional graphic designer with over 5 years of experience.',
            'title' => 'Senior Graphic Designer',
            'skills' => json_encode(['Graphic Design', 'Logo Design', 'Illustration', 'Photoshop', 'Illustrator']),
            'languages' => json_encode(['English', 'Spanish']),
        ]);

        // Create buyer user
        $buyer = User::create([
            'name' => 'Buyer User',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        UserProfile::create([
            'user_id' => $buyer->id,
            'account_type' => 'buyer',
            'bio' => 'Looking for creative professionals for my projects.',
            'title' => 'Project Manager',
            'skills' => json_encode(['Project Management', 'Marketing', 'Business Development']),
            'languages' => json_encode(['English']),
        ]);

        // Create additional users
        User::factory(10)->create()->each(function ($user) {
            $accountType = ['buyer', 'seller', 'both'][rand(0, 2)];
            
            UserProfile::create([
                'user_id' => $user->id,
                'account_type' => $accountType,
                'is_verified_seller' => $accountType !== 'buyer',
                'bio' => 'This is a sample bio for ' . $user->name,
                'title' => 'Professional ' . ucfirst($accountType),
                'skills' => json_encode(['Skill 1', 'Skill 2', 'Skill 3']),
                'languages' => json_encode(['English']),
            ]);
        });
    }
}
