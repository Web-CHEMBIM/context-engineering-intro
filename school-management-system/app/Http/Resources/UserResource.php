<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'profile_photo_url' => $this->profile_photo_url,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
            'roles' => $this->whenLoaded('roles', function() {
                return $this->roles->pluck('name');
            }),
            'permissions' => $this->whenLoaded('permissions', function() {
                return $this->permissions->pluck('name');
            }),
            'student' => new StudentResource($this->whenLoaded('student')),
            'teacher' => new TeacherResource($this->whenLoaded('teacher')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}