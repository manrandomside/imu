<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConnectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing connections first
        DB::table('connections')->truncate();

        // Get all users
        $users = User::all();
        $categories = Category::all();

        // Get specific categories
        $friendsCategory = $categories->where('name', 'Friends & Social')->first();
        $careerCategory = $categories->where('name', 'Career & Jobs')->first();

        // Find mutual likes and create connections
        // Based on the swipes we created, these pairs have mutual likes:

        // Alice ↔ Henry (Career)
        $alice = $users->where('name', 'Alice Johnson')->first();
        $henry = $users->where('name', 'Henry Park')->first();
        
        // Bob ↔ Jack (Friends)  
        $bob = $users->where('name', 'Bob Chen')->first();
        $jack = $users->where('name', 'Jack Thompson')->first();
        
        // Catherine ↔ Isabel (Friends)
        $catherine = $users->where('name', 'Catherine Maria')->first();
        $isabel = $users->where('name', 'Isabel Santos')->first();

        $connections = [
            // Alice ↔ Henry connection (Career/Tech networking)
            [
                'user1_id' => min($alice->id, $henry->id), // Ensure consistent ordering
                'user2_id' => max($alice->id, $henry->id),
                'category_id' => $careerCategory->id,
                'status' => 'accepted',
                'match_score' => 0.85,
                'connected_at' => now()->subHours(5),
            ],
            
            // Bob ↔ Jack connection (Friends/Sports)
            [
                'user1_id' => min($bob->id, $jack->id),
                'user2_id' => max($bob->id, $jack->id), 
                'category_id' => $friendsCategory->id,
                'status' => 'accepted',
                'match_score' => 0.92,
                'connected_at' => now()->subHours(7),
            ],
            
            // Catherine ↔ Isabel connection (Friends/Creative)
            [
                'user1_id' => min($catherine->id, $isabel->id),
                'user2_id' => max($catherine->id, $isabel->id),
                'category_id' => $friendsCategory->id,
                'status' => 'accepted',
                'match_score' => 0.88,
                'connected_at' => now()->subHours(3),
            ],

            // Additional sample connections (some people can connect through other means)
            // David ↔ Alice (Business networking)
            [
                'user1_id' => min($users->where('name', 'David Wilson')->first()->id, $alice->id),
                'user2_id' => max($users->where('name', 'David Wilson')->first()->id, $alice->id),
                'category_id' => $careerCategory->id,
                'status' => 'accepted',
                'match_score' => 0.78,
                'connected_at' => now()->subDays(1),
            ],

            // Emma ↔ Grace (Study/Health collaboration)
            [
                'user1_id' => min($users->where('name', 'Emma Rodriguez')->first()->id, $users->where('name', 'Grace Liu')->first()->id),
                'user2_id' => max($users->where('name', 'Emma Rodriguez')->first()->id, $users->where('name', 'Grace Liu')->first()->id),
                'category_id' => $friendsCategory->id,
                'status' => 'accepted',
                'match_score' => 0.81,
                'connected_at' => now()->subHours(12),
            ],

            // One pending connection (Frank reached out to Emma)
            [
                'user1_id' => min($users->where('name', 'Frank Kumar')->first()->id, $users->where('name', 'Emma Rodriguez')->first()->id),
                'user2_id' => max($users->where('name', 'Frank Kumar')->first()->id, $users->where('name', 'Emma Rodriguez')->first()->id),
                'category_id' => $friendsCategory->id,
                'status' => 'pending',
                'match_score' => 0.73,
                'connected_at' => now()->subHours(2),
            ],
        ];

        // Insert all connections
        foreach ($connections as $connection) {
            DB::table('connections')->insert(array_merge($connection, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}