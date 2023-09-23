<?php

namespace App\Http\Resources\partnerships;

use Illuminate\Http\Request;
use App\Http\Resources\regions\RegionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnershipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id' => $this->id,
            'region' => $this->whenNotNull(new RegionResource($this->region)), 
            'name' => $this->name,
            'description' => $this->description,
            'promo' => $this->promo,
            'logo' => $this->logo,
        ];
    }
}
