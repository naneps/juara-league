<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentPrize extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'tournament_id',
        'tier_name',
        'prize_amount',
        'description',
        'rank',
        'order',
    ];

    protected $casts = [
        'prize_amount' => 'decimal:2',
        'rank' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the tournament that owns the prize.
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }
}
