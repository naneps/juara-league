<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tournament_id' => $this->tournament_id,
            'user_id' => $this->user_id,
            'team_id' => $this->team_id,
            'status' => $this->status,
            'payment_proof_url' => $this->payment_proof_url,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Load relations if requested/loaded
            'user' => new UserResource($this->whenLoaded('user')),
            'team' => $this->whenLoaded('team'), // Assuming Team doesn't have a resource yet or is simple
            'tournament' => new TournamentResource($this->whenLoaded('tournament')),
        ];
    }
}
