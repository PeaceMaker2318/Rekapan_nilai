<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin',
            'nip' => '123123123',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        // Daftar guru
        $guruUsers = [
            ['name' => 'Iis Nuryati, S.Pd.SD', 'nip' => '197507052022211002', 'role' => 'guru_pai'],
            ['name' => 'Ecin S. S.Pd.SD', 'nip' => '198305162023212006', 'role' => 'guru'],
            ['name' => 'Heni Apriani. S.Pd', 'nip' => '198907212025212029', 'role' => 'guru'],
            ['name' => 'Boy Pangestu, S.Pd', 'nip' => '197304102024211001', 'role' => 'guru'],
            ['name' => 'SALSIH, S.Pd.SD', 'nip' => '197204132014052001', 'role' => 'guru'],
            ['name' => 'Melati Wulandari, S.Pd', 'nip' => '198510192021212002', 'role' => 'guru'],
            ['name' => 'Herman Sasmita, S.Pd', 'nip' => '198603102023211006', 'role' => 'guru'],
            ['name' => 'Mochamad Dicky, S.Pd', 'nip' => '199115122023211004', 'role' => 'guru'],
            ['name' => 'Suherman, S.Pd.I', 'nip' => '197901182021211001', 'role' => 'guru'],
            ['name' => 'Idah Faridah, S.Pd.MM', 'nip' => '197310221996032002', 'role' => 'kepsek'],
            ['name' => 'Putri Agustin, S.Pd', 'nip' => '1989072', 'role' => 'guru'],
        ];

        foreach ($guruUsers as $guru) {
            User::create([
                'name' => $guru['name'],
                'nip' => $guru['nip'],
                'password' => bcrypt('test123'),
                'role' => $guru['role'],
            ]);
        }

        // Kepala Sekolah
        User::create([
            'name' => 'Kepala Sekolah',
            'nip' => '12345678',
            'password' => bcrypt('test123'),
            'role' => 'kepsek',
        ]);

        // Siswa
        User::create([
            'name' => 'Contoh Siswa',
            'nip' => '123',
            'password' => bcrypt('test123'),
            'role' => 'siswa',
        ]);
    }
}
