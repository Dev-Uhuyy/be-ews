<?php

namespace App\Http\Resources\KoorEws;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MahasiswaGagalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'nama' => $this['nama'],
            'nim' => $this['nim'],
            'jumlah_nilai_e' => $this['jumlah_nilai_e'],
            'status_ews' => $this['status_ews'],
        ];
    }
}
