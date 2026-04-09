<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentApproval extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'tournament_id',
        'status',
        'auto_check_log',
        'reviewed_by',
        'reviewed_at',
        'note',
    ];

    protected $casts = [
        'auto_check_log' => 'array',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the tournament this approval belongs to.
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the user who reviewed this tournament.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
