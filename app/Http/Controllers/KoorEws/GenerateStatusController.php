<?php

namespace App\Http\Controllers\KoorEws;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AkademikMahasiswa;
use App\Models\KhsKrsMahasiswa;
use App\Models\EarlyWarningSystem;
use Illuminate\Support\Facades\DB;

class GenerateStatusController extends Controller
{
    public function generate()
    {
        // Increase memory limit and execution time for bulk processing
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        try {
            DB::beginTransaction();

            // 1. Get all academic records with their student and KHS/KRS data
            // Eager load khskrsmahasiswa and its mata_kuliah relationship
            // Also load user to get the name
            $akademikData = AkademikMahasiswa::with([
                'mahasiswa', 
                'mahasiswa.user', 
                'mahasiswa.khskrsmahasiswa.mata_kuliah'
            ])->get();

            $updatedCount = 0;
            $details = [];

            foreach ($akademikData as $akademik) {
                // Ensure we have a student ID
                if (!$akademik->mahasiswa) continue;

                $mahasiswaId = $akademik->mahasiswa_id;
                
                // --- PREPARE LOGIC PARAMETERS ---

                // 1. SKS Lulus
                $sksLulus = (int) ($akademik->sks_lulus ?? 0);

                // 2. Semester Saat Ini
                $semesterSaatIni = (int) ($akademik->semester_aktif ?? 1);

                // 3. Status NFU (Nasional, Fakultas, Prodi/Umum)
                $isNfuDone = ($akademik->mk_nasional === 'yes' && 
                              $akademik->mk_fakultas === 'yes' && 
                              $akademik->mk_prodi === 'yes');
                
                $statusDoneNfuGanjil = $isNfuDone;
                $statusDoneNfuGenap = $isNfuDone;

                // 4. Hitung Jumlah Nilai E dan D
                $khsRecords = $akademik->mahasiswa->khskrsmahasiswa;
                
                $jumlahNilaiE = $khsRecords->where('nilai_akhir_huruf', 'E')->count();
                $jumlahNilaiD = $khsRecords->where('nilai_akhir_huruf', 'D')->count();

                // 5. Cek keberadaan E/D di Matkul Ganjil vs Genap
                $adaEdMatkulGanjil = $khsRecords->filter(function ($record) {
                    if (!in_array($record->nilai_akhir_huruf, ['E', 'D'])) return false;
                    if (!$record->mata_kuliah) return false;
                    return ($record->mata_kuliah->semester % 2) != 0;
                })->isNotEmpty();

                $adaEdMatkulGenap = $khsRecords->filter(function ($record) {
                    if (!in_array($record->nilai_akhir_huruf, ['E', 'D'])) return false;
                    if (!$record->mata_kuliah) return false;
                    return ($record->mata_kuliah->semester % 2) == 0;
                })->isNotEmpty();


                // --- EXECUTE LOGIC ---
                $logicResult = $this->cek_status_kelulusan(
                    $sksLulus,
                    $jumlahNilaiE,
                    $jumlahNilaiD,
                    $semesterSaatIni,
                    $statusDoneNfuGanjil,
                    $statusDoneNfuGenap,
                    $adaEdMatkulGanjil,
                    $adaEdMatkulGenap
                );
                
                $statusColor = $logicResult['status'];
                $reason = $logicResult['reason'];

                // --- MAP TO DATABASE ENUM ---
                $dbStatus = 'normal'; // Default
                if ($statusColor === 'MERAH') {
                    $dbStatus = 'kritis';
                } elseif ($statusColor === 'KUNING') {
                    $dbStatus = 'perhatian';
                } elseif ($statusColor === 'HIJAU') {
                    $dbStatus = 'normal';
                } elseif ($statusColor === 'BIRU') {
                    $dbStatus = 'tepat_waktu';
                } elseif ($statusColor === 'NORMAL') {
                    $dbStatus = 'normal';
                }

                // --- UPDATE DATABASE (Only Status) ---
                EarlyWarningSystem::updateOrCreate(
                    ['akademik_mahasiswa_id' => $akademik->id],
                    [
                        'status' => $dbStatus,
                    ]
                );

                $details[] = [
                    'nama' => $akademik->mahasiswa->user->name ?? 'Unknown',
                    'nim' => $akademik->mahasiswa->nim,
                    'semester' => $semesterSaatIni,
                    'sks_lulus' => $sksLulus,
                    'status_ews' => $dbStatus, // Converted for DB
                    'status_logic' => $statusColor, // Original Color
                    'reason' => $reason
                ];

                $updatedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Generate status EWS berhasil.',
                'processed_count' => $updatedCount,
                'data' => $details
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat generate status.',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * LOGIC IMPLEMENTATION (Ported from logic.py with Reasons)
     */
    private function hitung_sks_maks_bisa_diambil($semester_sekarang, $semester_target)
    {
        // Checked and matches logic.py: hitung_sks_maks_bisa_diambil
        if ($semester_sekarang > $semester_target) {
            return 0;
        }

        $total_sks_bisa_diambil = 0;
        for ($smt = $semester_sekarang; $smt <= $semester_target; $smt++) {
            if ($smt <= 10) {
                $total_sks_bisa_diambil += 20;
            } else {
                $total_sks_bisa_diambil += 24;
            }
        }
            
        return $total_sks_bisa_diambil;
    }

    private function cek_status_kelulusan(
        $sks_lulus,
        $jumlah_nilai_e,
        $jumlah_nilai_d,
        $semester_saat_ini,
        $status_done_nfu_ganjil,
        $status_done_nfu_genap,
        $ada_ED_matkul_ganjil,
        $ada_ED_matkul_genap
    ) {
        // Checked and matches logic.py: cek_status_kelulusan
        // 1. Hitung variabel turunan
        $sisa_sks = 144 - $sks_lulus;
        $is_genap = ($semester_saat_ini % 2 == 0);
        $is_ganjil = !$is_genap;

        // 2. Hitung batas SKS yang bisa diambil dari semester ini
        $sks_bisa_diambil_sd_14 = $this->hitung_sks_maks_bisa_diambil($semester_saat_ini, 14);
        $sks_bisa_diambil_sd_10 = $this->hitung_sks_maks_bisa_diambil($semester_saat_ini, 10);
        $sks_bisa_diambil_sd_8 = $this->hitung_sks_maks_bisa_diambil($semester_saat_ini, 8);

        // 3. Implementasi Logika with REASONS

        // --- LOGIKA MERAH (DROP OUT) ---
        if ($sisa_sks > $sks_bisa_diambil_sd_14) {
            return [
                'status' => 'MERAH', 
                'reason' => "Sisa SKS ($sisa_sks) melebihi batas yg bisa diambil s.d sem 14 ($sks_bisa_diambil_sd_14)"
            ];
        }

        if ($is_ganjil && $semester_saat_ini == 13) {
            if (!$status_done_nfu_ganjil || $ada_ED_matkul_ganjil) {
                return [
                    'status' => 'MERAH', 
                    'reason' => "Semester 13 (Ganjil): Masih ada NFU belum lulus atau ada matkul Ganjil nilai E/D"
                ];
            }
        } elseif ($is_genap && $semester_saat_ini == 14) {
            if (!$status_done_nfu_genap || $ada_ED_matkul_genap) {
                return [
                    'status' => 'MERAH',
                    'reason' => "Semester 14 (Genap): Masih ada NFU belum lulus atau ada matkul Genap nilai E/D"
                ];
            }
        }

        // --- LOGIKA KUNING (LULUS 7 TAHUN) ---
        if ($sisa_sks > $sks_bisa_diambil_sd_10) {
            return [
                'status' => 'KUNING',
                'reason' => "Sisa SKS ($sisa_sks) melebihi batas yg bisa diambil s.d sem 10 ($sks_bisa_diambil_sd_10)"
            ];
        }

        if ($is_ganjil && $semester_saat_ini == 9) {
            if (!$status_done_nfu_ganjil || $ada_ED_matkul_ganjil) {
                return [
                    'status' => 'KUNING',
                    'reason' => "Semester 9 (Ganjil): Masih ada NFU belum lulus atau ada matkul Ganjil nilai E/D"
                ];
            }
        } elseif ($is_genap && $semester_saat_ini == 10) {
            if (!$status_done_nfu_genap || $ada_ED_matkul_genap) {
                return [
                    'status' => 'KUNING',
                    'reason' => "Semester 10 (Genap): Masih ada NFU belum lulus atau ada matkul Genap nilai E/D"
                ];
            }
        }

        // --- LOGIKA HIJAU (LULUS 5 TAHUN) ---
        if ($sisa_sks > $sks_bisa_diambil_sd_8) {
            return [
                'status' => 'HIJAU',
                'reason' => "Sisa SKS ($sisa_sks) melebihi batas yg bisa diambil s.d sem 8 ($sks_bisa_diambil_sd_8)"
            ];
        }

        if ($is_ganjil && $semester_saat_ini == 7) {
            if (!$status_done_nfu_ganjil || $ada_ED_matkul_ganjil) {
                return [
                    'status' => 'HIJAU',
                    'reason' => "Semester 7 (Ganjil): Masih ada NFU belum lulus atau ada matkul Ganjil nilai E/D"
                ];
            }
        } elseif ($is_genap && $semester_saat_ini == 8) {
            if (!$status_done_nfu_genap || $ada_ED_matkul_genap) {
                return [
                    'status' => 'HIJAU',
                    'reason' => "Semester 8 (Genap): Masih ada NFU belum lulus atau ada matkul Genap nilai E/D"
                ];
            }
        }

        // --- LOGIKA BIRU (LULUS 4 TAHUN) ---
        $kondisi_sks_biru = ($sisa_sks <= $sks_bisa_diambil_sd_8);
        
        // Cek syarat biru
        if (($is_ganjil && $semester_saat_ini == 7) || ($is_genap && $semester_saat_ini == 8)) {
            if ($kondisi_sks_biru && ($jumlah_nilai_e <= 0) && ($jumlah_nilai_d <= 1) && $status_done_nfu_ganjil) {
                return [
                    'status' => 'BIRU',
                    'reason' => "Memenuhi syarat lulus tepat waktu (SKS cukup, E=0, D<=1, NFU Done)"
                ];
            }
        }

        // --- DEFAULT ---
        return [
            'status' => 'NORMAL',
            'reason' => "Tidak memenuhi kondisi kritis (Merah/Kuning/Hijau) dan belum/tidak masuk kriteria Biru"
        ];
    }
}
