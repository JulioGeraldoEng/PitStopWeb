<?php
// database/migrations/2024_03_16_000002_create_user_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            
            // Chave estrangeira para usuários
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade')
                  ->unique() // Um usuário tem apenas uma configuração
                  ->comment('ID do usuário');
            
            // Configurações de aparência
            $table->string('tema', 20)
                  ->default('claro')
                  ->comment('claro, escuro, auto');
                  
            $table->string('idioma', 10)
                  ->default('pt-BR')
                  ->comment('pt-BR, en, es');
            
            // Configurações de notificação
            $table->boolean('notificacoes_email')
                  ->default(true)
                  ->comment('Receber notificações por email');
                  
            $table->boolean('notificacoes_sistema')
                  ->default(true)
                  ->comment('Notificações no sistema');
            
            // Configurações de backup
            $table->boolean('backup_automatico')
                  ->default(true)
                  ->comment('Backup automático diário');
            
            // Configurações de segurança
            $table->boolean('two_factor')
                  ->default(false)
                  ->comment('Autenticação de dois fatores');
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->index('tema');
            $table->index('idioma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};