<?php

namespace App\Http\Resources\KoorEws;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailMahasiswaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'nama_mahasiswa' => $this->user->name ?? '-',
            'dosen_wali' => $this->akademikmahasiswa->dosen_wali->user->name ?? '-',
        ];
    }
}
