<?php
// database/migrations/2024_03_16_xxxxxx_add_telefone_to_users_table.php

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
        Schema::table('users', function (Blueprint $table) {
            // Adiciona campo telefone após o email
            $table->string('telefone', 20)
                  ->nullable()
                  ->after('email')
                  ->comment('Número de WhatsApp para notificações');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove o campo telefone se precisar reverter
            $table->dropColumn('telefone');
        });
    }
};