<?php

namespace App\Http\Resources\KoorEws;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SummaryAllResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'angkatan' => $this->angkatan,
            'jml_mhs' => $this->jml_mhs,
            'aktif' => $this->aktif,
            'cuti_2x' => $this->cuti_2x,
            'ipk_rata_rata' => $this->ipk_rata_rata,
            'tepat_waktu' => $this->tepat_waktu,
            'perhatian' => $this->perhatian,
            'kritis' => $this->kritis,
        ];
    }
}
