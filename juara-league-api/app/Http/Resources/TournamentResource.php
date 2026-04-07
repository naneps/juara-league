<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'user' => new UserResource($this->whenLoaded('user')),
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'status' => $this->status,
            'mode' => $this->mode,
            'participant_type' => $this->participant_type,
            'team_size' => $this->team_size,
            'bracket_type' => $this->bracket_type,
            'venue' => $this->venue,
            'banner_url' => $this->banner_url,
            'prize_pool' => $this->prize_pool,
            'entry_fee' => $this->entry_fee,
            'max_participants' => $this->max_participants,
            'current_participants' => $this->participants()->where('status', '!=', 'rejected')->count(),
            'registration_start_at' => $this->registration_start_at,
            'registration_end_at' => $this->registration_end_at,
            'start_at' => $this->start_at,
            'sport' => new SportResource($this->whenLoaded('sport')),
            'staff' => TournamentStaffResource::collection($this->whenLoaded('staff')),
            'stages' => $this->whenLoaded('stages'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
