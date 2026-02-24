<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'company',
        'position',
        'platform',
        'applied_at',
        'status',
        'follow_up_at',
        'url',
        'notes',
        'location',
        'salary',
    ];


     protected $casts = [
        'applied_at'   => 'date',
        'follow_up_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     public function scopeNeedsFollowUp($query)
    {
        return $query
            ->where('status', 'envoyee')
            ->where('follow_up_at', '<=', now()->toDateString());
    }
}
