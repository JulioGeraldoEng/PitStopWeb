<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'telefone',      // <-- ADICIONADO: campo para WhatsApp
        'password',
        'tipo',          // admin/usuario
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ==================== RELACIONAMENTOS ====================
    
    /**
     * Um usuário pode ter muitas configurações (mas geralmente é 1:1)
     */
    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }

    /**
     * Um usuário pode ter muitos clientes (opcional)
     */
    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    /**
     * Um usuário pode ter muitas vendas (via clientes)
     */
    public function vendas()
    {
        return $this->hasManyThrough(Venda::class, Cliente::class);
    }

    // ==================== MÉTODOS ÚTEIS ====================

    /**
     * Verifica se o usuário é admin
     */
    public function isAdmin(): bool
    {
        return $this->tipo === 'admin';
    }

    /**
     * Formata o telefone para exibição
     */
    public function getTelefoneFormatadoAttribute(): ?string
    {
        if (!$this->telefone) {
            return null;
        }

        // Remove tudo que não é número
        $telefone = preg_replace('/[^0-9]/', '', $this->telefone);
        
        if (strlen($telefone) === 11) {
            return '(' . substr($telefone, 0, 2) . ') ' 
                   . substr($telefone, 2, 5) . '-' 
                   . substr($telefone, 7);
        }
        
        if (strlen($telefone) === 10) {
            return '(' . substr($telefone, 0, 2) . ') ' 
                   . substr($telefone, 2, 4) . '-' 
                   . substr($telefone, 6);
        }
        
        return $this->telefone;
    }

    /**
     * Formata o telefone para envio no WhatsApp (com código do país)
     */
    public function getTelefoneWhatsAppAttribute(): ?string
    {
        if (!$this->telefone) {
            return null;
        }

        // Remove tudo que não é número
        $telefone = preg_replace('/[^0-9]/', '', $this->telefone);
        
        // Adiciona código do Brasil se necessário
        if (strlen($telefone) === 10 || strlen($telefone) === 11) {
            return '55' . $telefone;
        }
        
        return $telefone;
    }

    /**
     * Inicializa as configurações padrão para um novo usuário
     */
    protected static function booted()
    {
        static::created(function ($user) {
            // Cria configurações padrão quando um usuário é criado
            $user->settings()->create([
                'tema' => 'claro',
                'idioma' => 'pt-BR',
                'notificacoes_sistema' => true,
                'notificacoes_whatsapp' => false,
                'notificacoes_email' => true,
                'notif_atrasados' => true,
                'notif_pendentes' => false,
                'notif_estoque_baixo' => true,
                'notif_produto_zerado' => true,
                'frequencia_whatsapp' => 'diario',
            ]);
        });
    }
}