<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    protected $table = 'mata_kuliahs';

    protected $fillable = [
        'kode',
        'name',
        'sks',
        'semester',
        'peminatan_id',
    ];

    public function peminatan()
    {
        return $this->belongsTo(MataKuliahPeminatan::class, 'peminatan_id');
    }
}
