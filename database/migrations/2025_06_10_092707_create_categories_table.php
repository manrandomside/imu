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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Friends, Jobs, Committee, PKM, KKN, Contest
            $table->string('slug')->unique(); // friends, jobs, committee, pkm, kkn, contest
            $table->string('icon'); // Icon class for UI
            $table->string('color')->default('#007bff'); // UI color
            $table->text('description')->nullable(); // Category description
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // For ordering in UI
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};