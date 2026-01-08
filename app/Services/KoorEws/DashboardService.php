<?php

namespace App\Services\KoorEws;

use App\Models\Mahasiswa;
use App\Models\AkademikMahasiswa;
use App\Models\EarlyWarningSystem;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getKategoriMahasiswaCounts()
    {
        return [
            'total_mahasiswa' => Mahasiswa::count(),
            'lulus' => Mahasiswa::where('status_mahasiswa', 'lulus')->count(),
            'aktif' => Mahasiswa::where('status_mahasiswa', 'aktif')->count(),
            'tidak_aktif' => Mahasiswa::where('status_mahasiswa', 'tidak_aktif')->count(),
            'mangkir' => Mahasiswa::where('status_mahasiswa', 'mangkir')->count(),
            'cuti' => Mahasiswa::where('status_mahasiswa', 'cuti')->count(),
            'do' => Mahasiswa::where('status_mahasiswa', 'DO')->count(),
        ];
    }

    public function getIpkAndEligibleStats()
    {
        $avgIpk = AkademikMahasiswa::avg('ipk');
        
        // Ensure we handle case where there are no records (avg returns null)
        $avgIpk = $avgIpk ? round($avgIpk, 2) : 0;

        // Calculate Average IPK per Angkatan
        $ipkPerAngkatan = AkademikMahasiswa::select('tahun_masuk', DB::raw('ROUND(AVG(ipk), 2) as rata_rata'))
            ->whereNotNull('tahun_masuk')
            ->groupBy('tahun_masuk')
            ->orderBy('tahun_masuk', 'desc')
            ->get();

        $eligibleCount = EarlyWarningSystem::where('status_kelulusan', 'eligible')->count();
        $nonEligibleCount = EarlyWarningSystem::where('status_kelulusan', 'noneligible')->count();

        return [
            'rata_rata_ipk' => $avgIpk,
            'rata_rata_ipk_per_angkatan' => $ipkPerAngkatan,
            'total_eligible' => $eligibleCount,
            'total_not_eligible' => $nonEligibleCount,
        ];
    }

    public function getSummaryAngkatanStats()
    {
        $data = DB::table('akademik_mahasiswa as am')
            ->join('mahasiswa as m', 'am.mahasiswa_id', '=', 'm.id')
            ->leftJoin('early_warning_system as ews', 'am.id', '=', 'ews.akademik_mahasiswa_id')
            ->select(
                'am.tahun_masuk as angkatan',
                DB::raw('COUNT(m.id) as jml_mhs'),
                DB::raw('SUM(CASE WHEN m.status_mahasiswa = "aktif" THEN 1 ELSE 0 END) as aktif'),
                DB::raw('SUM(CASE WHEN m.cuti_2 = "yes" THEN 1 ELSE 0 END) as cuti_2x'),
                DB::raw('ROUND(AVG(am.ipk), 2) as ipk_rata_rata'),
                DB::raw('SUM(CASE WHEN ews.status = "tepat_waktu" THEN 1 ELSE 0 END) as tepat_waktu'),
                DB::raw('SUM(CASE WHEN ews.status = "perhatian" THEN 1 ELSE 0 END) as perhatian'),
                DB::raw('SUM(CASE WHEN ews.status = "kritis" THEN 1 ELSE 0 END) as kritis')
            )
            ->whereNotNull('am.tahun_masuk')
            ->groupBy('am.tahun_masuk')
            ->orderBy('am.tahun_masuk', 'desc')
            ->get();

        return $data;
    }

    public function getMahasiswaByCategoryAndAngkatan($category, $angkatan)
    {
        $query = Mahasiswa::query()
            ->with(['user', 'akademikmahasiswa', 'akademikmahasiswa.early_warning_systems', 'akademikmahasiswa.dosen_wali.user'])
            ->join('akademik_mahasiswa as am', 'mahasiswa.id', '=', 'am.mahasiswa_id')
            ->leftJoin('early_warning_system as ews', 'am.id', '=', 'ews.akademik_mahasiswa_id')
            ->select('mahasiswa.*', 'ews.status as ews_status');

        if ($angkatan) {
            $query->where('am.tahun_masuk', $angkatan);
        }

        switch ($category) {
            case 'aktif':
            case 'cuti':
            case 'mangkir':
            case 'DO':
            case 'lulus':
            case 'tidak_aktif':
                // Handles "tidak_aktif" vs "tidak aktif" mismatch if any, assuming input matches db value or we normalize
                // For safety, let's assume input matches enum values.
                 $query->where('mahasiswa.status_mahasiswa', $category);
                break;

            case 'cuti_2x':
                $query->where('mahasiswa.cuti_2', 'yes');
                break;

            case 'tepat_waktu':
            case 'perhatian':
            case 'kritis':
            case 'normal':
                $query->where('ews.status', $category);
                break;
                
            default:
                // If category is "all" or unknown, maybe return all for that angkatan?
                // Or handle specific mappings like "tidak aktif" (space)
                 if ($category == 'tidak aktif') {
                     $query->where('mahasiswa.status_mahasiswa', 'tidak_aktif');
                 }
                break;
        }

        return $query->get();
    }
}
