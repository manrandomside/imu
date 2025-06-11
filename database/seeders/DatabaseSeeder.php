<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run all IMU app seeders in the correct order
        $this->call([
            CategorySeeder::class,       // 1. Create categories first
            InterestSeeder::class,       // 2. Create interests  
            UserSeeder::class,           // 3. Create users
            UserInterestSeeder::class,   // 4. Connect users to interests
            SwipeSeeder::class,          // 5. Create swipe interactions
            ConnectionSeeder::class,     // 6. Create connections from mutual likes
        ]);

        $this->command->info('IMU Database seeding completed successfully!');
        $this->command->info('Summary:');
        $this->command->info('   Categories: 10');
        $this->command->info('   Interests: 40');  
        $this->command->info('   Users: 11 (1 existing + 10 new)');
        $this->command->info('   User-Interest relationships: Connected');
        $this->command->info('   Swipes: 16 interactions');
        $this->command->info('   Connections: 6 (5 accepted + 1 pending)');
        $this->command->info('🚀 IMU app is ready for development and testing!');
    }
}