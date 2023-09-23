<?php

namespace App\Http\Resources\talentRecruitments;

use Illuminate\Http\Request;
use App\Http\Resources\terms\TermResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TalentRecruitmentResource extends JsonResource
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
            'term' => $this->whenNotNull(new TermResource($this->term)),
            'region' => $this->region,
            'nim' => $this->nim,
            'name' => $this->name,
            'gender' => $this->gender,
            'major' => $this->major == null ? null : [
                'id' => $this->major->id,
                'faculty' => $this->major->faculty,
                'major_name' => $this->major->major_name,
            ],
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'alt_phone_number' => $this->alt_phone_number,
            'line_id' => $this->line_id,
            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date,
            'allergy' => $this->allergy,
            'address' => $this->address,
            'first_talent_field' => $this->first_talent_field == null ? null : [
                'id' => $this->first_talent_field->id, 
                'name' => $this->first_talent_field->name, 
            ],
            'second_talent_field' => $this->second_talent_field == null ? null : [
                'id' => $this->second_talent_field->id, 
                'name' => $this->second_talent_field->name, 
            ],
            'created_at' => date('Y-m-d', strtotime($this->created_at)),
            'updated_at' => date('Y-m-d', strtotime($this->updated_at)),
            'deleted_at' => $this->deleted_at == null ? null :date('Y-m-d', strtotime($this->deleted_at))  ,
        ];
    }
}
