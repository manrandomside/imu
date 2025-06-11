<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing interests first (optional - untuk fresh start)
        Interest::truncate();

        $interests = [
            // Academic & Study Related
            ['name' => 'Computer Science', 'icon' => 'code', 'category' => 'academic'],
            ['name' => 'Mathematics', 'icon' => 'calculator', 'category' => 'academic'],
            ['name' => 'Physics', 'icon' => 'atom', 'category' => 'academic'],
            ['name' => 'Biology', 'icon' => 'leaf', 'category' => 'academic'],
            ['name' => 'Chemistry', 'icon' => 'flask', 'category' => 'academic'],
            ['name' => 'Engineering', 'icon' => 'gear', 'category' => 'academic'],
            ['name' => 'Business & Management', 'icon' => 'briefcase', 'category' => 'academic'],
            ['name' => 'Psychology', 'icon' => 'brain', 'category' => 'academic'],

            // Technology & Programming  
            ['name' => 'Web Development', 'icon' => 'globe', 'category' => 'technology'],
            ['name' => 'Mobile Development', 'icon' => 'smartphone', 'category' => 'technology'],
            ['name' => 'Data Science', 'icon' => 'chart-bar', 'category' => 'technology'],
            ['name' => 'UI/UX Design', 'icon' => 'palette', 'category' => 'technology'],
            ['name' => 'Cybersecurity', 'icon' => 'shield', 'category' => 'technology'],
            ['name' => 'Game Development', 'icon' => 'gamepad', 'category' => 'technology'],

            // Sports & Fitness
            ['name' => 'Football', 'icon' => 'soccer-ball', 'category' => 'sports'],
            ['name' => 'Basketball', 'icon' => 'basketball', 'category' => 'sports'],
            ['name' => 'Badminton', 'icon' => 'racket', 'category' => 'sports'],
            ['name' => 'Swimming', 'icon' => 'waves', 'category' => 'sports'],
            ['name' => 'Gym & Fitness', 'icon' => 'dumbbell', 'category' => 'sports'],
            ['name' => 'Running', 'icon' => 'runner', 'category' => 'sports'],

            // Arts & Creative
            ['name' => 'Music', 'icon' => 'music-note', 'category' => 'creative'],
            ['name' => 'Photography', 'icon' => 'camera', 'category' => 'creative'],
            ['name' => 'Drawing & Painting', 'icon' => 'brush', 'category' => 'creative'],
            ['name' => 'Writing', 'icon' => 'pen', 'category' => 'creative'],
            ['name' => 'Dancing', 'icon' => 'dance', 'category' => 'creative'],
            ['name' => 'Film & Video', 'icon' => 'video', 'category' => 'creative'],

            // Hobbies & Lifestyle
            ['name' => 'Reading', 'icon' => 'book-open', 'category' => 'lifestyle'],
            ['name' => 'Gaming', 'icon' => 'game-controller', 'category' => 'lifestyle'],
            ['name' => 'Cooking', 'icon' => 'chef-hat', 'category' => 'lifestyle'],
            ['name' => 'Travel', 'icon' => 'plane', 'category' => 'lifestyle'],
            ['name' => 'Anime & Manga', 'icon' => 'star', 'category' => 'lifestyle'],
            ['name' => 'Fashion', 'icon' => 'shirt', 'category' => 'lifestyle'],

            // Business & Entrepreneurship
            ['name' => 'Startup & Innovation', 'icon' => 'lightbulb', 'category' => 'business'],
            ['name' => 'Digital Marketing', 'icon' => 'megaphone', 'category' => 'business'],
            ['name' => 'Finance & Investment', 'icon' => 'dollar-sign', 'category' => 'business'],
            ['name' => 'Leadership', 'icon' => 'crown', 'category' => 'business'],

            // Social & Community
            ['name' => 'Volunteering', 'icon' => 'heart', 'category' => 'social'],
            ['name' => 'Environmental Issues', 'icon' => 'tree', 'category' => 'social'],
            ['name' => 'Mental Health', 'icon' => 'brain-circuit', 'category' => 'social'],
            ['name' => 'Networking', 'icon' => 'users-round', 'category' => 'social'],
        ];

        foreach ($interests as $interest) {
            Interest::create($interest);
        }
    }
}