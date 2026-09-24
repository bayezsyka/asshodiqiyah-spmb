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
        User::updateOrCreate(
            ['username' => 'lenterahati'],
            [
                'name' => 'Admin Lentera Hati',
                'email' => 'lenterahati@spmb.lenterahatiibs.com',
                'password' => Hash::make('lenterahati'),
                'peran' => PeranUser::Superadmin,
                'status_aktif' => true,
            ]
        );
    }
}
