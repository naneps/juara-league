<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tournament extends Model
{
    use HasFactory, SoftDeletes, HasUlids;
    
    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'user_id',
        'sport_id',
        'title',
        'slug',
        'description',
        'category',
        'status',
        'approval_status',
        'mode',
        'venue_type',
        'bracket_type',
        'participant_type',
        'team_size',
        'venue',
        'banner_url',
        'prize_pool',
        'entry_fee',
        'prize_description',
        'max_participants',
        'registration_start_at',
        'registration_end_at',
        'start_at',
    ];

    protected $casts = [
        'prize_pool' => 'decimal:2',
        'entry_fee' => 'decimal:2',
        'max_participants' => 'integer',
        'team_size' => 'integer',
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

    /**
     * Get the sport that the tournament belongs to.
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Get the stages for the tournament.
     */
    public function stages()
    {
        return $this->hasMany(Stage::class)->orderBy('order');
    }

    /**
     * Get the participants for the tournament.
     */
    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    /**
     * Get the staff members for the tournament.
     */
    public function staff()
    {
        return $this->hasMany(TournamentStaff::class);
    }

    /**
     * Get the approval logs for the tournament.
     */
    public function approvals()
    {
        return $this->hasMany(TournamentApproval::class);
    }

    /**
     * Get the prizes for the tournament.
     */
    public function prizes()
    {
        return $this->hasMany(TournamentPrize::class)->orderBy('order');
    }
    /**
     * Get a human-readable summary of the tournament format based on its stages.
     */
    public function getFormatSummary(): string
    {
        $stages = $this->stages;
        
        if ($stages->isEmpty()) {
            return $this->bracket_type ? str_replace('_', ' ', ucwords($this->bracket_type, '_')) : 'Format belum diatur';
        }

        $types = $stages->map(function ($stage) {
            return match ($stage->type) {
                'single_elim' => 'Single Elimination',
                'double_elim' => 'Double Elimination',
                'round_robin' => 'Round Robin',
                'swiss' => 'Swiss',
                default => str_replace('_', ' ', ucwords($stage->type, '_')),
            };
        })->unique();

        if ($types->count() === 1) {
            return $types->first();
        }

        return $types->implode(' + ');
    }

    /**
     * Get a summary list of stages for quick information.
     */
    public function getStagesInfo(): array
    {
        return $this->stages->map(function ($stage) {
            return [
                'name' => $stage->name,
                'type' => $stage->type,
                'rules' => [
                    'bo_format' => $stage->bo_format,
                    'groups_count' => $stage->groups_count,
                    'participants_advance' => $stage->participants_advance,
                ]
            ];
        })->toArray();
    }
}
