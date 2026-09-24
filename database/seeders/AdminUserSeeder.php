<?php

namespace Database\Seeders;

use App\Enums\PeranUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $username = env('SPMB_SUPERADMIN_USERNAME', 'admin');
        $password = env('SPMB_SUPERADMIN_PASSWORD', 'admin');

        User::updateOrCreate(
            ['username' => $username],
            [
                'name' => env('SPMB_SUPERADMIN_NAMA', 'Superadmin Asshodiqiyah'),
                'email' => env('SPMB_SUPERADMIN_EMAIL', 'admin@asshodiqiyah.com'),
                'password' => Hash::make($password),
                'peran' => PeranUser::Superadmin,
                'status_aktif' => true,
            ]
        );
    }
}
