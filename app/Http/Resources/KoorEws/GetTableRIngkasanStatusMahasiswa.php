<?php

namespace App\Http\Resources\KoorEws;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetTableRIngkasanStatusMahasiswa extends JsonResource
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
            'total_mahasiswa' => $this->total_mahasiswa,
            'ipk_kurang_2' => $this->ipk_kurang_2,
            'mangkir' => $this->mangkir,
            'cuti_2x' => $this->cuti_2x,
            'normal' => $this->normal,
        ];
    }
}
