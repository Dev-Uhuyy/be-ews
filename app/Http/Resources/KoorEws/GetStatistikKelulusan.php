<?php

namespace App\Http\Resources\KoorEws;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetStatistikKelulusan extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'eligible' => $this['eligible'],
            'tidak_eligible' => $this['tidak_eligible'],
            'aktif' => $this['aktif'],
            'mangkir' => $this['mangkir'],
            'cuti' => $this['cuti'],
        ];
    }
}
