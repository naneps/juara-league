<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StageResource extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'bo_format' => $this->bo_format,
            'participants_advance' => $this->participants_advance,
            'groups_count' => $this->groups_count,
            'participants_per_group' => $this->participants_per_group,
            'order' => $this->order,
            'settings' => $this->settings,

            // Relations
            'groups' => GroupResource::collection($this->whenLoaded('groups')),
            'matches' => TournamentMatchResource::collection($this->whenLoaded('matches')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
