<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;
    protected $fillable = [
        'tournament_id',
        'user_id',
        'team_id',
        'status',
        'payment_proof_url',
        'notes',
    ];

    /**
     * Get the tournament this participant belongs to.
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the user that is this participant (for individual mode).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the team that is this participant (for team mode).
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
