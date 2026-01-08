<?php

namespace App\Http\Resources\KoorEws;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KategoriMahasiswaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_mahasiswa' => $this['total_mahasiswa'],
            'lulus' => $this['lulus'],
            'aktif' => $this['aktif'],
            'tidak_aktif' => $this['tidak_aktif'],
            'mangkir' => $this['mangkir'],
            'cuti' => $this['cuti'],
            'do' => $this['do'],
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
                'message' => 'Data Kategori Mahasiswa berhasil diambil',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }
}
