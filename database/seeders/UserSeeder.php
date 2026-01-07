<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Akun Koor
        $koor = User::firstOrCreate(
            ['email' => 'koor@ews.com'],
            [
                'name' => 'Koordinator EWS',
                'password' => 'password', // Password otomatis di-hash oleh model mutator/cast jika ada, tapi karena di User model 'password' => 'hashed', kita bisa pass plain text, TAPI firstOrCreate bypasses mutators kadang. Mari gunakan Hash::make untuk aman, tapi casts berfungsi. Namun casts 'hashed' di Laravel 10+ otomatis handle set. 
                // Mari kita check User model. protected function casts(): array { return ['password' => 'hashed']; }
                // Jadi cukup string biasa.
            ]
        );
        $koor->assignRole('koor');

        // 2. Akun Dosen
        $dosen = User::firstOrCreate(
            ['email' => 'dosen@ews.com'],
            [
                'name' => 'Dosen Penguji',
                'password' => 'password',
            ]
        );
        $dosen->assignRole('dosen');

        // 3. Akun Mahasiswa
        $mahasiswa = User::firstOrCreate(
            ['email' => 'mahasiswa@ews.com'],
            [
                'name' => 'Mahasiswa Test',
                'password' => 'password',
            ]
        );
        $mahasiswa->assignRole('mahasiswa');
    }
}
