<?php
// database/migrations/xxxx_add_whatsapp_column_to_user_settings.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_settings', 'notificacoes_whatsapp')) {
                $table->boolean('notificacoes_whatsapp')->default(false)->after('notificacoes_sistema');
            }
            
            if (!Schema::hasColumn('user_settings', 'notif_atrasados')) {
                $table->boolean('notif_atrasados')->default(true);
            }
            
            if (!Schema::hasColumn('user_settings', 'notif_pendentes')) {
                $table->boolean('notif_pendentes')->default(false);
            }
            
            if (!Schema::hasColumn('user_settings', 'notif_estoque_baixo')) {
                $table->boolean('notif_estoque_baixo')->default(true);
            }
            
            if (!Schema::hasColumn('user_settings', 'notif_produto_zerado')) {
                $table->boolean('notif_produto_zerado')->default(true);
            }
            
            if (!Schema::hasColumn('user_settings', 'frequencia_whatsapp')) {
                $table->string('frequencia_whatsapp', 20)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn([
                'notificacoes_whatsapp',
                'notif_atrasados',
                'notif_pendentes',
                'notif_estoque_baixo',
                'notif_produto_zerado',
                'frequencia_whatsapp'
            ]);
        });
    }
};