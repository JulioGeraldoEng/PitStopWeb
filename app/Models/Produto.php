<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $fillable = ['nome', 'preco', 'quantidade'];

    // Um produto aparece em muitos itens de venda
    public function itensVenda()
    {
        return $this->hasMany(ItemVenda::class);
    }
}