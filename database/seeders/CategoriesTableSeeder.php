<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainCategories = [
            [
                'name' => 'Digital Marketing',
                'icon' => 'fa-bullhorn',
                'subcategories' => [
                    'Social Media Marketing',
                    'Search Engine Optimization (SEO)',
                    'Content Marketing',
                    'Email Marketing',
                    'PPC Advertising',
                ]
            ],
            [
                'name' => 'Graphic Design',
                'icon' => 'fa-palette',
                'subcategories' => [
                    'Logo Design',
                    'Brand Identity',
                    'Illustration',
                    'Print Design',
                    'Packaging Design',
                ]
            ],
            [
                'name' => 'Web Development',
                'icon' => 'fa-code',
                'subcategories' => [
                    'Frontend Development',
                    'Backend Development',
                    'Full Stack Development',
                    'E-commerce Development',
                    'WordPress Development',
                ]
            ],
            [
                'name' => 'Writing & Translation',
                'icon' => 'fa-pen',
                'subcategories' => [
                    'Content Writing',
                    'Copywriting',
                    'Technical Writing',
                    'Translation',
                    'Proofreading & Editing',
                ]
            ],
            [
                'name' => 'Video & Animation',
                'icon' => 'fa-video',
                'subcategories' => [
                    'Video Editing',
                    'Animation',
                    'Motion Graphics',
                    'Video Production',
                    'Explainer Videos',
                ]
            ],
            [
                'name' => 'Music & Audio',
                'icon' => 'fa-music',
                'subcategories' => [
                    'Voice Over',
                    'Music Production',
                    'Audio Editing',
                    'Sound Design',
                    'Podcast Production',
                ]
            ],
        ];

        foreach ($mainCategories as $index => $mainCategory) {
            $category = Category::create([
                'name' => $mainCategory['name'],
                'slug' => Str::slug($mainCategory['name']),
                'description' => 'Services related to ' . $mainCategory['name'],
                'icon' => $mainCategory['icon'],
                'order' => $index + 1,
                'is_featured' => true,
            ]);

            foreach ($mainCategory['subcategories'] as $subIndex => $subcategoryName) {
                Category::create([
                    'name' => $subcategoryName,
                    'slug' => Str::slug($subcategoryName),
                    'description' => 'Services related to ' . $subcategoryName,
                    'parent_id' => $category->id,
                    'order' => $subIndex + 1,
                ]);
            }
        }
    }
}
