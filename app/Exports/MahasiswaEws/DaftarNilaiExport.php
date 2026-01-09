<?php

namespace App\Exports\MahasiswaEws;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DaftarNilaiExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Nama Mata Kuliah',
            'Kode Mata Kuliah',
            'SKS',
            'Nilai Huruf',
            'Nilai Angka',
        ];
    }

    public function map($row): array
    {
        return [
            $row['nama_matkul'],
            $row['kode_matkul'],
            $row['sks'],
            $row['nilai_huruf'],
            $row['nilai_angka'],
        ];
    }
}
