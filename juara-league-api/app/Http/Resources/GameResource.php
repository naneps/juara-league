<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
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
            'game_number' => $this->game_number,
            'winner' => new ParticipantResource($this->whenLoaded('winner')),
            'winner_id' => $this->winner_id,
            'score_p1' => $this->score_p1,
            'score_p2' => $this->score_p2,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
