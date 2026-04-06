<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentStaffResource extends JsonResource
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
            'user' => new UserResource($this->user),
            'role' => $this->role,
            'created_at' => $this->created_at,
        ];
    }
}
