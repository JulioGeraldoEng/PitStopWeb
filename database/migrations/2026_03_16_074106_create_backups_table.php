<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->increments('id'); // SQLite friendly
            
            $table->unsignedInteger('user_id'); // SQLite friendly
            $table->string('filename');
            $table->bigInteger('size')->nullable();
            $table->string('type', 20)->default('manual');
            $table->string('status', 20)->default('sucesso');
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Adiciona foreign key manualmente para SQLite
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Índices
            $table->index(['user_id', 'created_at']);
            $table->index('type');
            $table->index('status');
            $table->index(['user_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};