<?php

namespace App\Http\Resources\KoorEws;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GetStatusEws extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statusCounts = $this->collection->pluck('jumlah', 'status');

        return [
            'tepat_waktu' => $statusCounts['tepat_waktu'] ?? 0,
            'normal' => $statusCounts['normal'] ?? 0,
            'perhatian' => $statusCounts['perhatian'] ?? 0,
            'kritis' => $statusCounts['kritis'] ?? 0,
            'total' => $this->collection->sum('jumlah')
        ];
    }
}
