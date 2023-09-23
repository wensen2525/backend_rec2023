<?php

namespace App\Http\Resources\toeflDetails;

use Illuminate\Http\Request;
use App\Http\Resources\terms\TermResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\toeflShifts\ToeflShiftResource;
use App\Http\Resources\toeflPayments\ToeflPaymentResource;

class ToeflDetailResource extends JsonResource
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
            'payment' => $this->whenNotNull(new ToeflPaymentResource($this->toeflPayment)),
            'user' =>[
                'id'=> $this->user->id,
                'nim'=> $this->user->nim,
                'name'=> $this->user->name,
                'campus_location'=> $this->user->campus_location,
                'role'=> $this->user->role,
            ],
            'shift' => $this->whenNotNull(new ToeflShiftResource($this->toeflShift)),
            'edit_status' => $this->edit_status,
            'edit_reason' => $this->edit_reason,
            'is_attend' => $this->is_attend,
            'score' => $this->score,
            'request_edit_shift' => $this->request_edit_shift_id == null ? null : [
                'id' => $this->whenNotNull($this->requestedShift->id),
                'shift' => $this->whenNotNull($this->requestedShift->shift),
            ],
            'created_at' => date('Y-m-d', strtotime($this->created_at)),
            'updated_at' => date('Y-m-d', strtotime($this->updated_at)),
            'deleted_at' => $this->deleted_at == null ? null : date('Y-m-d', strtotime($this->deleted_at)),
        ];
    }
}
