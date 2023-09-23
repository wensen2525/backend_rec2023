<?php

namespace App\Http\Resources\toeflShifts;

use Illuminate\Http\Request;
use App\Http\Resources\terms\TermResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ToeflShiftResource extends JsonResource
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
            'term' => $this->whenNotNull(new TermResource($this->term)),
            'shift' => $this->shift,
            'quota' => $this->quota,
            'line_group' => $this->line_group,
            'link' => $this->link,
            'is_active' => $this->is_active,
        ];
    }
}
