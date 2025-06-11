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
        Schema::table('users', function (Blueprint $table) {
            // Basic Authentication Fields
            $table->string('username')->unique()->after('name');
            $table->string('full_name')->after('username');
            
            // User Type & Verification
            $table->enum('user_type', ['student', 'alumni'])->after('email');
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])
                  ->default('pending')->after('user_type');
            $table->string('verification_document')->nullable()->after('verification_status');
            
            // Profile Information (from UI Profile Setup)
            $table->string('nickname')->nullable()->after('verification_document');
            $table->string('prodi')->nullable()->after('nickname');
            $table->string('fakultas')->nullable()->after('prodi');
            $table->enum('gender', ['male', 'female'])->nullable()->after('fakultas');
            $table->text('bio')->nullable()->after('gender');
            $table->integer('age')->nullable()->after('bio');
            $table->string('qualification')->nullable()->after('age'); // MBBS, S1, etc
            $table->string('profile_picture')->nullable()->after('qualification');
            
            // Match Preferences (from UI "What are you looking for?")
            $table->json('looking_for')->nullable()->after('profile_picture');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'full_name', 
                'user_type',
                'verification_status',
                'verification_document',
                'nickname',
                'prodi',
                'fakultas',
                'gender',
                'bio',
                'age',
                'qualification',
                'profile_picture',
                'looking_for'
            ]);
        });
    }
};