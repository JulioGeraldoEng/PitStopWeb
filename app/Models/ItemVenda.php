<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemVenda extends Model
{
    protected $table = 'itens_venda';
    
    protected $fillable = ['venda_id', 'produto_id', 'nome_produto', 'quantidade', 'preco_unitario'];

    // Um item pertence a uma venda
    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    // Um item pertence a um produto
    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}