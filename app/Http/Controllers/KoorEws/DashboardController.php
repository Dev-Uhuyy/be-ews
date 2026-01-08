<?php

namespace App\Http\Controllers\KoorEws;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\KoorEws\DashboardService;
use App\Http\Resources\KoorEws\KategoriMahasiswaResource;

use App\Http\Resources\KoorEws\IpkEligibleResource;
use App\Http\Resources\KoorEws\SummaryAllResource;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KoorEws\SummaryAngkatanExport;
use App\Http\Resources\KoorEws\DetailMahasiswaResource;
use App\Exports\KoorEws\DetailMahasiswaExport;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function kategoriAll()
    {
        $data = $this->dashboardService->getKategoriMahasiswaCounts();

        return new KategoriMahasiswaResource($data);
    }

    public function ipkEligible()
    {
        $data = $this->dashboardService->getIpkAndEligibleStats();

        return new IpkEligibleResource($data);
    }

    public function summaryAll()
    {
        $data = $this->dashboardService->getSummaryAngkatanStats();

        // Return collection resource since it is a list of items
        return SummaryAllResource::collection($data)->additional([
            'meta' => [
                'status' => 'success',
                'message' => 'Data summary angkatan berhasil diambil',
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function exportSummaryAll()
    {
        $fileName = 'summary_angkatan_' . now()->timestamp . '.xlsx';
        $path = 'exports/' . $fileName;

        Excel::store(new SummaryAngkatanExport($this->dashboardService), $path, 'public');

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'File berhasil digenerate',
                'timestamp' => now()->toIso8601String(),
            ],
            'data' => [
                'url' => asset('storage/' . $path)
            ]
        ]);
    }

    public function detailMahasiswa(Request $request)
    {
        $category = $request->query('category');
        $angkatan = $request->query('angkatan');

        if (!$category || !$angkatan) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Category and Angkatan parameters are required',
                    'timestamp' => now()->toIso8601String(),
                ]
            ], 400);
        }

        $data = $this->dashboardService->getMahasiswaByCategoryAndAngkatan($category, $angkatan);

        return DetailMahasiswaResource::collection($data)->additional([
            'meta' => [
                'status' => 'success',
                'message' => 'Data detail mahasiswa berhasil diambil',
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function exportDetailMahasiswa(Request $request)
    {
        $category = $request->query('category');
        $angkatan = $request->query('angkatan');

        if (!$category || !$angkatan) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Category and Angkatan parameters are required',
                    'timestamp' => now()->toIso8601String(),
                ]
            ], 400);
        }

        $fileName = 'detail_mahasiswa_' . $category . '_' . $angkatan . '_' . now()->timestamp . '.xlsx';
        $path = 'exports/' . $fileName;

        Excel::store(new DetailMahasiswaExport($this->dashboardService, $category, $angkatan), $path, 'public');

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'File export detail mahasiswa berhasil digenerate',
                'timestamp' => now()->toIso8601String(),
            ],
            'data' => [
                'url' => asset('storage/' . $path)
            ]
        ]);
    }
}
