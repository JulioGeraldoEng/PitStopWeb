<?php
// app/Models/Backup.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'backups';

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'filename',
        'size',
        'type',        // manual, automatico, importacao, seguranca
        'status',      // sucesso, erro
        'error_message'
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento: Um backup pertence a um usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor: Formatar tamanho do arquivo
     */
    public function getSizeFormattedAttribute()
    {
        $bytes = $this->size;
        
        if (!$bytes) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Accessor: Tipo formatado
     */
    public function getTypeFormattedAttribute()
    {
        $types = [
            'manual' => 'Backup Manual',
            'automatico' => 'Backup Automático',
            'importacao' => 'Importação',
            'seguranca' => 'Backup de Segurança'
        ];
        
        return $types[$this->type] ?? $this->type;
    }

    /**
     * Accessor: Badge de status para exibição
     */
    public function getStatusBadgeAttribute()
    {
        if ($this->status === 'sucesso') {
            return '<span class="badge bg-success">Sucesso</span>';
        }
        
        return '<span class="badge bg-danger">Erro</span>';
    }

    /**
     * Scope: Filtrar por tipo
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Filtrar por status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Backups recentes
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Backups de um usuário específico
     */
    public function scopeOfUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Verificar se o arquivo de backup existe
     */
    public function fileExists()
    {
        if ($this->type === 'seguranca') {
            return file_exists(storage_path("app/backups/seguranca/{$this->filename}"));
        }
        
        return file_exists(storage_path("app/backups/{$this->filename}"));
    }

    /**
     * Obter o caminho completo do arquivo
     */
    public function getFilePathAttribute()
    {
        if ($this->type === 'seguranca') {
            return storage_path("app/backups/seguranca/{$this->filename}");
        }
        
        return storage_path("app/backups/{$this->filename}");
    }

    /**
     * Excluir o arquivo físico ao deletar o registro
     */
    protected static function booted()
    {
        static::deleting(function ($backup) {
            if ($backup->fileExists()) {
                unlink($backup->file_path);
            }
        });
    }
}