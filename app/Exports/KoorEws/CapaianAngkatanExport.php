<?php

namespace App\Exports\KoorEws;

use App\Services\KoorEws\CapaianMhsService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class CapaianAngkatanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $service;

    public function __construct(CapaianMhsService $service)
    {
        $this->service = $service;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = $this->service->getAllAngkatanStats();
        return new Collection($data);
    }

    public function headings(): array
    {
        return [
            'Angkatan',
            'Tren IPS (Dibanding sebelumnya)',
            'Jumlah Mahasiswa Gagal Mata Kuliah (Nilai E)',
        ];
    }

    public function map($row): array
    {
        // Capitalize trend for better readability
        $trend = ucfirst($row['tren_ips']);
        
        return [
            $row['angkatan'],
            $trend,
            $row['mk_gagal'],
        ];
    }
}
