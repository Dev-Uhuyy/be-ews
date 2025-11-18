<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Periode extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'periode';

    protected $fillable = [
        'tahun_ajaran',
        'kode_periode',
        'tgl_awal',
        'tgl_akhir',
        'tgl_awal_ta',
        'tgl_akhir_ta',
        'tgl_awal_bk',
        'tgl_akhir_bk',
        'alokasi_kuota',
        'status',
    ];

    public static function checkCurrentDateInRange()
    {
        $currentDate = now(); // Mendapatkan tanggal sekarang
        $periode = self::where('tgl_awal', '<=', $currentDate)
            ->where('tgl_akhir', '>=', $currentDate)
            ->where('status', 1)
            ->first();

        if (!$periode) {
            return false; // Tidak ada periode yang valid
        }

        return true; // Tanggal sekarang dalam range
    }


    public static function periodeNow()
    {
        return Periode::where('status', '1')->first([
            'id',
            'kode_periode',
            'tahun_ajaran',
            'status',
            'tgl_awal',
            'tgl_akhir',
            'tgl_awal_ta',
            'tgl_akhir_ta',
        ]);
    }

    public function isPeriodeNow(){
        $periode = self::where('status', 1)->first();
        if(!$periode){
            return false;
        }
        return true;
    }

    public static function deactivateAllStatus()
    {
        return self::where('status', 1)->update(['status' => 0]);
    }
}
