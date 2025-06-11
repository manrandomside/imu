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
        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user1_id')->constrained('users')->onDelete('cascade'); // User pertama (yang "hit me up" pertama)
            $table->foreignId('user2_id')->constrained('users')->onDelete('cascade'); // User kedua (yang di-match)
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade'); // Category context
            $table->enum('status', ['pending', 'accepted', 'blocked'])->default('accepted'); // Status connection
            $table->decimal('match_score', 3, 2)->nullable(); // Score dari algoritma matching (0.00-1.00)
            $table->timestamp('connected_at')->useCurrent(); // Kapan connection terbentuk
            $table->timestamps();

            // Ensure no duplicate connections (user1_id always < user2_id)
            $table->unique(['user1_id', 'user2_id', 'category_id'], 'unique_connection');
            
            // Indexes for performance
            $table->index(['user1_id', 'status']);
            $table->index(['user2_id', 'status']);
            $table->index(['category_id', 'connected_at']);
            $table->index('match_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connections');
    }
};