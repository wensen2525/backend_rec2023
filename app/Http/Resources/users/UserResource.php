<?php

namespace App\Http\Resources\users;

use Illuminate\Http\Request;
use App\Http\Resources\terms\TermResource;
use App\Http\Resources\majors\MajorResource;
use App\Http\Resources\regions\RegionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\toeflDetails\ToeflDetailResource;
use App\Http\Resources\memberPayments\MemberPaymentResource;
use App\Http\Resources\bomRecruitments\BomRecruitmentResource;
use App\Http\Resources\welcomingParties\WelcomingPartyResource;
use App\Http\Resources\talentRecruitments\TalentRecruitmentResource;

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
            'role' => $this->role,
            'term' => $this->whenNotNull(new TermResource($this->term)),
            'region' => $this->whenNotNull(new RegionResource($this->region)),
            'batch' => $this->batch,
            'nim' => $this->nim,
            'name' => $this->name,
            'major' => $this->whenNotNull(new MajorResource($this->major)),
            'gender' => $this->gender,
            'birth_place' => $this->birth_place,
            'birth_date' => date('Y-m-d', strtotime($this->birth_date)),
            'address' => $this->address,
            'ticket_number' => $this->ticket_number,
            'email_verified_at' => $this->email_verified_at,
            'campus_location' => $this->campus_location,
            'domicile' => $this->domicile,
            'line_id' => $this->line_id,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'toefl_detail' => $this->toeflDetail == null ? null : new ToeflDetailResource($this->toeflDetail),
            'member_payment' => $this->memberPayment->id == null ? null : new MemberPaymentResource($this->memberPayment),
            'welcoming_party' => $this->welcomingParty->id == null ? null : new WelcomingPartyResource($this->welcomingParty),
            'bom_recruitment' => $this->bomRecruitment->id == null ? null : new BomRecruitmentResource($this->bomRecruitment),
            'talent_recruitment' => $this->talentRecruitment->id == null ? null : new TalentRecruitmentResource($this->talentRecruitment),
        ];
    }
}
