<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        'description',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Category memiliki banyak swipes
     */
    public function swipes()
    {
        return $this->hasMany(Swipe::class);
    }

    /**
     * Category memiliki banyak connections
     */
    public function connections()
    {
        return $this->hasMany(Connection::class);
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope untuk hanya category yang active
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk order berdasarkan sort_order
     */
    public function scopeOrdered(Builder $query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope untuk search category berdasarkan name atau description
     */
    public function scopeSearch(Builder $query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'like', '%' . $term . '%')
              ->orWhere('description', 'like', '%' . $term . '%');
        });
    }

    // ========================================
    // STATIC METHODS
    // ========================================

    /**
     * Get all active categories ordered
     */
    public static function getActiveCategories()
    {
        return self::active()->ordered()->get();
    }

    /**
     * Find category by slug
     */
    public static function findBySlug($slug)
    {
        return self::where('slug', $slug)->first();
    }

    /**
     * Get categories with stats (swipe count, connection count)
     */
    public static function getWithStats()
    {
        return self::withCount(['swipes', 'connections'])
                   ->ordered()
                   ->get();
    }

    /**
     * Get popular categories (most swipes)
     */
    public static function getPopular($limit = 5)
    {
        return self::withCount('swipes')
                   ->active()
                   ->orderBy('swipes_count', 'desc')
                   ->limit($limit)
                   ->get();
    }

    // ========================================
    // INSTANCE METHODS
    // ========================================

    /**
     * Get total swipes in this category
     */
    public function getTotalSwipes()
    {
        return $this->swipes()->count();
    }

    /**
     * Get total likes in this category
     */
    public function getTotalLikes()
    {
        return $this->swipes()->where('action', 'like')->count();
    }

    /**
     * Get total passes in this category
     */
    public function getTotalPasses()
    {
        return $this->swipes()->where('action', 'pass')->count();
    }

    /**
     * Get like percentage in this category
     */
    public function getLikePercentage()
    {
        $totalSwipes = $this->getTotalSwipes();
        
        if ($totalSwipes === 0) {
            return 0;
        }

        $totalLikes = $this->getTotalLikes();
        return round(($totalLikes / $totalSwipes) * 100, 1);
    }

    /**
     * Get total connections in this category
     */
    public function getTotalConnections()
    {
        return $this->connections()->count();
    }

    /**
     * Get connection rate (connections / likes * 100)
     */
    public function getConnectionRate()
    {
        $totalLikes = $this->getTotalLikes();
        
        if ($totalLikes === 0) {
            return 0;
        }

        // Connection terbentuk dari mutual likes, jadi bagi 2
        $totalConnections = $this->getTotalConnections();
        return round(($totalConnections / ($totalLikes / 2)) * 100, 1);
    }

    /**
     * Get users who are looking for this category
     */
    public function getUsersLookingFor()
    {
        return User::whereJsonContains('looking_for', $this->slug)
                   ->verified()
                   ->get();
    }

    /**
     * Get active users in this category (who swiped recently)
     */
    public function getActiveUsers($days = 7)
    {
        $userIds = $this->swipes()
                       ->where('swiped_at', '>=', now()->subDays($days))
                       ->pluck('swiper_id')
                       ->unique();

        return User::whereIn('id', $userIds)
                   ->verified()
                   ->get();
    }

    /**
     * Check if category is popular (top 3 by swipes)
     */
    public function isPopular()
    {
        $popularCategories = self::getPopular(3)->pluck('id')->toArray();
        return in_array($this->id, $popularCategories);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get icon class for UI
     */
    public function getIconClass()
    {
        return $this->icon ?: 'fas fa-folder';
    }

    /**
     * Get color for UI (with fallback)
     */
    public function getColor()
    {
        return $this->color ?: '#007bff';
    }

    /**
     * Get route parameter (using slug)
     */
    public function getRouteKey()
    {
        return $this->slug;
    }

    /**
     * Resolve route binding by slug
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('slug', $value)->first();
    }

    /**
     * Get category card HTML for UI
     */
    public function getCardHtml()
    {
        $stats = sprintf(
            '%d swipes, %d connections',
            $this->getTotalSwipes(),
            $this->getTotalConnections()
        );

        return sprintf(
            '<div class="category-card" style="border-left: 4px solid %s;">
                <div class="category-header">
                    <i class="%s" style="color: %s;"></i>
                    <h4>%s</h4>
                </div>
                <p class="category-description">%s</p>
                <small class="category-stats">%s</small>
            </div>',
            $this->getColor(),
            $this->getIconClass(),
            $this->getColor(),
            $this->name,
            $this->description ?: 'Tidak ada deskripsi',
            $stats
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
            'slug' => $this->slug,
            'icon' => $this->getIconClass(),
            'color' => $this->getColor(),
            'description' => $this->description,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'stats' => [
                'total_swipes' => $this->getTotalSwipes(),
                'total_likes' => $this->getTotalLikes(),
                'total_passes' => $this->getTotalPasses(),
                'like_percentage' => $this->getLikePercentage(),
                'total_connections' => $this->getTotalConnections(),
                'connection_rate' => $this->getConnectionRate(),
                'is_popular' => $this->isPopular(),
            ],
        ];
    }

    // ========================================
    // URL GENERATION METHODS
    // ========================================

    /**
     * Get URL untuk swipe page di category ini
     */
    public function getSwipeUrl()
    {
        return route('swipe.category', $this->slug);
    }

    /**
     * Get URL untuk connections page di category ini
     */
    public function getConnectionsUrl()
    {
        return route('connections.category', $this->slug);
    }

    /**
     * Get URL untuk category detail page
     */
    public function getDetailUrl()
    {
        return route('categories.show', $this->slug);
    }

    // ========================================
    // MODEL EVENTS
    // ========================================

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug when creating
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = str()->slug($category->name);
            }
        });

        // Auto-update slug when name changes
        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = str()->slug($category->name);
            }
        });
    }
}