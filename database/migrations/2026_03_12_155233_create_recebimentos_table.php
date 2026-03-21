<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recebimentos', function (Blueprint $table) {
            $table->increments('id'); // SQLite friendly
            $table->unsignedInteger('venda_id'); // SQLite friendly
            $table->date('data_vencimento');
            $table->decimal('valor_total', 10, 2);
            $table->decimal('valor_pago', 10, 2)->default(0);
            $table->date('data_pagamento')->nullable();
            $table->string('status', 20)->default('pendente');
            $table->string('forma_pagamento', 50)->nullable();
            $table->timestamps();
            
            // Adiciona foreign key manualmente para SQLite
            $table->foreign('venda_id')->references('id')->on('vendas')->onDelete('cascade');
            
            // Adiciona índice para status
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recebimentos');
    }
};