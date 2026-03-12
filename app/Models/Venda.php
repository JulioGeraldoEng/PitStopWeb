<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    protected $fillable = ['cliente_id', 'data', 'data_vencimento', 'total'];

    // Uma venda pertence a um cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Uma venda tem muitos itens
    public function itens()
    {
        return $this->hasMany(ItemVenda::class, 'venda_id');
    }

    // Uma venda tem um recebimento
    public function recebimento()
    {
        return $this->hasOne(Recebimento::class);
    }
}