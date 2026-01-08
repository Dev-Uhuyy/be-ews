<?php

namespace App\Http\Resources\KoorEws;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Top10MatkulGagalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'matakuliah' => $this['matakuliah'],
            'kode_matakuliah' => $this['kode_matakuliah'],
            'total_gagal' => $this['total_gagal'],
        ];
    }
}
