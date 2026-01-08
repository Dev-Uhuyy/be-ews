<?php

namespace App\Http\Controllers\MahasiswaEws;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function statusMahasiswa(Request $request)
    {
        $user = $request->user();

        // Cari data mahasiswa berdasarkan user yang login
        $mahasiswa = \App\Models\Mahasiswa::where('user_id', $user->id)->first();

        if (!$mahasiswa) {
            return response()->json([
                'message' => 'Data mahasiswa not found for this user',
            ], 404);
        }

        // Ambil data akademik mahasiswa
        $akademik = $mahasiswa->akademikmahasiswa;

        // Ambil status dari early warning system
        // Mengambil status terbaru jika ada banyak, atau null jika tidak ada
        $ewsStatus = null;
        if ($akademik) {
            $ewsEntry = $akademik->early_warning_systems()->latest()->first();
            $ewsStatus = $ewsEntry ? $ewsEntry->status : null;
        }

        // Ambil data IPS
        $ipsData = $mahasiswa->ipsmahasiswa;
        
        // Format IPS menjadi list untuk tabel
        $ipsList = [];
        if ($ipsData) {
            for ($i = 1; $i <= 14; $i++) {
                $key = "ips_$i";
                // Hanya masukkan jika nilainya tidak null (atau bisa disesuaikan kebutuhan)
                if (!is_null($ipsData->$key)) {
                    $ipsList[] = [
                        'semester' => $i,
                        'ips' => $ipsData->$key
                    ];
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'status_ews' => $ewsStatus,
                'akademik' => [
                    'ipk' => $akademik ? $akademik->ipk : null,
                    'sks_lulus' => $akademik ? $akademik->sks_lulus : null,
                    'semester_aktif' => $akademik ? $akademik->semester_aktif : null,
                    'dosen_wali' => $akademik && $akademik->dosen_wali ? (
                        ($akademik->dosen_wali->gelar_depan ? $akademik->dosen_wali->gelar_depan . ' ' : '') .
                        ($akademik->dosen_wali->user ? $akademik->dosen_wali->user->name : '') .
                        ($akademik->dosen_wali->gelar_belakang ? ', ' . $akademik->dosen_wali->gelar_belakang : '')
                    ) : null, 
                ],
                'ips_collection' => $ipsList
            ]
        ]);
    }
}
