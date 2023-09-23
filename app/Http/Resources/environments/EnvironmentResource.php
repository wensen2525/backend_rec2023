<?php

namespace App\Http\Resources\environments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnvironmentResource extends JsonResource
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
            'env_code' => $this->env_code,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ];
    }
}
