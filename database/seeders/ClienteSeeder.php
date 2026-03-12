<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'nome' => 'João Silva',
                'telefone' => '14999998888',
                'observacao' => 'Cliente desde 2023'
            ],
            [
                'nome' => 'Maria Santos',
                'telefone' => '14977776666',
                'observacao' => 'Prefere contato por WhatsApp'
            ],
            [
                'nome' => 'Carlos Oliveira',
                'telefone' => '14955554444',
                'observacao' => null
            ],
            [
                'nome' => 'Ana Pereira',
                'telefone' => '14933332222',
                'observacao' => 'Indicou o José'
            ],
            [
                'nome' => 'Pedro Costa',
                'telefone' => '14911110000',
                'observacao' => 'Cliente VIP'
            ],
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }
    }
}