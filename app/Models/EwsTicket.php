<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EwsTicket extends Model
{
    protected $table = 'ews_tickets';

    protected $fillable = [
        'mahasiswa_id',
        'ews_id',
        'jenis',
        'warnings',
        'status',
        'status_kirim',
        'tanggal_kirim',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'id');
    }

    public function ews()
    {
        return $this->belongsTo(EarlyWarningSystem::class, 'ews_id', 'id');
    }
}
