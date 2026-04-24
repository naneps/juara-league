<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentMatch extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'tournament_matches';

    protected $fillable = [
        'stage_id',
        'group_id',
        'round',
        'match_number',
        'winner_id',
        'status',
        'bracket_side',
        'next_match_winner_id',
        'next_match_loser_id',
        'scores',
        'scheduled_at',
        'completed_at',
    ];

    protected $casts = [
        'round' => 'integer',
        'match_number' => 'integer',
        'scores' => 'array',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Always eager-load participants from pivot table.
     * This prevents TBD showing up anywhere in the app.
     */
    protected $with = ['matchParticipants'];

    /**
     * Get the stage this match belongs to.
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    /**
     * Get the group this match belongs to (for round robin).
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the winner of this match.
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'winner_id');
    }

    /**
     * Get the next match for the winner.
     */
    public function nextMatchWinner(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class, 'next_match_winner_id');
    }

    /**
     * Get the next match for the loser (double elimination).
     */
    public function nextMatchLoser(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class, 'next_match_loser_id');
    }

    /**
     * Check if match is a bye.
     */
    public function isBye(): bool
    {
        return $this->status === 'bye';
    }

    /**
     * Check if match is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if match is ongoing.
     */
    public function isOngoing(): bool
    {
        return $this->status === 'ongoing';
    }

    /**
     * Get the participants for this match (New Pivot Table).
     */
    public function matchParticipants(): HasMany
    {
        return $this->hasMany(MatchParticipant::class, 'match_id');
    }

    /**
     * Get the games for this match.
     */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'match_id')->orderBy('game_number');
    }

    /**
     * Helper to get the winner from match_participants table.
     */
    public function getActualWinner()
    {
        return $this->matchParticipants()->where('is_winner', true)->first()?->participant;
    }

    /**
     * Helper to check if a specific participant is in this match.
     */
    public function hasParticipant(string $participantId): bool
    {
        return $this->matchParticipants()->where('participant_id', $participantId)->exists();
    }
}
