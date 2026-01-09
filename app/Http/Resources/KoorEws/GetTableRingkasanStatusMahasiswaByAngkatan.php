<?php

namespace App\Http\Resources\KoorEws;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetTableRingkasanStatusMahasiswaByAngkatan extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'nama' => $this->nama,
            'nim' => $this->nim,
            'doswal' => $this->nama_doswal,
            'ipk' => $this->ipk,
            'status_ews' => $this->status_ews,
        ];
    }
}
