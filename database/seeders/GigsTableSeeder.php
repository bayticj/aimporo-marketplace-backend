<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Gig;
use App\Models\User;
use App\Models\Category;
use App\Models\UserProfile;

class GigsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get seller users
        $sellerProfiles = UserProfile::where('account_type', 'seller')
            ->orWhere('account_type', 'both')
            ->get();
        
        // Get categories
        $categories = Category::whereNotNull('parent_id')->get();
        
        // Sample gig titles and descriptions
        $gigTemplates = [
            [
                'title_template' => 'I will create a professional {category} for your business',
                'description_template' => 'I will design a custom {category} that perfectly represents your brand identity. With over 5 years of experience in {parent_category}, I deliver high-quality work that meets your specific requirements. The package includes unlimited revisions until you are completely satisfied.',
                'price_range' => [50, 200],
                'delivery_days' => [3, 7],
            ],
            [
                'title_template' => 'I will provide expert {category} services',
                'description_template' => 'Looking for professional {category} services? I am an experienced {parent_category} specialist with a proven track record of delivering exceptional results. I work closely with clients to understand their needs and provide tailored solutions that exceed expectations.',
                'price_range' => [100, 500],
                'delivery_days' => [5, 14],
            ],
            [
                'title_template' => 'Premium {category} service for your project',
                'description_template' => 'Get top-quality {category} services from an industry expert. I specialize in creating custom solutions that help businesses stand out in the competitive market. My approach focuses on understanding your unique requirements and delivering results that drive success.',
                'price_range' => [150, 800],
                'delivery_days' => [7, 21],
            ],
        ];
        
        // Create gigs for each seller
        foreach ($sellerProfiles as $profile) {
            $user = User::find($profile->user_id);
            
            // Create 1-3 gigs per seller
            $gigCount = rand(1, 3);
            
            for ($i = 0; $i < $gigCount; $i++) {
                // Select random category
                $category = $categories->random();
                $parentCategory = Category::find($category->parent_id);
                
                // Select random gig template
                $template = $gigTemplates[array_rand($gigTemplates)];
                
                // Replace placeholders in templates
                $title = str_replace(
                    ['{category}', '{parent_category}'],
                    [$category->name, $parentCategory->name],
                    $template['title_template']
                );
                
                $description = str_replace(
                    ['{category}', '{parent_category}'],
                    [$category->name, $parentCategory->name],
                    $template['description_template']
                );
                
                // Generate random price and delivery time
                $price = rand($template['price_range'][0], $template['price_range'][1]);
                $deliveryTime = rand($template['delivery_days'][0], $template['delivery_days'][1]);
                
                // Create the gig
                Gig::create([
                    'user_id' => $user->id,
                    'title' => $title,
                    'description' => $description,
                    'category_id' => $parentCategory->id,
                    'subcategory' => $category->name,
                    'price' => $price,
                    'delivery_time' => $deliveryTime,
                    'requirements' => 'Please provide details about your project requirements.',
                    'is_featured' => rand(0, 1) === 1,
                    'is_active' => true,
                    'tags' => json_encode([$category->name, $parentCategory->name, 'service']),
                ]);
            }
        }
    }
}
