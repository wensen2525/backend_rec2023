<?php

namespace App\Http\Resources\contacts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactPersonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            "id" => $this->id,
            "region" => [
                'region_id' => $this->region->id, 
                'region_init' => $this->region->region_init, 
                'region' => $this->region->region, 
            ],
            "name" => $this->name,
            "line" => $this->line,
            "email" => $this->email,
            "phone_number" => $this->phone_number,
            "type" => $this->type,
        ];
    }
}
