<?php

namespace App\Services\KoorEws;

use App\Http\Resources\KoorEws\GetStatusEws;
use App\Http\Resources\KoorEws\GetTableRingkasanStatusMahasiswa;
use App\Http\Resources\KoorEws\GetTableRingkasanStatusMahasiswaByAngkatan;
use App\Http\Resources\KoorEws\GetMahasiswaBerisiko;
use App\Models\EarlyWarningSystem;
use App\Models\Mahasiswa;

class StatusMahasiswaService
{
    public function getAllStatusMahasiswaEws()
    {
        $data = EarlyWarningSystem::selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->get();

        return new GetStatusEws($data);
    }

    public function getStatusMahasiswaEwsByAngkatan($angkatan)
    {
        $statusData = EarlyWarningSystem::selectRaw('status, COUNT(*) as jumlah')
            ->join('akademik_mahasiswa', 'early_warning_system.akademik_mahasiswa_id', '=', 'akademik_mahasiswa.id')
            ->where('akademik_mahasiswa.tahun_masuk', $angkatan)
            ->groupBy('status')
            ->get();

        // Get IPS data per semester
        $ipsData = Mahasiswa::select(
            'ips_mahasiswa.ips_1',
            'ips_mahasiswa.ips_2',
            'ips_mahasiswa.ips_3',
            'ips_mahasiswa.ips_4',
            'ips_mahasiswa.ips_5',
            'ips_mahasiswa.ips_6',
            'ips_mahasiswa.ips_7',
            'ips_mahasiswa.ips_8',
            'ips_mahasiswa.ips_9',
            'ips_mahasiswa.ips_10',
            'ips_mahasiswa.ips_11',
            'ips_mahasiswa.ips_12',
            'ips_mahasiswa.ips_13',
            'ips_mahasiswa.ips_14'
        )
            ->join('akademik_mahasiswa', 'mahasiswa.id', '=', 'akademik_mahasiswa.mahasiswa_id')
            ->leftJoin('ips_mahasiswa', 'mahasiswa.id', '=', 'ips_mahasiswa.mahasiswa_id')
            ->where('akademik_mahasiswa.tahun_masuk', $angkatan)
            ->get();

        // Calculate average IPS per semester
        $ipsAverage = [];
        for ($i = 1; $i <= 14; $i++) {
            $column = "ips_$i";
            $values = $ipsData->pluck($column)->filter()->values();
            $ipsAverage["semester_$i"] = $values->count() > 0 ? round($values->avg(), 2) : null;
        }

        return [
            'status_ews' => (new GetStatusEws($statusData))->toArray(request()),
            'grafik_ips' => $ipsAverage
        ];
    }

    public function getTableRingkasanStatusMahasiswa($perPage = 10, $page = 1)
    {
        $query = Mahasiswa::select('akademik_mahasiswa.tahun_masuk as angkatan')
            ->selectRaw('COUNT(mahasiswa.id) as total_mahasiswa')
            ->selectRaw('SUM(CASE WHEN akademik_mahasiswa.ipk < 2 THEN 1 ELSE 0 END) as ipk_kurang_2')
            ->selectRaw('SUM(CASE WHEN mahasiswa.status_mahasiswa = "mangkir" THEN 1 ELSE 0 END) as mangkir')
            ->selectRaw('SUM(CASE WHEN mahasiswa.status_mahasiswa = "cuti" THEN 1 ELSE 0 END) as cuti_2x')
            ->selectRaw('SUM(CASE WHEN mahasiswa.status_mahasiswa = "aktif" AND akademik_mahasiswa.ipk >= 2 THEN 1 ELSE 0 END) as normal')
            ->join('akademik_mahasiswa', 'mahasiswa.id', '=', 'akademik_mahasiswa.mahasiswa_id')
            ->groupBy('akademik_mahasiswa.tahun_masuk')
            ->orderBy('akademik_mahasiswa.tahun_masuk', 'desc');

        $total = $query->get()->count();
        $data = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $formattedData = GetTableRingkasanStatusMahasiswa::collection($data)->toArray(request());

        return [
            'data' => $formattedData,
            'total' => $total
        ];
    }

    public function getTableRingkasanStatusMahasiswaByAngkatan($angkatan, $perPage = 10, $page = 1)
    {
        $query = Mahasiswa::select(
            'users.name as nama',
            'mahasiswa.nim',
            'dosen_users.name as nama_doswal',
            'akademik_mahasiswa.ipk',
            'early_warning_system.status as status_ews'
        )
            ->join('users', 'mahasiswa.user_id', '=', 'users.id')
            ->join('akademik_mahasiswa', 'mahasiswa.id', '=', 'akademik_mahasiswa.mahasiswa_id')
            ->join('dosen', 'akademik_mahasiswa.dosen_wali_id', '=', 'dosen.id')
            ->join('users as dosen_users', 'dosen.user_id', '=', 'dosen_users.id')
            ->leftJoin('early_warning_system', 'akademik_mahasiswa.id', '=', 'early_warning_system.akademik_mahasiswa_id')
            ->where('akademik_mahasiswa.tahun_masuk', $angkatan)
            ->orderBy('akademik_mahasiswa.tahun_masuk', 'desc');

        $total = $query->count();
        $data = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $formattedData = GetTableRingkasanStatusMahasiswaByAngkatan::collection($data)->toArray(request());

        return [
            'data' => $formattedData,
            'total' => $total
        ];
    }

    public function getMahasiswaBerisiko($perPage = 10, $page = 1, $angkatan = null, $semester = null)
    {
        $query = Mahasiswa::select(
        'mahasiswa.id',
        'users.name as nama',
        'mahasiswa.nim',
        'akademik_mahasiswa.tahun_masuk as angkatan',
        'akademik_mahasiswa.semester_aktif',
        'akademik_mahasiswa.ipk',
        'early_warning_system.status'
    )
    ->join('users', 'mahasiswa.user_id', '=', 'users.id')
    ->join('akademik_mahasiswa', 'mahasiswa.id', '=', 'akademik_mahasiswa.mahasiswa_id')
    ->join('early_warning_system', 'akademik_mahasiswa.id', '=', 'early_warning_system.akademik_mahasiswa_id');

        if ($angkatan) {
            $query->where('akademik_mahasiswa.tahun_masuk', $angkatan);
        }

        if ($semester) {
            $query->whereIn('akademik_mahasiswa.semester_aktif', [1, 2, 3]);
        }

        $query->orderBy('early_warning_system.status', 'desc');

        $total = $query->count();
        $data = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $formattedData = GetMahasiswaBerisiko::collection($data)->toArray(request());

        return [
            'data' => $formattedData,
            'total' => $total
        ];
    }
}
