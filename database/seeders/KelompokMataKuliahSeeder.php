<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\KelompokMataKuliah;
use App\Models\MataKuliahPeminatan;
use Illuminate\Database\Seeder;

class KelompokMataKuliahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $peminatans = MataKuliahPeminatan::with('matakuliah')->get();
        
        // Get all Dosen IDs
        $dosenIds = Dosen::pluck('id')->toArray();
        
        // Fallback if no dosens exist (should not happen if DosenSeeder runs first)
        if (empty($dosenIds)) {
             // In a real scenario we might create one, but here we assume DosenSeeder ran.
             return; 
        }

        foreach ($peminatans as $peminatan) {
             // "buatkan masing masing 2 seeder untuk 1 peminatan"
             // Determine valid range (10-20)
             $validDosenIds = array_filter($dosenIds, fn($id) => $id >= 10 && $id <= 20);
             // If no IDs in 10-20 range, use any available ID
             $pool = !empty($validDosenIds) ? $validDosenIds : $dosenIds;

             if ($peminatan->matakuliah->isEmpty()) {
                 continue;
             }

             for ($i = 1; $i <= 2; $i++) {
                 // Pick a random MK from this peminatan
                 $mk = $peminatan->matakuliah->random();
                 
                 // Pick a random Dosen ID
                 $dosenId = $pool[array_rand($pool)];

                 KelompokMataKuliah::create([
                     'mata_kuliah_id' => $mk->id,
                     'kode' => 'Klmpk-' . $peminatan->peminatan . '-' . $i,
                     'dosen_pengampu_id' => $dosenId,
                 ]);
             }
        }
    }
}
