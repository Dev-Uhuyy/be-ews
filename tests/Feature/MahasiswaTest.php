<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\AkademikMahasiswa;
use App\Models\IpsMahasiswa;
use App\Models\EarlyWarningSystem;
use App\Models\KhsKrsMahasiswa;
use App\Models\MataKuliah;
use App\Models\KelompokMataKuliah;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class MahasiswaTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup roles - use firstOrCreate to avoid duplicate errors
        Role::firstOrCreate(['name' => 'mahasiswa', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'dosen', 'guard_name' => 'web']);
    }

    /**
     * Test mahasiswa can access dashboard status
     */
    public function test_mahasiswa_can_access_dashboard_status(): void
    {
        // Setup data
        $prodi = Prodi::create(['nama' => 'Teknik Informatika']);

        $user = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole('mahasiswa');

        $mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'nim' => '2023001',
            'telepon' => '081234567890',
        ]);

        $dosenUser = User::create([
            'name' => 'Dr. Dosen Wali',
            'email' => 'dosen@example.com',
            'password' => Hash::make('password123'),
        ]);

        $dosen = Dosen::create([
            'user_id' => $dosenUser->id,
            'npp' => '198501012010011001',
            'gelar_depan' => 'Dr.',
            'gelar_belakang' => 'M.Kom',
        ]);

        $akademik = AkademikMahasiswa::create([
            'mahasiswa_id' => $mahasiswa->id,
            'dosen_wali_id' => $dosen->id,
            'semester_aktif' => 5,
            'tahun_masuk' => 2023,
            'ipk' => 3.50,
            'sks_lulus' => 100,
        ]);

        $ips = IpsMahasiswa::create([
            'mahasiswa_id' => $mahasiswa->id,
            'ips_1' => 3.40,
            'ips_2' => 3.50,
            'ips_3' => 3.60,
            'ips_4' => 3.55,
            'ips_5' => 3.50,
        ]);

        EarlyWarningSystem::create([
            'akademik_mahasiswa_id' => $akademik->id,
            'status' => 'normal',
        ]);

        // Test API
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/mahasiswa/dashboard/status-mahasiswa');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'status_ews',
                    'akademik' => [
                        'ipk',
                        'sks_lulus',
                        'semester_aktif',
                        'dosen_wali',
                    ],
                    'ips_collection'
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'status_ews' => 'normal',
                    'akademik' => [
                        'ipk' => 3.50,
                        'sks_lulus' => 100,
                        'semester_aktif' => 5,
                    ]
                ]
            ]);
    }

    /**
     * Test mahasiswa can get status akademik
     */
    public function test_mahasiswa_can_get_status_akademik(): void
    {
        // Setup data
        $prodi = Prodi::create(['nama' => 'Teknik Informatika']);

        $user = User::create([
            'name' => 'Ani Rahayu',
            'email' => 'ani@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole('mahasiswa');

        $mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'nim' => '2023002',
        ]);

        $dosenUser = User::create([
            'name' => 'Prof. Dosen',
            'email' => 'prof@example.com',
            'password' => Hash::make('password123'),
        ]);

        $dosen = Dosen::create([
            'user_id' => $dosenUser->id,
            'npp' => '198501012010011002',
        ]);

        $akademik = AkademikMahasiswa::create([
            'mahasiswa_id' => $mahasiswa->id,
            'dosen_wali_id' => $dosen->id,
            'semester_aktif' => 6,
            'ipk' => 3.75,
        ]);

        IpsMahasiswa::create([
            'mahasiswa_id' => $mahasiswa->id,
            'ips_1' => 3.60,
            'ips_2' => 3.70,
            'ips_3' => 3.80,
        ]);

        EarlyWarningSystem::create([
            'akademik_mahasiswa_id' => $akademik->id,
            'status' => 'tepat_waktu',
        ]);

        // Test API
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/mahasiswa/akademik/status-akademik');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'nama_mahasiswa',
                    'nim',
                    'prodi',
                    'dosen_wali',
                    'status_ews',
                    'data_ips',
                    'matkul_gagal'
                ],
                'meta'
            ])
            ->assertJson([
                'data' => [
                    'nim' => '2023002',
                    'status_ews' => 'tepat_waktu',
                ]
            ]);
    }

    /**
     * Test mahasiswa can get daftar nilai with passing grades
     */
    public function test_mahasiswa_can_get_daftar_nilai_with_grades(): void
    {
        // Setup data
        $prodi = Prodi::create(['nama' => 'Teknik Informatika']);

        $user = User::create([
            'name' => 'Citra Dewi',
            'email' => 'citra@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole('mahasiswa');

        $mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'nim' => '2023003',
        ]);

        $dosenUser = User::create([
            'name' => 'Dosen Pengampu',
            'email' => 'pengampu@example.com',
            'password' => Hash::make('password123'),
        ]);

        $dosen = Dosen::create([
            'user_id' => $dosenUser->id,
            'npp' => '198501012010011003',
        ]);

        $matakuliah1 = MataKuliah::create([
            'prodi_id' => $prodi->id,
            'kode' => 'IF101',
            'name' => 'Pemrograman Dasar',
            'sks' => 3,
            'semester' => 1,
        ]);

        $matakuliah2 = MataKuliah::create([
            'prodi_id' => $prodi->id,
            'kode' => 'IF102',
            'name' => 'Algoritma',
            'sks' => 3,
            'semester' => 1,
        ]);

        $kelompok1 = KelompokMataKuliah::create([
            'mata_kuliah_id' => $matakuliah1->id,
            'kode' => 'A',
            'dosen_pengampu_id' => $dosen->id,
        ]);

        $kelompok2 = KelompokMataKuliah::create([
            'mata_kuliah_id' => $matakuliah2->id,
            'kode' => 'B',
            'dosen_pengampu_id' => $dosen->id,
        ]);

        // Create grades
        KhsKrsMahasiswa::create([
            'mahasiswa_id' => $mahasiswa->id,
            'matakuliah_id' => $matakuliah1->id,
            'kelompok_id' => $kelompok1->id,
            'status' => 'B',
            'nilai_akhir_angka' => 85,
            'nilai_akhir_huruf' => 'A',
        ]);

        KhsKrsMahasiswa::create([
            'mahasiswa_id' => $mahasiswa->id,
            'matakuliah_id' => $matakuliah2->id,
            'kelompok_id' => $kelompok2->id,
            'status' => 'B',
            'nilai_akhir_angka' => 78,
            'nilai_akhir_huruf' => 'B',
        ]);

        // Test API
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/mahasiswa/akademik/nilai-mahasiswa');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'nama_matkul',
                        'kode_matkul',
                        'sks',
                        'nilai_huruf',
                        'nilai_angka'
                    ]
                ],
                'meta'
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test mahasiswa with failed courses shows in status akademik
     */
    public function test_status_akademik_shows_failed_courses(): void
    {
        // Setup data
        $prodi = Prodi::create(['nama' => 'Teknik Informatika']);

        $user = User::create([
            'name' => 'Dedi Kurniawan',
            'email' => 'dedi@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole('mahasiswa');

        $mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'nim' => '2023004',
        ]);

        $dosenUser = User::create([
            'name' => 'Dosen Test',
            'email' => 'dosentest@example.com',
            'password' => Hash::make('password123'),
        ]);

        $dosen = Dosen::create([
            'user_id' => $dosenUser->id,
            'npp' => '198501012010011004',
        ]);

        $akademik = AkademikMahasiswa::create([
            'mahasiswa_id' => $mahasiswa->id,
            'dosen_wali_id' => $dosen->id,
            'semester_aktif' => 5,
            'ipk' => 2.50,
        ]);

        $matakuliah = MataKuliah::create([
            'prodi_id' => $prodi->id,
            'kode' => 'IF201',
            'name' => 'Basis Data',
            'sks' => 3,
            'semester' => 2,
        ]);

        $kelompok = KelompokMataKuliah::create([
            'mata_kuliah_id' => $matakuliah->id,
            'kode' => 'A',
            'dosen_pengampu_id' => $dosen->id,
        ]);

        // Create failed course
        KhsKrsMahasiswa::create([
            'mahasiswa_id' => $mahasiswa->id,
            'matakuliah_id' => $matakuliah->id,
            'kelompok_id' => $kelompok->id,
            'status' => 'B',
            'nilai_akhir_angka' => 45,
            'nilai_akhir_huruf' => 'E',
        ]);

        EarlyWarningSystem::create([
            'akademik_mahasiswa_id' => $akademik->id,
            'status' => 'perhatian',
        ]);

        // Test API
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/mahasiswa/akademik/status-akademik');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status_ews' => 'perhatian',
                ]
            ]);

        // Check failed courses
        $matkul_gagal = $response->json('data.matkul_gagal');
        $this->assertCount(1, $matkul_gagal);
        $this->assertEquals('Basis Data', $matkul_gagal[0]['nama_matkul']);
        $this->assertEquals('E', $matkul_gagal[0]['nilai_huruf']);
    }

    /**
     * Test mahasiswa without data returns 404
     */
    public function test_dashboard_returns_404_when_mahasiswa_not_found(): void
    {
        $user = User::create([
            'name' => 'User Without Mahasiswa',
            'email' => 'nodata@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole('mahasiswa');

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/mahasiswa/dashboard/status-mahasiswa');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Data mahasiswa not found for this user'
            ]);
    }

    /**
     * Test mahasiswa can create and read data
     */
    public function test_mahasiswa_model_relationships(): void
    {
        $user = User::create([
            'name' => 'Eko Prasetyo',
            'email' => 'eko@example.com',
            'password' => Hash::make('password123'),
        ]);

        $mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'nim' => '2023005',
            'telepon' => '081234567890',
            'minat' => 'Data Science',
        ]);

        // Test relationships
        $this->assertInstanceOf(User::class, $mahasiswa->user);
        $this->assertEquals('Eko Prasetyo', $mahasiswa->user->name);
    }

    /**
     * Test IPS data stored correctly for mahasiswa
     */
    public function test_ips_mahasiswa_stores_semester_grades(): void
    {
        $prodi = Prodi::create(['nama' => 'Teknik Informatika']);

        $user = User::create([
            'name' => 'Fitri Handayani',
            'email' => 'fitri@example.com',
            'password' => Hash::make('password123'),
        ]);

        $mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'nim' => '2023006',
        ]);

        $ips = IpsMahasiswa::create([
            'mahasiswa_id' => $mahasiswa->id,
            'ips_1' => 3.45,
            'ips_2' => 3.55,
            'ips_3' => 3.65,
            'ips_4' => 3.70,
            'ips_5' => 3.75,
            'ips_6' => 3.80,
        ]);

        $this->assertEquals(3.45, $ips->ips_1);
        $this->assertEquals(3.80, $ips->ips_6);
        $this->assertInstanceOf(Mahasiswa::class, $ips->mahasiswa);
    }

    /**
     * Test EWS status updates correctly
     */
    public function test_early_warning_system_tracks_mahasiswa_status(): void
    {
        $prodi = Prodi::create(['nama' => 'Teknik Informatika']);

        $user = User::create([
            'name' => 'Gilang Ramadhan',
            'email' => 'gilang@example.com',
            'password' => Hash::make('password123'),
        ]);

        $mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'nim' => '2023007',
        ]);

        $dosenUser = User::create([
            'name' => 'Dosen Wali',
            'email' => 'dosenwali@example.com',
            'password' => Hash::make('password123'),
        ]);

        $dosen = Dosen::create([
            'user_id' => $dosenUser->id,
            'npp' => '198501012010011005',
        ]);

        $akademik = AkademikMahasiswa::create([
            'mahasiswa_id' => $mahasiswa->id,
            'dosen_wali_id' => $dosen->id,
            'semester_aktif' => 7,
            'ipk' => 2.10,
        ]);

        $ews = EarlyWarningSystem::create([
            'akademik_mahasiswa_id' => $akademik->id,
            'status' => 'kritis',
            'status_kelulusan' => 'noneligible',
        ]);

        $this->assertEquals('kritis', $ews->status);
        $this->assertEquals('noneligible', $ews->status_kelulusan);
        $this->assertInstanceOf(AkademikMahasiswa::class, $ews->akademik_mahasiswa);
    }
}
