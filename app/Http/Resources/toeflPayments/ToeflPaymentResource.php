<?php

namespace App\Http\Resources\toeflPayments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\paymentProviders\PaymentProviderResource;

class ToeflPaymentResource extends JsonResource
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
            'payment_type' => $this->payment_type,
            'provider' => $this->whenNotNull(new PaymentProviderResource ($this->paymentProvider)),
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'payment_amount' => $this->payment_amount,
            'payment_proof' => $this->payment_proof,
            'receiver_id' => $this->receiver_id,
            'is_confirmed' => $this->is_confirmed,
            'details' => $this->details == null ? null : [
                'id' => $this->details[0]->id ?? null,
                'term_id' => $this->details[0]->term_id ?? null,
                'user_id' => $this->details[0]->user_id ?? null
            ],
            'details_2' => $this->details == null ? null : [
                'id' => $this->details[1]->id ?? null,
                'term_id' => $this->details[1]->term_id ?? null,
                'user_id' => $this->details[1]->user_id ?? null
            ],
            'details_3' => $this->details == null ? null : [
                'id' => $this->details[2]->id ?? null,
                'term_id' => $this->details[2]->term_id ?? null,
                'user_id' => $this->details[2]->user_id ?? null
            ],
            'created_at' => date('Y-m-d', strtotime($this->created_at)),
            'updated_at' => $this->whenNotNull(date('Y-m-d', strtotime($this->updated_at))),
            'deleted_at' => $this->deleted_at == null ? null : date('Y-m-d', strtotime($this->deleted_at)),
        ];
    }
}
