<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'tournament_id',
        'name',
        'type',
        'status',
        'participants_advance',
        'groups_count',
        'participants_per_group',
        'order',
        'settings',
    ];

    protected $appends = ['bo_format'];

    /**
     * Settings Accessors
     */
    public function getMatchFormat(): string
    {
        return $this->settings['match_format'] ?? 'best_of';
    }

    public function getScoringMethod(): string
    {
        return $this->settings['scoring_method'] ?? 'result_based';
    }

    public function getBoFormatAttribute(): string
    {
        if ($this->getMatchFormat() === 'best_of') {
            $winCondition = $this->getWinCondition();
            $games = ($winCondition * 2) - 1;
            return "bo{$games}";
        }
        return 'bo1';
    }

    public function getWinCondition(): int
    {
        return (int) ($this->settings['win_condition'] ?? 1);
    }

    public function getMatchRules(): array
    {
        return $this->settings['rules'] ?? [];
    }

    protected $casts = [
        'settings' => 'array',
        'order' => 'integer',
        'participants_advance' => 'integer',
        'groups_count' => 'integer',
        'participants_per_group' => 'integer',
    ];

    /**
     * Get the tournament that owns the stage.
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the groups for this stage (round robin).
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class)->orderBy('order');
    }

    /**
     * Get all matches for this stage.
     */
    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class);
    }

    /**
     * Check if the stage is still editable (pending status).
     */
    public function isEditable(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the stage is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the stage is ongoing.
     */
    public function isOngoing(): bool
    {
        return $this->status === 'ongoing';
    }

    /**
     * Check if the stage is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
