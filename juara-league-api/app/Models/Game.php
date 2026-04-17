<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'match_id',
        'game_number',
        'winner_id',
        'score_p1',
        'score_p2',
        'status',
    ];

    protected $casts = [
        'game_number' => 'integer',
        'score_p1' => 'integer',
        'score_p2' => 'integer',
    ];

    /**
     * Get the match this game belongs to.
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class, 'match_id');
    }

    /**
     * Get the winner of this game.
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'winner_id');
    }
}
