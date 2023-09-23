<?php

namespace App\Http\Resources\welcomingParties;

use Illuminate\Http\Request;
use App\Http\Resources\majors\MajorResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\welcomingParties\WelcomingPartyShiftResource;

class WelcomingPartyResource extends JsonResource
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
            'shift' => $this->whenNotNull(new WelcomingPartyShiftResource($this->welcome_party_shifts)),
            'is_confirmed' => $this->is_confirmed,  
            'name' => $this->name,  
            'type' => $this->type,  
            'campus_location' => $this->campus_location,  
            'nim' => $this->nim,  
            'major' => $this->whenNotNull(new MajorResource($this->major)),  
            'email' => $this->email,  
            'phone_number' => $this->phone_number,  
            'line_id' => $this->line_id,  
            'instagram' => $this->instagram,  
            'proof' => $this->proof,  
            'created_at' => date('Y-m-d', strtotime($this->created_at)),
            'updated_at' => $this->updated_at == null ? null : date('Y-m-d', strtotime($this->updated_at)),
            'deleted_at' => $this->deleted_at == null ? null : date('Y-m-d', strtotime($this->deleted_at)),
        ];
    }
}
