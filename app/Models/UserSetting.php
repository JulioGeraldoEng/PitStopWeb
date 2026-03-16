<?php
// app/Models/UserSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'user_settings';

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'tema',
        'idioma',
        'notificacoes_email',
        'notificacoes_sistema',
        'notificacoes_whatsapp',  // <-- ADICIONADO
        'notif_atrasados',         // <-- ADICIONADO
        'notif_pendentes',         // <-- ADICIONADO
        'notif_estoque_baixo',     // <-- ADICIONADO
        'notif_produto_zerado',    // <-- ADICIONADO
        'frequencia_whatsapp',     // <-- ADICIONADO
        'backup_automatico',
        'two_factor',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'notificacoes_email' => 'boolean',
        'notificacoes_sistema' => 'boolean',
        'backup_automatico' => 'boolean',
        'two_factor' => 'boolean',
    ];

    /**
     * Relacionamento: Uma configuração pertence a um usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}