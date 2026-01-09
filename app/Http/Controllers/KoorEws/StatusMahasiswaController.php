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

    public function getTableRingkasanStatusMahasiswa(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $data = $this->StatusMahasiswaService->getTableRingkasanStatusMahasiswa($perPage, $page);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendapatkan tabel ringkasan status mahasiswa',
                'data' => $data['data'],
                'pagination' => $data['pagination']
            ]);
        } catch (\Exception $e) {
            return $this->exceptionError($e, 'Gagal mendapatkan tabel ringkasan status mahasiswa di status mahasiswa EWS');
        }
    }

    public function getMahasiswaBerisko(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $data = $this->StatusMahasiswaService->getMahasiswaBerisko($perPage, $page);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendapatkan data mahasiswa berisiko',
                'data' => $data['data'],
                'pagination' => $data['pagination']
            ]);
        } catch (\Exception $e) {
            return $this->exceptionError($e, 'Gagal mendapatkan data mahasiswa berisiko di status mahasiswa EWS');
        }
    }
}
