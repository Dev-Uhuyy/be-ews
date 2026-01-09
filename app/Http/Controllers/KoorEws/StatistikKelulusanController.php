<?php

namespace App\Http\Controllers\KoorEws;

use App\Http\Controllers\Controller;
use App\Services\KoorEws\StatistikKelulusanService;
use Illuminate\Http\Request;

class StatistikKelulusanController extends Controller
{
    protected $StatistikKelulusanService;

    public function __construct(StatistikKelulusanService $StatistikKelulusanService)
    {
        $this->StatistikKelulusanService = $StatistikKelulusanService;
    }

    public function getStatistikKelulusan(Request $request)
    {
        try {
            $data = $this->StatistikKelulusanService->getStatistikKelulusan();

            return $this->successResponse($data, 'Berhasil mendapatkan statistik kelulusan');
        } catch (\Exception $e) {
            return $this->exceptionError($e, 'Gagal mendapatkan statistik kelulusan');
        }
    }
}
