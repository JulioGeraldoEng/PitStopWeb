<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produto;

class ProdutoSeeder extends Seeder
{
    public function run(): void
    {
        $produtos = [
            [
                'nome' => 'Óleo Motor 20W50',
                'preco' => 45.90,
                'quantidade' => 50
            ],
            [
                'nome' => 'Filtro de Óleo',
                'preco' => 25.50,
                'quantidade' => 30
            ],
            [
                'nome' => 'Pastilha de Freio Dianteira',
                'preco' => 89.90,
                'quantidade' => 20
            ],
            [
                'nome' => 'Amortecedor Dianteiro',
                'preco' => 350.00,
                'quantidade' => 10
            ],
            [
                'nome' => 'Correia Dentada',
                'preco' => 120.00,
                'quantidade' => 15
            ],
        ];

        foreach ($produtos as $produto) {
            Produto::create($produto);
        }
        
        $this->command->info('✅ ' . count($produtos) . ' produtos criados com sucesso!');
    }
}