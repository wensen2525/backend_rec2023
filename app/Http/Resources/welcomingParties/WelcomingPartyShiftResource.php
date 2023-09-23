<?php

namespace App\Http\Resources\welcomingParties;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WelcomingPartyShiftResource extends JsonResource
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
            'shift' => $this->shift,
            'quota' => $this->quota,
            'created_at' => date('Y-m-d', strtotime($this->created_at)),
            'updated_at' => $this->updated_at == null ? null : date('Y-m-d', strtotime($this->updated_at))
        ];
    }
}
