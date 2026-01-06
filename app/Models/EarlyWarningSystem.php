<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarlyWarningSystem extends Model
{
    protected $table = 'early_warning_system';

    protected $fillable = [
        'akademik_mahasiswa_id',
        'status',
    ];

    public function akademik_mahasiswa()
    {
        return $this->belongsTo(AkademikMahasiswa::class,'akademik_mahasiswa_id', 'id');
    }

    public function ews_tickets()
    {
        return $this->hasMany(EwsTicket::class, 'ews_id', 'id');
    }
}
