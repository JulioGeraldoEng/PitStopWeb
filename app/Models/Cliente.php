<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = ['nome', 'telefone', 'observacao'];

    // Um cliente tem muitas vendas
    public function vendas()
    {
        return $this->hasMany(Venda::class);
    }
}