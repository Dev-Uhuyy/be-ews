<?php

namespace App\Http\Controllers\KoorEws;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\KoorEws\CapaianMhsService;
use App\Http\Resources\KoorEws\RataRataIpsResource;
use App\Http\Resources\KoorEws\Top10MatkulGagalResource;
use App\Http\Resources\KoorEws\CapaianAngkatanResource;
use App\Http\Resources\KoorEws\MahasiswaGagalResource;


class CapaianMhsController extends Controller
{
    protected $capaianMhsService;

    public function __construct(CapaianMhsService $capaianMhsService)
    {
        $this->capaianMhsService = $capaianMhsService;
    }

    public function getRataRataIps()
    {
        $data = $this->capaianMhsService->getRataRataIpsTerakhir();

        return new RataRataIpsResource($data);
    }

    public function getTop10MatkulGagal()
    {
        $data = $this->capaianMhsService->getTop10MatakuliahGagal();

        return Top10MatkulGagalResource::collection($data)->additional([
            'meta' => [
                'status' => 'success',
                'message' => 'Data 10 matakuliah dengan nilai E terbanyak berhasil diambil',
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function getAllAngkatan()
    {
        $data = $this->capaianMhsService->getAllAngkatanStats();

        return CapaianAngkatanResource::collection($data)->additional([
            'meta' => [
                'status' => 'success',
                'message' => 'Data capaian per angkatan berhasil diambil',
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function exportCapaianAngkatan()
    {
        $fileName = 'capaian_angkatan_' . now()->timestamp . '.xlsx';
        $path = 'exports/' . $fileName;

        \Maatwebsite\Excel\Facades\Excel::store(new \App\Exports\KoorEws\CapaianAngkatanExport($this->capaianMhsService), $path, 'public');

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'File export capaian angkatan berhasil digenerate',
                'timestamp' => now()->toIso8601String(),
            ],
            'data' => [
                'url' => asset('storage/' . $path)
            ]
        ]);
    }

    public function getDaftarGagalPerAngkatan(Request $request)
    {
        $angkatan = $request->query('angkatan');

        if (!$angkatan) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Parameter angkatan diperlukan',
                    'timestamp' => now()->toIso8601String(),
                ]
            ], 400);
        }

        $data = $this->capaianMhsService->getDaftarMahasiswaGagalPerAngkatan($angkatan);

        return MahasiswaGagalResource::collection($data)->additional([
            'meta' => [
                'status' => 'success',
                'message' => 'Data mahasiswa gagal per angkatan berhasil diambil',
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function exportDaftarGagalPerAngkatan(Request $request)
    {
        $angkatan = $request->query('angkatan');

        if (!$angkatan) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Parameter angkatan diperlukan',
                    'timestamp' => now()->toIso8601String(),
                ]
            ], 400);
        }

        $fileName = 'mahasiswa_gagal_angkatan_' . $angkatan . '_' . now()->timestamp . '.xlsx';
        $path = 'exports/' . $fileName;

        \Maatwebsite\Excel\Facades\Excel::store(new \App\Exports\KoorEws\MahasiswaGagalExport($this->capaianMhsService, $angkatan), $path, 'public');

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'File export mahasiswa gagal berhasil digenerate',
                'timestamp' => now()->toIso8601String(),
            ],
            'data' => [
                'url' => asset('storage/' . $path)
            ]
        ]);
    }
}
