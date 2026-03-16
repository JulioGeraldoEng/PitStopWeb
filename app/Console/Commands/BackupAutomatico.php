<?php
// app/Console/Commands/BackupAutomatico.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Controllers\PerfilController;
use Illuminate\Support\Facades\Auth; // <-- IMPORTANTE: ADICIONAR ESTA LINHA

class BackupAutomatico extends Command
{
    protected $signature = 'backup:automatico';
    protected $description = 'Executa backup automático para usuários com a opção ativada';

    public function handle()
    {
        $this->info('🔍 Iniciando backups automáticos...');
        
        $usuarios = User::whereHas('settings', function($q) {
            $q->where('backup_automatico', true);
        })->get();

        if ($usuarios->isEmpty()) {
            $this->warn('❌ Nenhum usuário com backup automático ativo.');
            return 0;
        }

        $this->info("📦 Usuários com backup ativo: " . $usuarios->count());
        
        $controller = new PerfilController();
        $sucessos = 0;
        $falhas = 0;

        foreach ($usuarios as $usuario) {
            $this->line("🔄 Processando usuário: {$usuario->name}");
            
            try {
                // Logar como usuário para o backup
                Auth::login($usuario);
                
                // Executar backup
                $resultado = $controller->backupManual();
                
                // Verificar se o backup foi bem sucedido
                if ($resultado->getStatusCode() === 200) {
                    $sucessos++;
                    $this->info("   ✅ Backup concluído para {$usuario->name}");
                } else {
                    $falhas++;
                    $this->error("   ❌ Falha no backup para {$usuario->name}");
                }
                
                // Deslogar
                Auth::logout();
                
            } catch (\Exception $e) {
                $falhas++;
                $this->error("   ❌ Erro: " . $e->getMessage());
                Auth::logout();
            }
        }

        $this->info("✅ Backups finalizados! Sucessos: {$sucessos}, Falhas: {$falhas}");
        
        return 0;
    }
}