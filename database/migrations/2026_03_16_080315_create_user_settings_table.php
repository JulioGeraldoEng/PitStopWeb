<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->increments('id'); // SQLite friendly
            
            $table->unsignedInteger('user_id'); // SQLite friendly
            $table->string('tema', 20)->default('claro');
            $table->string('idioma', 10)->default('pt-BR');
            $table->boolean('notificacoes_email')->default(true);
            $table->boolean('notificacoes_sistema')->default(true);
            $table->boolean('backup_automatico')->default(true);
            $table->boolean('two_factor')->default(false);
            $table->timestamps();
            
            // Adiciona foreign key manualmente para SQLite
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Unique constraint
            $table->unique('user_id');
            
            // Índices
            $table->index('tema');
            $table->index('idioma');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};