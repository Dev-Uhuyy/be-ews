<?php

namespace App\Http\Resources\KoorEws;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IpkEligibleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'rata_rata_ipk' => $this['rata_rata_ipk'],
            'total_eligible' => $this['total_eligible'],
            'total_not_eligible' => $this['total_not_eligible'],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'status' => 'success',
                'message' => 'Data statistic IPK dan Eligibility berhasil diambil',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }
}
