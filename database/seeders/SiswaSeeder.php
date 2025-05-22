<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Siswa;

class SiswaSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Ambil semua kelas
        $kelasIds = DB::table('kelas')->pluck('id')->toArray();
        $offset = 1000;

        for ($i = 1; $i <= 20; $i++) {
            $nis = 'NIS' . str_pad($i + $offset, 4, '0', STR_PAD_LEFT);
            $nama = $faker->name();
            $email = strtolower(str_replace(' ', '_', $nama)) . $i . '@example.com';

            // Buat user terlebih dahulu
            $user = User::create([
                'name' => $nama,
                'email' => $email,
                'password' => Hash::make('password123'), // password default
                'role' => 'siswa',
                'nip' => $nis,
            ]);

            // Buat data siswa terhubung ke user
            Siswa::create([
                'user_id' => $user->id,
                'kelas_id' => $faker->randomElement($kelasIds),
                'nama' => $nama,
                'nis' => $nis,
                'alamat' => $faker->address(),
                'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
