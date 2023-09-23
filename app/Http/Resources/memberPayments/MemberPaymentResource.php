<?php

namespace App\Http\Resources\memberPayments;

use Illuminate\Http\Request;
use App\Http\Resources\terms\TermResource;
use App\Http\Resources\users\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\paymentProviders\PaymentProviderResource;

class MemberPaymentResource extends JsonResource
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
            'term' => $this->whenNotNull(new TermResource ($this->term)),
            'user' => $this->user == null ? null : [
                'id' => $this->user->id
            ],
            'payment_type' => $this->payment_type,
            'provider' => $this->whenNotNull(new PaymentProviderResource($this->paymentProvider )),
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'payment_amount' => $this->payment_amount,
            'payment_proof' => $this->payment_proof,
            'is_confirmed' => $this->is_confirmed,
            'created_at' => date('d-m-Y', strtotime($this->created_at)),
            'updated_at' => $this->updated_at == null ? null : date('d-m-Y', strtotime($this->updated_at)),
            'deleted_at' => $this->deleted_at == null ? null : date('d-m-Y', strtotime($this->deleted_at)),
        ];
    }
}
