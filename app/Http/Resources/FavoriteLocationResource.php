<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteLocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'title'     => $this->title,
            'address'   => $this->address,
            'latitude'  => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'created_at'=> $this->created_at->toDateTimeString(),
            'updated_at'=> $this->updated_at->toDateTimeString(),
        ];
    }
}
