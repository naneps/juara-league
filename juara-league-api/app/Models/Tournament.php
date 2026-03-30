<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tournament extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'category',
        'status',
        'mode',
        'bracket_type',
        'venue',
        'banner_url',
        'prize_pool',
        'entry_fee',
        'max_participants',
        'registration_start_at',
        'registration_end_at',
        'start_at',
    ];

    protected $casts = [
        'prize_pool' => 'integer',
        'entry_fee' => 'integer',
        'max_participants' => 'integer',
        'registration_start_at' => 'datetime',
        'registration_end_at' => 'datetime',
        'start_at' => 'datetime',
    ];

    /**
     * Get the user that owns the tournament.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
