<?php

namespace App\Http\Resources\KoorEws;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CapaianAngkatanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'angkatan' => $this['angkatan'],
            'tren_ips' => $this['tren_ips'],
            'mk_gagal' => $this['mk_gagal'],
        ];
    }
}
