<?php

namespace App\Exports\KoorEws;

use App\Services\KoorEws\CapaianMhsService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class MahasiswaGagalExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $service;
    protected $angkatan;

    public function __construct(CapaianMhsService $service, $angkatan)
    {
        $this->service = $service;
        $this->angkatan = $angkatan;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = $this->service->getDaftarMahasiswaGagalPerAngkatan($this->angkatan);
        return new Collection($data);
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NIM',
            'Jumlah Nilai E',
            'Status EWS',
        ];
    }

    public function map($row): array
    {
        return [
            $row['nama'],
            $row['nim'],
            $row['jumlah_nilai_e'],
            $row['status_ews'],
        ];
    }
}
