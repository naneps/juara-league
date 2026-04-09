<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentStaff extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'tournament_staff';

    protected $fillable = [
        'tournament_id',
        'user_id',
        'role',
    ];

    /**
     * Get the tournament the staff member belongs to.
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the user that is a staff member.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
