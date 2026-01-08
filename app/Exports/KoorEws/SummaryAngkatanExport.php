<?php

namespace App\Exports\KoorEws;

use App\Services\KoorEws\DashboardService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SummaryAngkatanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->dashboardService->getSummaryAngkatanStats();
    }

    public function headings(): array
    {
        return [
            'Angkatan',
            'Jmlh Mhs',
            'Aktif',
            'Cuti2x',
            'IPK Rata Rata',
            'Tepat Waktu',
            'Perhatian',
            'Kritis'
        ];
    }

    public function map($row): array
    {
        return [
            $row->angkatan,
            $row->jml_mhs,
            $row->aktif,
            $row->cuti_2x,
            $row->ipk_rata_rata,
            $row->tepat_waktu,
            $row->perhatian,
            $row->kritis,
        ];
    }
}
