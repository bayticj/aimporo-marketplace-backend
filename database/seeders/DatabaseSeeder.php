<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call seeders in the correct order
        $this->call([
            UsersTableSeeder::class,      // First create users
            CategoriesTableSeeder::class, // Then create categories
            GigsTableSeeder::class,       // Then create gigs using users and categories
        ]);
    }
}
