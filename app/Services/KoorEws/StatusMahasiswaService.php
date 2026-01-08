<?php

namespace App\Services\KoorEws;

use App\Http\Resources\KoorEws\GetStatusEws;
use App\Http\Resources\KoorEws\GetTableRingkasanStatusMahasiswa;
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
            'pagination' => [
                'total' => $total,
                'count' => count($formattedData),
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
                'links' => [
                    'prev' => $page > 1 ? url()->current() . '?page=' . ($page - 1) . '&per_page=' . $perPage : null,
                    'next' => $page * $perPage < $total ? url()->current() . '?page=' . ($page + 1) . '&per_page=' . $perPage : null,
                ]
            ]
        ];
    }

    public function getMahasiswaBerisko($perPage = 10, $page = 1)
    {
        $query = Mahasiswa::select(
        'mahasiswa.id',
        'users.name as nama',
        'mahasiswa.nim',
        'akademik_mahasiswa.tahun_masuk as angkatan',
        'akademik_mahasiswa.ipk',
        'early_warning_system.status'
    )
    ->join('users', 'mahasiswa.user_id', '=', 'users.id')
    ->join('akademik_mahasiswa', 'mahasiswa.id', '=', 'akademik_mahasiswa.mahasiswa_id')
    ->join('early_warning_system', 'akademik_mahasiswa.id', '=', 'early_warning_system.akademik_mahasiswa_id')
    ->orderBy('early_warning_system.status', 'desc');

        $total = $query->count();
        $data = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $formattedData = GetMahasiswaBerisiko::collection($data)->toArray(request());

        return [
            'data' => $formattedData,
            'pagination' => [
                'total' => $total,
                'count' => count($formattedData),
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
                'links' => [
                    'prev' => $page > 1 ? url()->current() . '?page=' . ($page - 1) . '&per_page=' . $perPage : null,
                    'next' => $page * $perPage < $total ? url()->current() . '?page=' . ($page + 1) . '&per_page=' . $perPage : null,
                ]
            ]
        ];
    }

    public function exportTableRingkasanStatusMahasiswa()
    {
        $data = Mahasiswa::select('akademik_mahasiswa.tahun_masuk as angkatan')
            ->selectRaw('COUNT(mahasiswa.id) as total_mahasiswa')
            ->selectRaw('SUM(CASE WHEN akademik_mahasiswa.ipk < 2 THEN 1 ELSE 0 END) as ipk_kurang_2')
            ->selectRaw('SUM(CASE WHEN mahasiswa.status_mahasiswa = "mangkir" THEN 1 ELSE 0 END) as mangkir')
            ->selectRaw('SUM(CASE WHEN mahasiswa.status_mahasiswa = "cuti" THEN 1 ELSE 0 END) as cuti_2x')
            ->selectRaw('SUM(CASE WHEN mahasiswa.status_mahasiswa = "aktif" AND akademik_mahasiswa.ipk >= 2 THEN 1 ELSE 0 END) as normal')
            ->join('akademik_mahasiswa', 'mahasiswa.id', '=', 'akademik_mahasiswa.mahasiswa_id')
            ->groupBy('akademik_mahasiswa.tahun_masuk')
            ->orderBy('akademik_mahasiswa.tahun_masuk', 'desc')
            ->get();

        return GetTableRingkasanStatusMahasiswa::collection($data)->toArray(request());
    }
}
