<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itens_venda', function (Blueprint $table) {
            $table->increments('id'); // SQLite friendly
            $table->unsignedInteger('venda_id'); // SQLite friendly
            $table->unsignedInteger('produto_id'); // SQLite friendly
            $table->string('nome_produto');
            $table->integer('quantidade');
            $table->decimal('preco_unitario', 10, 2);
            $table->timestamps();
            
            // Adiciona foreign keys manualmente para SQLite
            $table->foreign('venda_id')->references('id')->on('vendas')->onDelete('cascade');
            $table->foreign('produto_id')->references('id')->on('produtos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens_venda');
    }
};