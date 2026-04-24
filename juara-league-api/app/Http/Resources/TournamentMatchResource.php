<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentMatchResource extends JsonResource
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
            'stage_id' => $this->stage_id,
            'group_id' => $this->group_id,
            'round' => $this->round,
            'match_number' => $this->match_number,

            // New multi-participant list
            'participants' => MatchParticipantResource::collection($this->whenLoaded('matchParticipants')),

            // Participants (Mapped from matchParticipants for backward compatibility)
            'participant_1' => $this->getLegacyParticipant(1),
            'participant_2' => $this->getLegacyParticipant(2),
            'winner' => new ParticipantResource($this->whenLoaded('winner')),

            // Raw IDs (for legacy support)
            'participant_1_id' => $this->getLegacyParticipantId(1),
            'participant_2_id' => $this->getLegacyParticipantId(2),
            'winner_id' => $this->winner_id,

            // Match state
            'status' => $this->status,
            'bracket_side' => $this->bracket_side,

            // Bracket linking
            'next_match_winner_id' => $this->next_match_winner_id,
            'next_match_loser_id' => $this->next_match_loser_id,

            // Scores
            'scores' => $this->scores,

            // Games (per-game results)
            'games' => GameResource::collection($this->whenLoaded('games')),

            'scheduled_at' => $this->scheduled_at,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    protected function getLegacyParticipant(int $slot)
    {
        if (!$this->relationLoaded('matchParticipants')) {
            return null;
        }

        $mp = $this->matchParticipants->where('slot', $slot)->first();
        return $mp ? new ParticipantResource($mp->participant) : null;
    }

    protected function getLegacyParticipantId(int $slot)
    {
        if (!$this->relationLoaded('matchParticipants')) {
            return null;
        }

        $mp = $this->matchParticipants->where('slot', $slot)->first();
        return $mp ? $mp->participant_id : null;
    }
}
