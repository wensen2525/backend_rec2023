<?php

namespace App\Http\Resources\bomDivisions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BomDivisionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection
        ];
    }
}
