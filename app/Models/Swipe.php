<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Swipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'swiper_id',
        'swiped_id', 
        'category_id',
        'action',
        'swiped_at',
    ];

    protected $casts = [
        'swiped_at' => 'datetime',
    ];
}