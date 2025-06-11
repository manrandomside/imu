<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SwipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing swipes first
        DB::table('swipes')->truncate();

        // Get all users and categories
        $users = User::all();
        $categories = Category::all();

        // Get specific categories for different types of interactions
        $friendsCategory = $categories->where('name', 'Friends & Social')->first();
        $careerCategory = $categories->where('name', 'Career & Jobs')->first();
        $studyCategory = $categories->where('name', 'Study Groups')->first();
        $datingCategory = $categories->where('name', 'Dating & Romance')->first();

        // Sample swipe interactions
        $swipes = [
            // Alice (CS) swipes on tech people
            [
                'swiper_id' => $users->where('name', 'Alice Johnson')->first()->id,
                'swiped_id' => $users->where('name', 'Henry Park')->first()->id, // Data Science
                'category_id' => $careerCategory->id,
                'action' => 'like',
                'swiped_at' => now()->subDays(1),
            ],
            [
                'swiper_id' => $users->where('name', 'Alice Johnson')->first()->id,
                'swiped_id' => $users->where('name', 'Frank Kumar')->first()->id, // Physics
                'category_id' => $studyCategory->id,
                'action' => 'like',
                'swiped_at' => now()->subDays(2),
            ],

            // Bob (Engineering) swipes on sports people
            [
                'swiper_id' => $users->where('name', 'Bob Chen')->first()->id,
                'swiped_id' => $users->where('name', 'Jack Thompson')->first()->id, // Sports
                'category_id' => $friendsCategory->id,
                'action' => 'like',
                'swiped_at' => now()->subDays(1),
            ],
            [
                'swiper_id' => $users->where('name', 'Bob Chen')->first()->id,
                'swiped_id' => $users->where('name', 'David Wilson')->first()->id, // Business
                'category_id' => $careerCategory->id,
                'action' => 'pass',
                'swiped_at' => now()->subDays(3),
            ],

            // Catherine (Design) swipes on creatives
            [
                'swiper_id' => $users->where('name', 'Catherine Maria')->first()->id,
                'swiped_id' => $users->where('name', 'Isabel Santos')->first()->id, // Music
                'category_id' => $friendsCategory->id,
                'action' => 'like',
                'swiped_at' => now()->subDays(1),
            ],
            [
                'swiper_id' => $users->where('name', 'Catherine Maria')->first()->id,
                'swiped_id' => $users->where('name', 'Alice Johnson')->first()->id, // CS
                'category_id' => $careerCategory->id,
                'action' => 'like',
                'swiped_at' => now()->subHours(12),
            ],

            // David (Business) swipes on entrepreneurial people
            [
                'swiper_id' => $users->where('name', 'David Wilson')->first()->id,
                'swiped_id' => $users->where('name', 'Alice Johnson')->first()->id, // CS
                'category_id' => $careerCategory->id,
                'action' => 'like',
                'swiped_at' => now()->subDays(2),
            ],
            [
                'swiper_id' => $users->where('name', 'David Wilson')->first()->id,
                'swiped_id' => $users->where('name', 'Henry Park')->first()->id, // Data Science
                'category_id' => $careerCategory->id,
                'action' => 'like',
                'swiped_at' => now()->subDays(1),
            ],

            // Emma (Psychology) swipes on helping-oriented people
            [
                'swiper_id' => $users->where('name', 'Emma Rodriguez')->first()->id,
                'swiped_id' => $users->where('name', 'Grace Liu')->first()->id, // Medicine
                'category_id' => $friendsCategory->id,
                'action' => 'like',
                'swiped_at' => now()->subDays(1),
            ],
            [
                'swiper_id' => $users->where('name', 'Emma Rodriguez')->first()->id,
                'swiped_id' => $users->where('name', 'Catherine Maria')->first()->id, // Design
                'category_id' => $studyCategory->id,
                'action' => 'pass',
                'swiped_at' => now()->subDays(2),
            ],

            // Some mutual likes for potential connections
            [
                'swiper_id' => $users->where('name', 'Henry Park')->first()->id,
                'swiped_id' => $users->where('name', 'Alice Johnson')->first()->id, // Mutual like
                'category_id' => $careerCategory->id,
                'action' => 'like',
                'swiped_at' => now()->subHours(6),
            ],
            [
                'swiper_id' => $users->where('name', 'Jack Thompson')->first()->id,
                'swiped_id' => $users->where('name', 'Bob Chen')->first()->id, // Mutual like
                'category_id' => $friendsCategory->id,
                'action' => 'like',
                'swiped_at' => now()->subHours(8),
            ],
            [
                'swiper_id' => $users->where('name', 'Isabel Santos')->first()->id,
                'swiped_id' => $users->where('name', 'Catherine Maria')->first()->id, // Mutual like
                'category_id' => $friendsCategory->id,
                'action' => 'like',
                'swiped_at' => now()->subHours(4),
            ],

            // Dating category swipes
            [
                'swiper_id' => $users->where('name', 'Frank Kumar')->first()->id,
                'swiped_id' => $users->where('name', 'Emma Rodriguez')->first()->id,
                'category_id' => $datingCategory->id,
                'action' => 'like',
                'swiped_at' => now()->subDays(1),
            ],
            [
                'swiper_id' => $users->where('name', 'Grace Liu')->first()->id,
                'swiped_id' => $users->where('name', 'Henry Park')->first()->id,
                'category_id' => $datingCategory->id,
                'action' => 'pass',
                'swiped_at' => now()->subDays(2),
            ],

            // More diverse interactions
            [
                'swiper_id' => $users->where('name', 'Isabel Santos')->first()->id,
                'swiped_id' => $users->where('name', 'Jack Thompson')->first()->id,
                'category_id' => $friendsCategory->id,
                'action' => 'pass',
                'swiped_at' => now()->subDays(3),
            ],
            [
                'swiper_id' => $users->where('name', 'Jack Thompson')->first()->id,
                'swiped_id' => $users->where('name', 'David Wilson')->first()->id,
                'category_id' => $careerCategory->id,
                'action' => 'like',
                'swiped_at' => now()->subHours(10),
            ],
        ];

        // Insert all swipes
        foreach ($swipes as $swipe) {
            DB::table('swipes')->insert(array_merge($swipe, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}