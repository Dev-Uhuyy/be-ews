<?php

namespace App\Services\MahasiswaEws;

use App\Models\User;
use App\Models\AkademikMahasiswa;
use App\Models\EarlyWarningSystem;
use Illuminate\Support\Facades\Auth;

class StatusAkademikService
{
    public function getStatusAkademikMahasiswa()
    {
        // 1. Get Logged in User
        $user = Auth::user();
        if (!$user->mahasiswa) {
            return null;
        }
        $mahasiswa = $user->mahasiswa;

        // 2. Get Akademik Data
        $akademik = AkademikMahasiswa::where('mahasiswa_id', $mahasiswa->id)
            ->with(['dosen_wali.user'])
            ->first();

        // 3. Get Prodi via ProdiUser
        $prodiUser = \App\Models\ProdiUser::where('user_id', $user->id)->with('prodi')->first();
        $namaProdi = $prodiUser && $prodiUser->prodi ? $prodiUser->prodi->nama : '-';

        // 4. Get EWS Status
        $ews = null;
        if ($akademik) {
            $ews = EarlyWarningSystem::where('akademik_mahasiswa_id', $akademik->id)
                ->latest()
                ->first();
        }
        
        // 5. Get IPS Data
        $ipsRecord = $mahasiswa->ipsmahasiswa;
        $ipsData = [];
        if ($ipsRecord) {
            for ($i = 1; $i <= 14; $i++) {
                $col = 'ips_' . $i;
                $ipsData['semester_' . $i] = $ipsRecord->$col;
            }
        }

        // 6. Get Failed Courses (E and D)
        $failedCourses = $mahasiswa->khskrsmahasiswa()
            ->whereIn('nilai_akhir_huruf', ['E', 'D'])
            ->with('mata_kuliah')
            ->get()
            ->map(function ($record) {
                return [
                    'nama_matkul' => $record->mata_kuliah->name,
                    'kode_matkul' => $record->mata_kuliah->kode,
                    'sks' => $record->mata_kuliah->sks,
                    'nilai_huruf' => $record->nilai_akhir_huruf
                ];
            });

        return [
            'nama_mahasiswa' => $user->name,
            'nim' => $mahasiswa->nim,
            'prodi' => $namaProdi,
            'dosen_wali' => ($akademik && $akademik->dosen_wali && $akademik->dosen_wali->user) ? $akademik->dosen_wali->user->name : '-',
            'status_ews' => $ews ? $ews->status : 'Aman',
            'data_ips' => $ipsData,
            'matkul_gagal' => $failedCourses
        ];
    }
    public function getDaftarNilaiMahasiswa()
    {
        $user = Auth::user();
        if (!$user->mahasiswa) {
            return null;
        }
        $mahasiswa = $user->mahasiswa;

        // Fetch all grades
        $grades = $mahasiswa->khskrsmahasiswa()
            ->with(['mata_kuliah'])
            ->get()
            ->map(function ($record) {
                return [
                    'nama_matkul' => $record->mata_kuliah->name,
                    'kode_matkul' => $record->mata_kuliah->kode,
                    'sks' => $record->mata_kuliah->sks,
                    'nilai_huruf' => $record->nilai_akhir_huruf,
                    'nilai_angka' => $record->nilai_akhir_angka
                ];
            });

        return $grades;
    }
}
