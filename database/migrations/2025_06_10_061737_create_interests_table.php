<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('interests', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('icon', 100)->nullable(); // Icon class/path
            $table->string('category', 50)->nullable(); // hobby, academic, sport, etc
            $table->timestamps();
        });
        
        // Insert initial interests data (from UI)
        DB::table('interests')->insert([
            ['name' => 'Photography', 'icon' => 'camera-icon', 'category' => 'hobby', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shopping', 'icon' => 'shopping-icon', 'category' => 'hobby', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Karaoke', 'icon' => 'mic-icon', 'category' => 'entertainment', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Yoga', 'icon' => 'yoga-icon', 'category' => 'sport', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cooking', 'icon' => 'cooking-icon', 'category' => 'hobby', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tennis', 'icon' => 'tennis-icon', 'category' => 'sport', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Run', 'icon' => 'run-icon', 'category' => 'sport', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Swimming', 'icon' => 'swimming-icon', 'category' => 'sport', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Art', 'icon' => 'art-icon', 'category' => 'creative', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Traveling', 'icon' => 'travel-icon', 'category' => 'hobby', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Extreme', 'icon' => 'extreme-icon', 'category' => 'sport', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Music', 'icon' => 'music-icon', 'category' => 'entertainment', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Drink', 'icon' => 'drink-icon', 'category' => 'social', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Video games', 'icon' => 'game-icon', 'category' => 'entertainment', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interests');
    }
};