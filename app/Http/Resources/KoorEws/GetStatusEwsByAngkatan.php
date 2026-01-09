<?php

namespace App\Http\Resources\KoorEws;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetStatusEwsByAngkatan extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status_ews' => $this->resource['status_ews'],
            'grafik_ips' => $this->resource['grafik_ips']
        ];
    }
}
