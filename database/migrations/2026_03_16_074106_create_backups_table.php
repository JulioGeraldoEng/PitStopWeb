<?php
// database/migrations/2024_03_16_000001_create_backups_table.php

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
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            
            // Chave estrangeira para usuários
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade')
                  ->comment('ID do usuário que gerou o backup');
            
            // Informações do arquivo
            $table->string('filename')
                  ->comment('Nome do arquivo de backup');
                  
            $table->bigInteger('size')
                  ->nullable()
                  ->comment('Tamanho do arquivo em bytes');
            
            // Tipo e status
            $table->string('type')
                  ->default('manual')
                  ->comment('Tipo: manual, automatico, importacao, seguranca');
                  
            $table->string('status')
                  ->default('sucesso')
                  ->comment('Status: sucesso, erro');
                  
            $table->text('error_message')
                  ->nullable()
                  ->comment('Mensagem de erro em caso de falha');
            
            // Timestamps
            $table->timestamps();
            
            // Índices para otimizar consultas
            $table->index(['user_id', 'created_at']);
            $table->index('type');
            $table->index('status');
            
            // Índice composto para buscas frequentes
            $table->index(['user_id', 'type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};