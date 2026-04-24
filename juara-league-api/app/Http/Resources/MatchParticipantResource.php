<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchParticipantResource extends JsonResource
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
            'match_id' => $this->match_id,
            'participant_id' => $this->participant_id,
            'slot' => $this->slot,
            'score' => $this->score,
            'rank' => $this->rank,
            'is_winner' => $this->is_winner,
            'metadata' => $this->metadata,
            
            // Nested participant details — always included via $with on model
            'participant' => $this->participant ? new ParticipantResource($this->participant) : null,
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
