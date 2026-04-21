<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StandingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'participant_id' => $this['participant_id'],
            'participant' => new ParticipantResource($this['participant']),
            'played' => $this['played'],
            'win' => $this['win'],
            'draw' => $this['draw'],
            'loss' => $this['loss'],
            'goals_for' => $this['goals_for'],
            'goals_against' => $this['goals_against'],
            'goal_difference' => $this['goal_difference'],
            'points' => $this['points'],
            'rank' => $this->resource['rank'] ?? null,
        ];
    }
}
