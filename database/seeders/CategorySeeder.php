<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Friends & Social',
                'description' => 'Connect with people to build friendships and social networks',
                'icon' => 'users',
                'color' => '#3B82F6', // Blue
                'is_active' => true,
            ],
            [
                'name' => 'Career & Jobs',
                'description' => 'Professional networking and career opportunities',
                'icon' => 'briefcase',
                'color' => '#059669', // Green
                'is_active' => true,
            ],
            [
                'name' => 'PKM & Research',
                'description' => 'Academic projects, research collaborations, and PKM teams',
                'icon' => 'academic-cap',
                'color' => '#7C3AED', // Purple
                'is_active' => true,
            ],
            [
                'name' => 'Study Groups',
                'description' => 'Find study partners and academic collaboration',
                'icon' => 'book-open',
                'color' => '#DC2626', // Red
                'is_active' => true,
            ],
            [
                'name' => 'Events & Activities',
                'description' => 'Campus events, activities, and social gatherings',
                'icon' => 'calendar',
                'color' => '#EA580C', // Orange
                'is_active' => true,
            ],
            [
                'name' => 'Dating & Romance',
                'description' => 'Romantic connections and dating',
                'icon' => 'heart',
                'color' => '#EC4899', // Pink
                'is_active' => true,
            ],
            [
                'name' => 'Mentorship',
                'description' => 'Connect with mentors or find mentees',
                'icon' => 'user-group',
                'color' => '#0891B2', // Cyan
                'is_active' => true,
            ],
            [
                'name' => 'Business & Startup',
                'description' => 'Entrepreneurship, business ideas, and startup collaborations',
                'icon' => 'lightning-bolt',
                'color' => '#7C2D12', // Brown
                'is_active' => true,
            ],
            [
                'name' => 'Hobbies & Interests',
                'description' => 'Connect over shared hobbies and personal interests',
                'icon' => 'star',
                'color' => '#9333EA', // Violet
                'is_active' => true,
            ],
            [
                'name' => 'Sports & Fitness',
                'description' => 'Sports teams, workout partners, and fitness activities',
                'icon' => 'fire',
                'color' => '#16A34A', // Dark Green
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}