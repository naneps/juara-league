<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchParticipant extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'match_id',
        'participant_id',
        'slot',
        'score',
        'rank',
        'is_winner',
        'metadata',
    ];

    protected $casts = [
        'slot'     => 'integer',
        'score'    => 'float',
        'rank'     => 'integer',
        'is_winner'=> 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Always load the participant with user/team.
     */
    protected $with = ['participant.user', 'participant.team'];

    /**
     * Get the match this participant belongs to.
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class, 'match_id');
    }

    /**
     * Get the participant details.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }
}
