<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Guru;
use App\Models\User;

class GuruSeeder extends Seeder
{
    public function run(): void
    {
        $guruList = [
            ['nama' => 'Iis Nuryati, S.Pd.SD', 'nip' => '197507052022211002'],
            ['nama' => 'Ecin S. S.Pd.SD', 'nip' => '198305162023212006'],
            ['nama' => 'Heni Apriani. S.Pd', 'nip' => '198907212025212029'],
            ['nama' => 'Boy Pangestu, S.Pd', 'nip' => '197304102024211001'],
            ['nama' => 'Putri Agustin, S.Pd', 'nip' => '1989072'],
            ['nama' => 'SALSIH, S.Pd.SD', 'nip' => '197204132014052001'],
            ['nama' => 'Melati Wulandari, S.Pd', 'nip' => '198510192021212002'],
            ['nama' => 'Herman Sasmita, S.Pd', 'nip' => '198603102023211006'],
            ['nama' => 'Mochamad Dicky, S.Pd', 'nip' => '199115122023211004'],
            ['nama' => 'Suherman, S.Pd.I', 'nip' => '197901182021211001'],
            ['nama' => 'Idah Faridah, S.Pd.MM', 'nip' => '197310221996032002'],
        ];

        foreach ($guruList as $data) {
            $user = User::where('nip', $data['nip'])
                ->whereIn('role', ['guru', 'guru_pai','kepsek'])
                ->first();

            if ($user) {
                Guru::create([
                    'nama' => $data['nama'],
                    'nip' => $data['nip'],
                    'user_id' => $user->id,
                ]);
            } else {
                echo "User dengan NIP {$data['nip']} tidak ditemukan atau rolenya tidak valid.\n";
            }
        }
    }
}
