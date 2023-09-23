<?php

namespace App\Http\Resources\regions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionResource extends JsonResource
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
            'region_init' => $this->region_init,
            'region' => $this->region,
            'current_batch' => $this->current_batch,
            'toefl_payment_detail' => $this->toefl_payment_detail,
            'member_payment_detail' => $this->member_payment_detail,
            'toefl_price_one' => $this->toefl_price_one,
            'toefl_price_two' => $this->toefl_price_two,
            'toefl_price_three' => $this->toefl_price_three,
            'member_price' => $this->member_price,
            'current_term_id' => $this->current_term_id,
            'link_line_group' => $this->link_line_group,
            'qr_line_group' => $this->qr_line_group,
            'status' => $this->status,
        ];
    }
}
