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
  }