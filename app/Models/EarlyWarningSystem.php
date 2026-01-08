<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarlyWarningSystem extends Model
{
    protected $table = 'early_warning_system';

    protected $fillable = [
        'akademik_mahasiswa_id',
        'status',
        'status_kelulusan',
        'status_rekomitmen',
        'link_rekomitmen',
    ];

    
    public function akademik_mahasiswa()
    {
        return $this->belongsTo(AkademikMahasiswa::class,'akademik_mahasiswa_id', 'id');
    }
}
