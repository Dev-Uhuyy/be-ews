<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Mahasiswa extends Model
{
    use SoftDeletes, HasFactory, Notifiable;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'user_id',
        'periode_id',
        'nim',
        'ipk',
        'transkrip',
        'telepon',
        'minat',
        'sks',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
