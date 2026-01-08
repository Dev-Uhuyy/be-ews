<?php

namespace App\Services\KoorEws;

use App\Models\IpsMahasiswa;
use App\Models\KhsKrsMahasiswa;
use App\Models\AkademikMahasiswa;
use Illuminate\Support\Facades\DB;

class CapaianMhsService
{
    public function getRataRataIpsTerakhir()
    {
        // Optimizing by selecting only necessary columns might be good if table is huge, 
        // but select * is fine for now as we need all ips columns.
        $ipsRecords = IpsMahasiswa::all();
        
        $totalIps = 0;
        $count = 0;
        $naik = 0;
        $turun = 0;

        foreach ($ipsRecords as $record) {
            // Find the last non-null IPS starting from 14 down to 1
            $lastIps = null;
            $lastIndex = 0;

            for ($i = 14; $i >= 1; $i--) {
                $column = 'ips_' . $i;
                if (!is_null($record->$column)) {
                    $lastIps = $record->$column;
                    $lastIndex = $i;
                    break;
                }
            }

            if (!is_null($lastIps)) {
                $totalIps += $lastIps;
                $count++;

                // Determine previous IPS value
                $prevIps = 0;
                if ($lastIndex > 1) {
                    // Check immediate previous semester column
                    $prevColumn = 'ips_' . ($lastIndex - 1);
                    // Use the value if it exists, otherwise default to 0 
                    // (Matches the logic: if ips_1 vs 0, then ips_X vs null/0)
                    $prevIps = $record->$prevColumn ?? 0;
                }

                if ($lastIps > $prevIps) {
                    $naik++;
                } elseif ($lastIps < $prevIps) {
                    $turun++;
                }
            }
        }

        $avgIps = $count > 0 ? round($totalIps / $count, 2) : 0;

        return [
            'rata_rata_ips_terakhir' => $avgIps,
            'total_mahasiswa_naik' => $naik,
            'total_mahasiswa_turun' => $turun,
        ];
    }

    public function getTop10MatakuliahGagal()
    {
        return KhsKrsMahasiswa::select('matakuliah_id', DB::raw('count(*) as total_gagal'))
            ->where('nilai_akhir_huruf', 'E')
            ->with('mata_kuliah')
            ->groupBy('matakuliah_id')
            ->orderBy('total_gagal', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'matakuliah' => $item->mata_kuliah->name,
                    'kode_matakuliah' => $item->mata_kuliah->kode,
                    'total_gagal' => $item->total_gagal,
                ];
            });
    }

    public function getAllAngkatanStats()
    {
        // Get all students grouped by angkatan (tahun_masuk)
        $students = AkademikMahasiswa::with('mahasiswa.ipsmahasiswa')
            ->get()
            ->groupBy('tahun_masuk');
        
        $results = [];

        // Get failed counts per angkatan
        $failedCounts = DB::table('khs_krs_mahasiswa as kkm')
            ->join('akademik_mahasiswa as am', 'kkm.mahasiswa_id', '=', 'am.mahasiswa_id')
            ->where('kkm.nilai_akhir_huruf', 'E')
            ->select('am.tahun_masuk', DB::raw('count(*) as total_gagal'))
            ->groupBy('am.tahun_masuk')
            ->pluck('total_gagal', 'tahun_masuk');

        foreach ($students as $angkatan => $list) {
            if (!$angkatan) continue;

            $totalCurrentIps = 0;
            $totalPrevIps = 0;
            $studentCount = 0;

            foreach ($list as $am) {
                $ipsRecord = $am->mahasiswa->ipsmahasiswa ?? null;
                if (!$ipsRecord) continue;

                $currentIps = 0;
                $prevIps = 0;
                $foundCurrent = false;

                // Find latest IPS
                $maxSemesters = 14; 
                for ($i = $maxSemesters; $i >= 1; $i--) {
                    $col = 'ips_' . $i;
                    if (!is_null($ipsRecord->$col)) {
                        $currentIps = $ipsRecord->$col;
                        
                        // Find previous IPS
                        if ($i > 1) {
                             $prevCol = 'ips_' . ($i - 1);
                             $prevIps = $ipsRecord->$prevCol ?? 0;
                        }
                        $foundCurrent = true;
                        break;
                    }
                }
                
                if ($foundCurrent) {
                    $totalCurrentIps += $currentIps;
                    $totalPrevIps += $prevIps;
                    $studentCount++;
                }
            }

            $avgCurrent = $studentCount > 0 ? $totalCurrentIps / $studentCount : 0;
            $avgPrev = $studentCount > 0 ? $totalPrevIps / $studentCount : 0;
            
            $trend = 'tetap';
            if ($avgCurrent > $avgPrev) $trend = 'naik';
            elseif ($avgCurrent < $avgPrev) $trend = 'turun';

            $results[] = [
                'angkatan' => $angkatan,
                'tren_ips' => $trend,
                'mk_gagal' => $failedCounts[$angkatan] ?? 0,
            ];
        }
        
        // Sort by angkatan desc
        usort($results, function($a, $b) {
            return $b['angkatan'] <=> $a['angkatan']; // Descending
        });

        return $results;
    }

    public function getDaftarMahasiswaGagalPerAngkatan($angkatan)
    {
        // 1. Find all students in that angkatan who have at least one 'E' grade
        // We need to join AkademikMahasiswa (for angkatan) -> Mahasiswa -> KhsKrsMahasiswa (for grades)
        
        $students = \App\Models\AkademikMahasiswa::query()
            ->where('tahun_masuk', $angkatan)
            ->whereHas('mahasiswa.khskrsmahasiswa', function($q) {
                $q->where('nilai_akhir_huruf', 'E');
            })
            ->with(['mahasiswa' => function($q) {
                $q->withCount(['khskrsmahasiswa as jumlah_e' => function($sq) {
                    $sq->where('nilai_akhir_huruf', 'E');
                }]);
                $q->with('user'); // For name
            }, 'early_warning_systems'])
            ->get();

        return $students->map(function($am) {
            $mahasiswa = $am->mahasiswa;
            $user = $mahasiswa->user;
            
            // Get EWS status if exists, else '-'
            // Relationship is HasMany in AkademikMahasiswa model: 'early_warning_systems'
            // We usually take the latest or relevant one. Assuming one relevant record or taking the first/latest.
            $ews = $am->early_warning_systems->first();
            $status = $ews ? $ews->status : '-';

            return [
                'nama' => $user ? $user->name : 'Unknown',
                'nim' => $mahasiswa->nim,
                'jumlah_nilai_e' => $mahasiswa->jumlah_e,
                'status_ews' => $status,
            ];
        });
    }
}
