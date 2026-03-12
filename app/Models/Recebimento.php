<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recebimento extends Model
{
    protected $fillable = [
        'venda_id', 
        'data_vencimento', 
        'valor_total', 
        'valor_pago', 
        'data_pagamento', 
        'status', 
        'forma_pagamento'
    ];

    // Um recebimento pertence a uma venda
    public function venda()
    {
        return $this->belongsTo(Venda::class, 'venda_id');
    }
}