<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->increments('id'); // SQLite friendly
            $table->unsignedInteger('cliente_id'); // SQLite friendly
            $table->date('data');
            $table->date('data_vencimento')->nullable();
            $table->decimal('total', 10, 2);
            $table->timestamps();
            
            // Adiciona foreign key manualmente para SQLite
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendas');
    }
};