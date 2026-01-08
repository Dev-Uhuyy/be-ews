<?php

namespace Database\Seeders;

use App\Models\MataKuliah;
use App\Models\MataKuliahPeminatan;
use Illuminate\Database\Seeder;

class MataKuliahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $peminatans = MataKuliahPeminatan::all();

        foreach ($peminatans as $peminatan) {
            // Berikan 3 seeder untuk masing masing peminatan
            for ($i = 1; $i <= 3; $i++) {
                MataKuliah::create([
                    'prodi_id' => 1,
                    'peminatan_id' => $peminatan->id,
                    'kode' => $peminatan->peminatan . '10' . $i,
                    'name' => 'Mata Kuliah ' . $peminatan->peminatan . ' ' . $i,
                    'sks' => 3,
                    'semester' => 5,
                ]);
            }
        }
    }
}
