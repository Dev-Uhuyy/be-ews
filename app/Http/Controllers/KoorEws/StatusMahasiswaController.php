<?php

namespace App\Http\Controllers\KoorEws;

use App\Http\Controllers\Controller;
use App\Services\KoorEws\StatusMahasiswaService;
use Illuminate\Http\Request;

class StatusMahasiswaController extends Controller
{
    protected $StatusMahasiswaService;

    public function __construct(StatusMahasiswaService $StatusMahasiswaService)
    {
        $this->StatusMahasiswaService = $StatusMahasiswaService;
    }

    public function getAllStatusMahasiswaEws()
    {
        try {
            $data = $this->StatusMahasiswaService->getAllStatusMahasiswaEws();
            return $this->successResponse($data, 'Berhasil mendapatkan status mahasiswa');
        } catch (\Exception $e) {
            return $this->exceptionError($e, 'Gagal mendapatkan status mahasiswa di EWS');
        }
    }

    public function getStatusMahasiswaEwsByAngkatan($angkatan)
    {
        try {
            $data = $this->StatusMahasiswaService->getStatusMahasiswaEwsByAngkatan($angkatan);
            return $this->successResponse($data, 'Berhasil mendapatkan status mahasiswa berdasarkan angkatan');
        } catch (\Exception $e) {
            return $this->exceptionError($e, 'Gagal mendapatkan status mahasiswa berdasarkan angkatan di EWS');
        }
    }

    public function getTableRingkasanStatusMahasiswa(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $result = $this->StatusMahasiswaService->getTableRingkasanStatusMahasiswa($perPage, $page);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendapatkan tabel ringkasan status mahasiswa',
                'data' => $result['data'],
                'pagination' => $this->paginate($result['total'], $perPage, $page, $result['data'])
            ]);
        } catch (\Exception $e) {
            return $this->exceptionError($e, 'Gagal mendapatkan tabel ringkasan status mahasiswa di status mahasiswa EWS');
        }
    }

    public function getTableRingkasanStatusMahasiswaByAngkatan(Request $request, $angkatan)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $result = $this->StatusMahasiswaService->getTableRingkasanStatusMahasiswaByAngkatan($angkatan, $perPage, $page);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendapatkan tabel ringkasan status mahasiswa berdasarkan angkatan',
                'data' => $result['data'],
                'pagination' => $this->paginate($result['total'], $perPage, $page, $result['data'])
            ]);
        } catch (\Exception $e) {
            return $this->exceptionError($e, 'Gagal mendapatkan tabel ringkasan status mahasiswa berdasarkan angkatan di status mahasiswa EWS');
        }
    }

    public function getMahasiswaBerisiko(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            $angkatan = $request->get('angkatan');
            $semester = $request->get('semester');

            $result = $this->StatusMahasiswaService->getMahasiswaBerisiko($perPage, $page, $angkatan, $semester);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendapatkan data mahasiswa berisiko',
                'data' => $result['data'],
                'pagination' => $this->paginate($result['total'], $perPage, $page, $result['data'])
            ]);
        } catch (\Exception $e) {
            return $this->exceptionError($e, 'Gagal mendapatkan data mahasiswa berisiko di status mahasiswa EWS');
        }
    }
}
