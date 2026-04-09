<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory, HasUlids;
    protected $fillable = [
        'tournament_id',
        'name',
        'type',
        'order',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'order' => 'integer',
    ];

    /**
     * Get the tournament that owns the stage.
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }
}
