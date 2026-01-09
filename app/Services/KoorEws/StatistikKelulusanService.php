<?php

namespace App\Services\KoorEws;

use App\Models\Mahasiswa;

class StatistikKelulusanService
{
    public function getStatistikKelulusan()
    {
        // Mahasiswa Eligible (IPK >= 2, SKS >= 144, tidak ada nilai D/E)
        $eligible = Mahasiswa::join('akademik_mahasiswa', 'mahasiswa.id', '=', 'akademik_mahasiswa.mahasiswa_id')
            ->leftJoin('khs_krs_mahasiswa', 'mahasiswa.id', '=', 'khs_krs_mahasiswa.mahasiswa_id')
            ->where('akademik_mahasiswa.ipk', '>=', 2)
            ->where('akademik_mahasiswa.total_sks', '>=', 144)
            ->whereNotIn('khs_krs_mahasiswa.nilai', ['D', 'E'])
            ->orWhereNull('khs_krs_mahasiswa.nilai')
            ->distinct('mahasiswa.id')
            ->count('mahasiswa.id');

        // Mahasiswa Tidak Eligible
        $tidakEligible = Mahasiswa::join('akademik_mahasiswa', 'mahasiswa.id', '=', 'akademik_mahasiswa.mahasiswa_id')
            ->leftJoin('khs_krs_mahasiswa', 'mahasiswa.id', '=', 'khs_krs_mahasiswa.mahasiswa_id')
            ->where(function($query) {
                $query->where('akademik_mahasiswa.ipk', '<', 2)
                    ->orWhere('akademik_mahasiswa.total_sks', '<', 144)
                    ->orWhereIn('khs_krs_mahasiswa.nilai', ['D', 'E']);
            })
            ->distinct('mahasiswa.id')
            ->count('mahasiswa.id');

        // Status Mahasiswa
        $aktif = Mahasiswa::where('status_mahasiswa', 'aktif')->count();
        $mangkir = Mahasiswa::where('status_mahasiswa', 'mangkir')->count();
        $cuti = Mahasiswa::where('status_mahasiswa', 'cuti')->count();

        return [
            'eligible' => $eligible,
            'tidak_eligible' => $tidakEligible,
            'aktif' => $aktif,
            'mangkir' => $mangkir,
            'cuti' => $cuti
        ];
    }
}
