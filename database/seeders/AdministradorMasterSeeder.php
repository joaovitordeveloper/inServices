<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdministradorMasterSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'developer.joaovitor@gmail.com'],
            [
                'name' => 'Administrador Master',
                'tipo' => 'administrador_geral',
                'password' => Hash::make('123456789'),
            ]
        );
    }
}
