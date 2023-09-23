<?php

namespace App\Http\Resources\toeflAttendances;

use Illuminate\Http\Request;
use App\Http\Resources\regions\RegionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\toeflShifts\ToeflShiftResource;
use App\Http\Resources\toeflDetails\ToeflDetailResource;

class ToeflAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'toefl_detail' => [
                'id' => $this->id,
                'is_attend' => $this->is_attend,
            ],
            'region' => $this->whenNotNull(new RegionResource($this->user->region)),
            'toefl_shift' => $this->whenNotNull(new ToeflShiftResource($this->toeflShift)),
            'batch' => $this->user->batch,
            'user' => [
                'id' => $this->user->id,
                'nim' => $this->user->nim,
                'name' => $this->user->name,
            ],
        ];
    }
}
