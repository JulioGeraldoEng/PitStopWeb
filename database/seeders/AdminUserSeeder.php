<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@pitstop.com',
            'password' => Hash::make('admin123'),
            'tipo' => 'admin',
            'telefone' => '(18) 99798-7391',
        ]);

        $this->command->info('✅ Usuário admin criado com sucesso!');
    }
}