<?php

namespace App\Http\Resources\KoorEws;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RataRataIpsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'rata_rata_ips_terakhir' => $this['rata_rata_ips_terakhir'],
            'total_mahasiswa_naik' => $this['total_mahasiswa_naik'],
            'total_mahasiswa_turun' => $this['total_mahasiswa_turun'],
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
                'message' => 'Data rata-rata IPS terakhir berhasil diambil',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }
}
