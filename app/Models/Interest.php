<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Interest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'icon',
        'category',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Many-to-Many relationship with User
     * Interest dapat dimiliki oleh banyak users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_interests')
                    ->withTimestamps();
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope untuk filter interests berdasarkan category
     */
    public function scopeByCategory(Builder $query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope untuk get interests yang populer (banyak user)
     */
    public function scopePopular(Builder $query, $limit = 10)
    {
        return $query->withCount('users')
                     ->orderBy('users_count', 'desc')
                     ->limit($limit);
    }

    /**
     * Scope untuk get interests dengan user count
     */
    public function scopeWithUserCount(Builder $query)
    {
        return $query->withCount('users');
    }

    // ========================================
    // STATIC METHODS
    // ========================================

    /**
     * Get all unique categories from interests
     */
    public static function getAllCategories()
    {
        return self::distinct('category')
                   ->whereNotNull('category')
                   ->pluck('category')
                   ->sort()
                   ->values();
    }

    /**
     * Get interests grouped by category
     */
    public static function getGroupedByCategory()
    {
        return self::orderBy('category')
                   ->orderBy('name')
                   ->get()
                   ->groupBy('category');
    }

    /**
     * Search interests by name
     */
    public static function search($term)
    {
        return self::where('name', 'like', '%' . $term . '%')
                   ->orderBy('name')
                   ->get();
    }

    // ========================================
    // INSTANCE METHODS
    // ========================================

    /**
     * Get the number of users who have this interest
     */
    public function getUserCount()
    {
        return $this->users()->count();
    }

    /**
     * Get popularity percentage (based on total users)
     */
    public function getPopularityPercentage()
    {
        $totalUsers = User::verified()->count();
        
        if ($totalUsers === 0) {
            return 0;
        }

        $usersWithThisInterest = $this->getUserCount();
        return round(($usersWithThisInterest / $totalUsers) * 100, 1);
    }

    /**
     * Check if interest is popular (more than 10% of users)
     */
    public function isPopular()
    {
        return $this->getPopularityPercentage() > 10;
    }

    /**
     * Get similar interests (same category)
     */
    public function getSimilarInterests($limit = 5)
    {
        return self::where('category', $this->category)
                   ->where('id', '!=', $this->id)
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get users with this interest from specific fakultas
     */
    public function getUsersByFakultas($fakultas)
    {
        return $this->users()
                    ->where('fakultas', $fakultas)
                    ->verified()
                    ->get();
    }

    /**
     * Get users with this interest by gender
     */
    public function getUsersByGender($gender)
    {
        return $this->users()
                    ->where('gender', $gender)
                    ->verified()
                    ->get();
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get icon class/path for UI
     */
    public function getIconClass()
    {
        return $this->icon ?: 'default-icon';
    }

    /**
     * Get category display name (capitalized)
     */
    public function getCategoryDisplay()
    {
        return ucfirst($this->category ?: 'Lainnya');
    }

    /**
     * Get category color for UI
     */
    public function getCategoryColor()
    {
        return match($this->category) {
            'hobby' => '#28a745',      // Green
            'sport' => '#007bff',      // Blue  
            'entertainment' => '#ffc107', // Yellow
            'creative' => '#e83e8c',   // Pink
            'social' => '#17a2b8',     // Cyan
            'academic' => '#6f42c1',   // Purple
            default => '#6c757d'       // Gray
        };
    }

    /**
     * Get interest badge HTML
     */
    public function getBadgeHtml()
    {
        $color = $this->getCategoryColor();
        $count = $this->getUserCount();
        
        return sprintf(
            '<span class="badge" style="background-color: %s; color: white;">
                <i class="%s"></i> %s (%d users)
            </span>',
            $color,
            $this->getIconClass(),
            $this->name,
            $count
        );
    }

    /**
     * Convert to array for API/JSON response
     */
    public function toApiArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $this->getIconClass(),
            'category' => $this->category,
            'category_display' => $this->getCategoryDisplay(),
            'category_color' => $this->getCategoryColor(),
            'user_count' => $this->getUserCount(),
            'popularity_percentage' => $this->getPopularityPercentage(),
            'is_popular' => $this->isPopular(),
        ];
    }

    // ========================================
    // BOOT METHODS
    // ========================================

    /**
     * Boot the model (jika perlu event listeners)
     */
    protected static function boot()
    {
        parent::boot();

        // Optional: Add model events
        // static::created(function ($interest) {
        //     // Do something when interest is created
        // });
    }
}