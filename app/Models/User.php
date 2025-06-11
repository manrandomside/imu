<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username', 
        'full_name',
        'email',
        'password',
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
        'looking_for',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_document',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'looking_for' => 'array', // Cast to array for multiple selections
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Many-to-Many relationship with Interest
     * User dapat memiliki banyak interests
     */
    public function interests()
    {
        return $this->belongsToMany(Interest::class, 'user_interests')
                    ->withTimestamps();
    }

    /**
     * Swipes yang dilakukan oleh user ini (outgoing swipes)
     */
    public function sentSwipes()
    {
        return $this->hasMany(Swipe::class, 'swiper_id');
    }

    /**
     * Swipes yang diterima oleh user ini (incoming swipes)
     */
    public function receivedSwipes()
    {
        return $this->hasMany(Swipe::class, 'swiped_id');
    }

    /**
     * Connections dimana user ini adalah user1
     */
    public function connectionsAsUser1()
    {
        return $this->hasMany(Connection::class, 'user1_id');
    }

    /**
     * Connections dimana user ini adalah user2
     */
    public function connectionsAsUser2()
    {
        return $this->hasMany(Connection::class, 'user2_id');
    }

    // ========================================
    // EMAIL VALIDATION METHODS
    // ========================================

    /**
     * Validate email domain based on user type
     */
    public static function validateEmailForUserType($email, $userType)
    {
        if ($userType === 'student') {
            // Student harus menggunakan @unud.ac.id
            return str_ends_with($email, '@unud.ac.id');
        } elseif ($userType === 'alumni') {
            // Alumni bisa email apa saja (akan diverifikasi manual)
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }
        
        return false;
    }

    // ========================================
    // VERIFICATION & STATUS METHODS
    // ========================================

    /**
     * Get verification status badge
     */
    public function getVerificationBadge()
    {
        return match($this->verification_status) {
            'verified' => 'Verified',
            'pending' => 'Pending',
            'rejected' => 'Rejected',
            default => 'Unverified'
        };
    }

    /**
     * Check if user is verified
     */
    public function isVerified()
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Check if user is student
     */
    public function isStudent()
    {
        return $this->user_type === 'student';
    }

    /**
     * Check if user is alumni
     */
    public function isAlumni()
    {
        return $this->user_type === 'alumni';
    }

    // ========================================
    // PROFILE COMPLETION METHODS
    // ========================================

    /**
     * Check if profile is complete
     */
    public function isProfileComplete()
    {
        $requiredFields = [
            'nickname', 'prodi', 'fakultas', 'gender', 
            'bio', 'age', 'qualification'
        ];

        foreach ($requiredFields as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }

        // Check if user has selected interests
        if ($this->interests()->count() === 0) {
            return false;
        }

        // Check if user has selected what they're looking for
        if (empty($this->looking_for) || count($this->looking_for) === 0) {
            return false;
        }

        return true;
    }

    /**
     * Get profile completion percentage
     */
    public function getProfileCompletionPercentage()
    {
        $totalFields = 10; // Total required fields
        $completedFields = 0;

        $requiredFields = [
            'nickname', 'prodi', 'fakultas', 'gender', 
            'bio', 'age', 'qualification', 'profile_picture'
        ];

        foreach ($requiredFields as $field) {
            if (!empty($this->$field)) {
                $completedFields++;
            }
        }

        // Check interests (worth 1 point)
        if ($this->interests()->count() > 0) {
            $completedFields++;
        }

        // Check looking_for (worth 1 point)
        if (!empty($this->looking_for) && count($this->looking_for) > 0) {
            $completedFields++;
        }

        return round(($completedFields / $totalFields) * 100);
    }

    // ========================================
    // MATCHING & CONNECTION METHODS
    // ========================================

    /**
     * Get all connections for this user (both as user1 and user2)
     */
    public function allConnections()
    {
        $connectionsAsUser1 = $this->connectionsAsUser1()->get();
        $connectionsAsUser2 = $this->connectionsAsUser2()->get();
        
        return $connectionsAsUser1->concat($connectionsAsUser2);
    }

    /**
     * Check if user has connection with another user in specific category
     */
    public function hasConnectionWith(User $otherUser, $categoryId = null)
    {
        $query = Connection::where(function($q) use ($otherUser) {
            $q->where(function($subQ) use ($otherUser) {
                $subQ->where('user1_id', $this->id)
                     ->where('user2_id', $otherUser->id);
            })->orWhere(function($subQ) use ($otherUser) {
                $subQ->where('user1_id', $otherUser->id)
                     ->where('user2_id', $this->id);
            });
        });

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->exists();
    }

    /**
     * Check if user has swiped another user in specific category
     */
    public function hasSwipedUser(User $otherUser, $categoryId)
    {
        return $this->sentSwipes()
                    ->where('swiped_id', $otherUser->id)
                    ->where('category_id', $categoryId)
                    ->exists();
    }

    /**
     * Get potential matches for user (users not yet swiped in category)
     */
    public function getPotentialMatches($categoryId, $limit = 10)
    {
        // Get IDs of users already swiped in this category
        $swipedUserIds = $this->sentSwipes()
                              ->where('category_id', $categoryId)
                              ->pluck('swiped_id')
                              ->toArray();

        // Add current user ID to exclude self
        $swipedUserIds[] = $this->id;

        // Get potential matches
        return User::whereNotIn('id', $swipedUserIds)
                   ->where('verification_status', 'verified')
                   ->inRandomOrder()
                   ->limit($limit)
                   ->get();
    }

    /**
     * Calculate match score with another user based on common interests
     */
    public function calculateMatchScore(User $otherUser)
    {
        $myInterests = $this->interests()->pluck('interests.id')->toArray();
        $theirInterests = $otherUser->interests()->pluck('interests.id')->toArray();

        if (empty($myInterests) || empty($theirInterests)) {
            return 0.0;
        }

        $commonInterests = array_intersect($myInterests, $theirInterests);
        $totalUniqueInterests = count(array_unique(array_merge($myInterests, $theirInterests)));

        if ($totalUniqueInterests === 0) {
            return 0.0;
        }

        return round((count($commonInterests) / $totalUniqueInterests), 2);
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope untuk filter user berdasarkan verification status
     */
    public function scopeVerified(Builder $query)
    {
        return $query->where('verification_status', 'verified');
    }

    /**
     * Scope untuk filter berdasarkan user type
     */
    public function scopeUserType(Builder $query, $type)
    {
        return $query->where('user_type', $type);
    }

    /**
     * Scope untuk filter berdasarkan fakultas
     */
    public function scopeFakultas(Builder $query, $fakultas)
    {
        return $query->where('fakultas', $fakultas);
    }

    /**
     * Scope untuk filter berdasarkan gender
     */
    public function scopeGender(Builder $query, $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope untuk users dengan profile complete
     */
    public function scopeWithCompleteProfile(Builder $query)
    {
        return $query->whereNotNull(['nickname', 'prodi', 'fakultas', 'gender', 'bio', 'age', 'qualification'])
                     ->whereHas('interests')
                     ->whereNotNull('looking_for');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get display name (nickname or name)
     */
    public function getDisplayName()
    {
        return $this->nickname ?: $this->name;
    }

    /**
     * Get profile picture URL
     */
    public function getProfilePictureUrl()
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        
        // Default avatar based on gender
        $defaultAvatar = $this->gender === 'female' ? 'default-female.png' : 'default-male.png';
        return asset('images/' . $defaultAvatar);
    }

    /**
     * Get age display
     */
    public function getAgeDisplay()
    {
        return $this->age ? $this->age . ' tahun' : 'Usia tidak diisi';
    }

    /**
     * Get bio excerpt
     */
    public function getBioExcerpt($length = 100)
    {
        if (!$this->bio) {
            return 'Belum ada bio';
        }

        return strlen($this->bio) > $length 
            ? substr($this->bio, 0, $length) . '...' 
            : $this->bio;
    }
}