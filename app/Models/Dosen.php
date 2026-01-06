<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dosen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dosen';

    protected $fillable = [
        'user_id',
        'prodi_id',
        'gelar_depan',
        'gelar_belakang',
        'bidang_kajian',
        'scholar_link',
        'npp',
        'telepon',
        'ttd'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id', 'id');
    }

    public function kelompok_mata_kuliah()
    {
        return $this->hasMany(KelompokMataKuliah::class, 'dosen_pengampu_id');
    }

    public function akademik_mahasiswa()
    {
        return $this->hasMany(AkademikMahasiswa::class, 'dosen_wali_id', 'id');
    }

}
