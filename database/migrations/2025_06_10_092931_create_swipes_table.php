<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('swipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('swiper_id')->constrained('users')->onDelete('cascade'); // User yang melakukan swipe
            $table->foreignId('swiped_id')->constrained('users')->onDelete('cascade'); // User yang di-swipe
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade'); // Category context (Friends, Jobs, etc.)
            $table->enum('action', ['like', 'pass']); // Action yang dilakukan
            $table->timestamp('swiped_at')->useCurrent(); // Kapan swipe dilakukan
            $table->timestamps();

            // Ensure one swipe per user pair per category
            $table->unique(['swiper_id', 'swiped_id', 'category_id'], 'unique_swipe_per_category');
            
            // Indexes for performance
            $table->index(['swiper_id', 'category_id']);
            $table->index(['swiped_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swipes');
    }
};