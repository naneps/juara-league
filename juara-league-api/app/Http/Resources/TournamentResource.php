<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class TournamentResource extends JsonResource
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
            'sport_id' => $this->sport_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'status' => $this->status,
            'approval_status' => $this->approval_status,
            'mode' => $this->mode,
            'venue_type' => $this->venue_type,
            'participant_type' => $this->participant_type,
            'team_size' => $this->team_size,
            'bracket_type' => $this->bracket_type,
            'venue' => $this->venue,
            'banner_url' => $this->banner_url,
            'prize_pool' => $this->prize_pool,
            'prize_description' => $this->prize_description,
            'entry_fee' => $this->entry_fee,
            'max_participants' => $this->max_participants,
            'current_participants' => $this->participants()->where('status', '!=', 'rejected')->count(),
            'participants_count' => $this->participants()->where('status', '!=', 'rejected')->count(),
            'registration_start_at' => $this->registration_start_at,
            'registration_end_at' => $this->registration_end_at,
            'start_at' => $this->start_at,
            'sport' => new SportResource($this->whenLoaded('sport')),
            'staff' => TournamentStaffResource::collection($this->whenLoaded('staff')),
            'stages' => StageResource::collection($this->whenLoaded('stages')),
            'user_participation' => $this->getUserParticipation(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get the current user's participation status for this tournament.
     */
    private function getUserParticipation(): ?array
    {
        // Require explicit sanctum guard check for public routes
        $user = auth('sanctum')->user();
        if (!$user) return null;

        // Now user_id is always stored (even for team tournaments)
        $participation = $this->participants()
            ->where('user_id', $user->id)
            ->first();

        if (!$participation) return null;

        return [
            'id' => $participation->id,
            'status' => $participation->status,
            'team_id' => $participation->team_id,
        ];
    }
}
    