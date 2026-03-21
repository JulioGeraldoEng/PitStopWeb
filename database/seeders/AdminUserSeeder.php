<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        if (!User::where('email', 'admin@pitstop.com')->exists()) {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@pitstop.com',
                'password' => Hash::make('admin123'),
                'tipo' => 'admin',
                'telefone' => '(18) 99798-7391',
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('✅ Usuário admin criado com sucesso!');
        } else {
            $this->command->info('⚠️ Usuário admin já existe!');
        }
    }
}