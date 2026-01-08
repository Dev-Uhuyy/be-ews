<?php

namespace App\Exports\KoorEws;

use App\Services\KoorEws\DashboardService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DetailMahasiswaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $dashboardService;
    protected $category;
    protected $angkatan;

    public function __construct(DashboardService $dashboardService, $category, $angkatan)
    {
        $this->dashboardService = $dashboardService;
        $this->category = $category;
        $this->angkatan = $angkatan;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->dashboardService->getMahasiswaByCategoryAndAngkatan($this->category, $this->angkatan);
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama Mahasiswa',
            'Angkatan',
            'Status Mahasiswa',
            'Dosen Wali',
            'Status EWS'
        ];
    }

    public function map($row): array
    {
        return [
            $row->nim,
            $row->user->name ?? '-',
            $row->akademikmahasiswa->tahun_masuk ?? '-',
            $row->status_mahasiswa,
            // $row->akademikmahasiswa->ipk ?? 0,
            // $row->akademikmahasiswa->sks_lulus ?? 0,
            $row->akademikmahasiswa->dosen_wali->user->name ?? '-',
            $row->ews_status ?? '-'
        ];
    }
}
