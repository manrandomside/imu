<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Connection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user1_id',
        'user2_id',
        'category_id',
        'status',
        'match_score',
        'connected_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'match_score' => 'decimal:2',
        'connected_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * User pertama dalam connection
     */
    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    /**
     * User kedua dalam connection
     */
    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    /**
     * Category context untuk connection ini
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeStatus(Builder $query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk connections yang accepted
     */
    public function scopeAccepted(Builder $query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope untuk connections yang pending
     */
    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk connections yang blocked
     */
    public function scopeBlocked(Builder $query)
    {
        return $query->where('status', 'blocked');
    }

    /**
     * Scope untuk filter berdasarkan category
     */
    public function scopeInCategory(Builder $query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope untuk connections yang melibatkan user tertentu
     */
    public function scopeForUser(Builder $query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('user1_id', $userId)
              ->orWhere('user2_id', $userId);
        });
    }

    /**
     * Scope untuk recent connections
     */
    public function scopeRecent(Builder $query, $days = 7)
    {
        return $query->where('connected_at', '>=', now()->subDays($days));
    }

    /**
     * Scope untuk high score connections
     */
    public function scopeHighScore(Builder $query, $minScore = 0.7)
    {
        return $query->where('match_score', '>=', $minScore);
    }

    // ========================================
    // STATIC METHODS - CORE CONNECTION LOGIC
    // ========================================

    /**
     * Create connection dari mutual like
     */
    public static function createConnection($user1Id, $user2Id, $categoryId)
    {
        // Ensure user1_id is always smaller (untuk unique constraint)
        if ($user1Id > $user2Id) {
            [$user1Id, $user2Id] = [$user2Id, $user1Id];
        }

        // Check if connection already exists
        $existingConnection = self::where('user1_id', $user1Id)
                                 ->where('user2_id', $user2Id)
                                 ->where('category_id', $categoryId)
                                 ->first();

        if ($existingConnection) {
            return $existingConnection;
        }

        // Calculate match score
        $user1 = User::find($user1Id);
        $user2 = User::find($user2Id);
        $matchScore = $user1->calculateMatchScore($user2);

        // Create connection
        return self::create([
            'user1_id' => $user1Id,
            'user2_id' => $user2Id,
            'category_id' => $categoryId,
            'status' => 'accepted', // Default to accepted for mutual likes
            'match_score' => $matchScore,
            'connected_at' => now(),
        ]);
    }

    /**
     * Get connections untuk user tertentu
     */
    public static function getUserConnections($userId, $categoryId = null, $status = null)
    {
        $query = self::forUser($userId)
                     ->with(['user1', 'user2', 'category']);

        if ($categoryId) {
            $query->inCategory($categoryId);
        }

        if ($status) {
            $query->status($status);
        }

        return $query->orderBy('connected_at', 'desc')->get();
    }

    /**
     * Check apakah dua user memiliki connection di category tertentu
     */
    public static function hasConnection($user1Id, $user2Id, $categoryId = null)
    {
        // Ensure correct order
        if ($user1Id > $user2Id) {
            [$user1Id, $user2Id] = [$user2Id, $user1Id];
        }

        $query = self::where('user1_id', $user1Id)
                     ->where('user2_id', $user2Id);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->exists();
    }

    /**
     * Get connection antara dua user di category tertentu
     */
    public static function getUserConnection($user1Id, $user2Id, $categoryId)
    {
        // Ensure correct order
        if ($user1Id > $user2Id) {
            [$user1Id, $user2Id] = [$user2Id, $user1Id];
        }

        return self::where('user1_id', $user1Id)
                   ->where('user2_id', $user2Id)
                   ->where('category_id', $categoryId)
                   ->first();
    }

    /**
     * Get recommended connections berdasarkan match score
     */
    public static function getRecommendedConnections($userId, $limit = 10)
    {
        return self::forUser($userId)
                   ->accepted()
                   ->highScore(0.5) // Minimum 50% match
                   ->orderBy('match_score', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get connection statistics
     */
    public static function getStats($categoryId = null)
    {
        $query = self::query();

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $totalConnections = $query->count();
        $acceptedConnections = $query->accepted()->count();
        $pendingConnections = $query->pending()->count();
        $blockedConnections = $query->blocked()->count();
        $recentConnections = $query->recent()->count();

        $averageMatchScore = $query->avg('match_score') ?? 0;

        return [
            'total_connections' => $totalConnections,
            'accepted_connections' => $acceptedConnections,
            'pending_connections' => $pendingConnections,
            'blocked_connections' => $blockedConnections,
            'recent_connections' => $recentConnections,
            'average_match_score' => round($averageMatchScore, 2),
            'acceptance_rate' => $totalConnections > 0 ? round(($acceptedConnections / $totalConnections) * 100, 1) : 0,
        ];
    }

    // ========================================
    // INSTANCE METHODS
    // ========================================

    /**
     * Get the other user in this connection
     */
    public function getOtherUser($currentUserId)
    {
        if ($this->user1_id == $currentUserId) {
            return $this->user2;
        } elseif ($this->user2_id == $currentUserId) {
            return $this->user1;
        }

        return null;
    }

    /**
     * Check if connection is accepted
     */
    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if connection is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if connection is blocked
     */
    public function isBlocked()
    {
        return $this->status === 'blocked';
    }

    /**
     * Accept the connection
     */
    public function accept()
    {
        return $this->update(['status' => 'accepted']);
    }

    /**
     * Block the connection
     */
    public function block()
    {
        return $this->update(['status' => 'blocked']);
    }

    /**
     * Set back to pending
     */
    public function setPending()
    {
        return $this->update(['status' => 'pending']);
    }

    /**
     * Check if connection involves specific user
     */
    public function involvesUser($userId)
    {
        return $this->user1_id == $userId || $this->user2_id == $userId;
    }

    /**
     * Get connection age in days
     */
    public function getAgeInDays()
    {
        return $this->connected_at->diffInDays(now());
    }

    /**
     * Check if connection is new (less than 3 days)
     */
    public function isNew()
    {
        return $this->getAgeInDays() <= 3;
    }

    /**
     * Get match score display
     */
    public function getMatchScoreDisplay()
    {
        if (!$this->match_score) {
            return 'N/A';
        }

        $percentage = round($this->match_score * 100);
        return $percentage . '%';
    }

    /**
     * Get match score level (Low, Medium, High)
     */
    public function getMatchScoreLevel()
    {
        if (!$this->match_score) {
            return 'Unknown';
        }

        if ($this->match_score >= 0.8) {
            return 'High';
        } elseif ($this->match_score >= 0.5) {
            return 'Medium';
        } else {
            return 'Low';
        }
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge()
    {
        $colors = [
            'accepted' => 'success',
            'pending' => 'warning',
            'blocked' => 'danger',
        ];

        $color = $colors[$this->status] ?? 'secondary';
        $text = ucfirst($this->status);

        return "<span class=\"badge bg-{$color}\">{$text}</span>";
    }

    /**
     * Get time since connection
     */
    public function getTimeSinceConnection()
    {
        return $this->connected_at->diffForHumans();
    }

    /**
     * Convert to array for API/JSON response
     */
    public function toApiArray($currentUserId = null)
    {
        $otherUser = $currentUserId ? $this->getOtherUser($currentUserId) : null;

        return [
            'id' => $this->id,
            'user1' => [
                'id' => $this->user1->id,
                'name' => $this->user1->getDisplayName(),
                'profile_picture' => $this->user1->getProfilePictureUrl(),
            ],
            'user2' => [
                'id' => $this->user2->id,
                'name' => $this->user2->getDisplayName(),
                'profile_picture' => $this->user2->getProfilePictureUrl(),
            ],
            'other_user' => $otherUser ? [
                'id' => $otherUser->id,
                'name' => $otherUser->getDisplayName(),
                'profile_picture' => $otherUser->getProfilePictureUrl(),
                'bio' => $otherUser->getBioExcerpt(),
            ] : null,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'icon' => $this->category->getIconClass(),
                'color' => $this->category->getColor(),
            ],
            'status' => $this->status,
            'match_score' => $this->match_score,
            'match_score_display' => $this->getMatchScoreDisplay(),
            'match_score_level' => $this->getMatchScoreLevel(),
            'connected_at' => $this->connected_at->toISOString(),
            'time_since_connection' => $this->getTimeSinceConnection(),
            'is_new' => $this->isNew(),
            'age_in_days' => $this->getAgeInDays(),
        ];
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

        // Set connected_at timestamp when creating
        static::creating(function ($connection) {
            if (empty($connection->connected_at)) {
                $connection->connected_at = now();
            }

            // Ensure user1_id is always smaller than user2_id
            if ($connection->user1_id > $connection->user2_id) {
                [$connection->user1_id, $connection->user2_id] = [$connection->user2_id, $connection->user1_id];
            }
        });
    }
}