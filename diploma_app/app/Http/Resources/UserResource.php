<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
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
            'last_name' => $this->last_name,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'email_verified_at' => $this->whenHas('email_verified_at'),
            'created_at' => $this->whenHas('created_at'),
            'updated_at' => $this->whenHas('updated_at'),
            'role_id' => $this->role_id,
            'role' => new RoleResource($this->whenLoaded('role')),
        ];
    }
}
