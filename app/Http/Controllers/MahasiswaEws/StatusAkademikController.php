<?php

namespace App\Http\Controllers\MahasiswaEws;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\MahasiswaEws\StatusAkademikService;

class StatusAkademikController extends Controller
{
    protected $statusAkademikService;

    public function __construct(StatusAkademikService $statusAkademikService)
    {
        $this->statusAkademikService = $statusAkademikService;
    }

    public function getStatusAkademikMahasiswa()
    {
        $data = $this->statusAkademikService->getStatusAkademikMahasiswa();

        if (!$data) {
             return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Data mahasiswa tidak ditemukan',
                    'timestamp' => now()->toIso8601String(),
                ]
            ], 404);
        }

        return response()->json([
            'data' => $data,
            'meta' => [
                'status' => 'success',
                'message' => 'Status akademik mahasiswa berhasil diambil',
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
    public function getDaftarNilaiMahasiswa()
    {
        $data = $this->statusAkademikService->getDaftarNilaiMahasiswa();

        if (is_null($data)) {
             return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Data mahasiswa tidak ditemukan',
                    'timestamp' => now()->toIso8601String(),
                ]
            ], 404);
        }

        return response()->json([
            'data' => $data,
            'meta' => [
                'status' => 'success',
                'message' => 'Daftar nilai mahasiswa berhasil diambil',
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
    public function exportDaftarNilai()
    {
        $data = $this->statusAkademikService->getDaftarNilaiMahasiswa();

        if (is_null($data)) {
             return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Data mahasiswa tidak ditemukan',
                    'timestamp' => now()->toIso8601String(),
                ]
            ], 404);
        }

        $fileName = 'daftar_nilai_mahasiswa_' . now()->timestamp . '.csv';
        $path = 'exports/' . $fileName;

        \Maatwebsite\Excel\Facades\Excel::store(new \App\Exports\MahasiswaEws\DaftarNilaiExport($data), $path, 'public', \Maatwebsite\Excel\Excel::CSV);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'File export daftar nilai berhasil digenerate',
                'timestamp' => now()->toIso8601String(),
            ],
            'data' => [
                'url' => asset('storage/' . $path)
            ]
        ]);
    }
}
