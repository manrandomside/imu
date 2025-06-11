<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Interest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserInterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing user_interests first
        DB::table('user_interests')->truncate();

        // Get all interests for easy reference
        $interests = Interest::all()->keyBy('name');

        // Define user-interest mappings based on their profiles
        $userInterests = [
            'Alice Johnson' => [
                'Computer Science', 'Web Development', 'Data Science', 'UI/UX Design', 
                'Startup & Innovation', 'Networking', 'Reading', 'Gaming'
            ],
            'Robert Chen Wei Ming' => [
                'Engineering', 'Basketball', 'Gym & Fitness', 'Gaming', 
                'Leadership', 'Sports & Fitness', 'Networking'
            ],
            'Catherine Maria Rodriguez' => [
                'UI/UX Design', 'Photography', 'Drawing & Painting', 'Film & Video',
                'Fashion', 'Music', 'Creative Writing', 'Travel'
            ],
            'David Michael Wilson' => [
                'Business & Management', 'Startup & Innovation', 'Digital Marketing', 
                'Leadership', 'Finance & Investment', 'Networking', 'Reading'
            ],
            'Emma Sofia Rodriguez' => [
                'Psychology', 'Mental Health', 'Volunteering', 'Reading', 
                'Writing', 'Environmental Issues', 'Networking'
            ],
            'Franklin Kumar Patel' => [
                'Physics', 'Mathematics', 'Gaming', 'Reading', 
                'Data Science', 'Computer Science', 'Film & Video'
            ],
            'Grace Mei-Lin Liu' => [
                'Biology', 'Chemistry', 'Swimming', 'Environmental Issues',
                'Volunteering', 'Mental Health', 'Reading'
            ],
            'Henry Joon Park' => [
                'Data Science', 'Computer Science', 'Machine Learning', 'Finance & Investment',
                'Web Development', 'Startup & Innovation', 'Reading'
            ],
            'Isabel Carmen Santos' => [
                'Music', 'Dancing', 'Film & Video', 'Photography',
                'Fashion', 'Travel', 'Drawing & Painting'
            ],
            'Jackson Michael Thompson' => [
                'Football', 'Basketball', 'Gym & Fitness', 'Running',
                'Leadership', 'Business & Management', 'Networking'
            ],
        ];

        // Create user-interest relationships
        foreach ($userInterests as $fullName => $userInterestNames) {
            $user = User::where('full_name', $fullName)->first();
            
            if ($user) {
                foreach ($userInterestNames as $interestName) {
                    $interest = $interests->get($interestName);
                    
                    if ($interest) {
                        // Insert into user_interests pivot table
                        DB::table('user_interests')->insert([
                            'user_id' => $user->id,
                            'interest_id' => $interest->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}