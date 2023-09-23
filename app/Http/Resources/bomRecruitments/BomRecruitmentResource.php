<?php

namespace App\Http\Resources\bomRecruitments;

use Illuminate\Http\Request;
use App\Http\Resources\terms\TermResource;
use App\Http\Resources\regions\RegionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BomRecruitmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "user" => [
                'id' => $this->user->id,
                'nim' => $this->user->nim,
                'name' => $this->user->name,
                'line_id' => $this->user->line_id,
                'phone_number' => $this->user->phone_number,
                'email' => $this->user->email,
            ],
            'region' => $this->whenNotNull(new RegionResource($this->user->region)),
            'term' => $this->whenNotNull(new TermResource($this->term)),
            'first_preference' => $this->first_preference == null ? null : [
                'id' => $this->first_preference->id,
                'name' => $this->first_preference->name,
            ],
            'first_preference_reason' => $this->first_preference_reason,
            'second_preference' => $this->second_preference == null ? null : [
                'id' =>  $this->second_preference->id ,
                'name' =>  $this->second_preference->name ,
            ],
            'second_preference_reason' => $this->second_preference_reason,
            'third_preference' => $this->third_preference== null ?  null : [
                'id' => $this->third_preference->id,
                'name' => $this->third_preference->name,
            ],
            'third_preference_reason' => $this->third_preference_reason,
            'created_at' => date('Y-m-d', strtotime($this->created_at)),
            'updated_at' => $this->updated_at == null ? null : date('Y-m-d', strtotime($this->updated_at)),
            'deleted_at' => $this->deleted_at == null ? null : date('Y-m-d', strtotime($this->deleted_at)),
        ];
    }
}
