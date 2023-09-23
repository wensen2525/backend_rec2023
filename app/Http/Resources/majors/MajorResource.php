<?php

namespace App\Http\Resources\majors;

use App\Models\Region;
use Illuminate\Http\Request;
use App\Http\Resources\regions\RegionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MajorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // dd($this->region_id);
        return [
          'id' => $this->id,
          'region_id' => $this->region->id,
          'region' => [
            'region_id' => $this->region->id, 
            'region_init' => $this->region->region_init,
            'region' => $this->region->region,
          ],
          'faculty' => $this->faculty ,
          'major_name' => $this->major_name ,
        ];
    }
}
