<?php
// app/Http/Controllers/PerfilController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\UserSetting;
use App\Models\Backup;

class PerfilController extends Controller
{
    // ==================== PERFIL ====================
    public function edit()
    {
        return view('perfil.index');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'telefone' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'telefone' => $request->telefone,
        ]);

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Senha atual incorreta']);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Senha alterada com sucesso!');
    }

    // ==================== CONFIGURAÇÕES ====================
    public function configuracoes()
    {
        $user = Auth::user();
        $settings = UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'tema' => 'claro',
                'idioma' => 'pt-BR',
                'notificacoes_email' => true,
                'notificacoes_sistema' => true,
                'notificacoes_whatsapp' => false,
                'notif_atrasados' => true,
                'notif_pendentes' => false,
                'notif_estoque_baixo' => true,
                'notif_produto_zerado' => true,
                'frequencia_whatsapp' => 'diario',
                'backup_automatico' => true,
            ]
        );

        return view('perfil.configuracoes', compact('settings'));
    }

    public function updateConfiguracoes(Request $request)
    {
        $user = Auth::user();

        // Validação simples
        $validated = $request->validate([
            'tema' => 'required|in:claro,escuro,auto',
            'idioma' => 'required|in:pt-BR,en,es',
        ]);

        // Preparar dados para salvar
        $settings = [
            'tema' => $request->tema,
            'idioma' => $request->idioma,
            
            // Canais - usa has() para verificar se o checkbox foi marcado
            'notificacoes_email' => $request->has('notificacoes_email'),
            'notificacoes_sistema' => $request->has('notificacoes_sistema'),
            'notificacoes_whatsapp' => $request->has('notificacoes_whatsapp'),
            
            // Tipos
            'notif_atrasados' => $request->has('notif_atrasados'),
            'notif_pendentes' => $request->has('notif_pendentes'),
            'notif_estoque_baixo' => $request->has('notif_estoque_baixo'),
            'notif_produto_zerado' => $request->has('notif_produto_zerado'),
            
            // Frequência
            'frequencia_whatsapp' => $request->has('notificacoes_whatsapp') ? 
                                    ($request->frequencia_whatsapp ?? 'diario') : null,
            
            'backup_automatico' => $request->has('backup_automatico'),
        ];

        // Salvar
        UserSetting::updateOrCreate(
            ['user_id' => $user->id],
            $settings
        );

        return back()->with('success', 'Configurações salvas com sucesso!');
    }

    // ==================== MÉTODO PARA SALVAR TEMA VIA AJAX ====================
    /**
     * Salvar tema via AJAX
     */
    public function salvarTema(Request $request)
    {
        $request->validate([
            'tema' => 'required|in:claro,escuro,auto'
        ]);

        $user = Auth::user();
        
        // Atualizar configurações
        $settings = UserSetting::updateOrCreate(
            ['user_id' => $user->id],
            ['tema' => $request->tema]
        );

        // Salvar na sessão para uso imediato
        session(['tema' => $request->tema]);

        return response()->json([
            'success' => true,
            'tema' => $request->tema,
            'message' => 'Tema atualizado com sucesso!'
        ]);
    }

    // ==================== BACKUP (VERSÃO SQLITE) ====================
    
    /**
     * Fazer backup manual (versão SQLite)
     */
    public function backupManual()
    {
        try {
            $user = Auth::user();
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "backup_{$user->id}_{$timestamp}.sql";
            
            // Criar diretório de backups se não existir
            if (!file_exists(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }
            
            $path = storage_path("app/backups/{$filename}");
            
            // Obter todas as tabelas do SQLite
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
            
            // Cabeçalho do backup
            $sql = "-- ===================================================\n";
            $sql .= "-- BACKUP DO BANCO DE DADOS - PITSTOP (SQLite)\n";
            $sql .= "-- ===================================================\n";
            $sql .= "-- Data: " . now()->format('d/m/Y H:i:s') . "\n";
            $sql .= "-- Usuário: " . $user->name . " (ID: " . $user->id . ")\n";
            $sql .= "-- Banco: SQLite\n";
            $sql .= "-- ===================================================\n\n";
            
            $sql .= "BEGIN TRANSACTION;\n\n";
            
            foreach ($tables as $table) {
                $tableName = $table->name;
                
                // Pular tabelas do sistema
                if (in_array($tableName, ['migrations', 'failed_jobs', 'password_resets', 'personal_access_tokens'])) {
                    continue;
                }
                
                $sql .= "-- ===================================================\n";
                $sql .= "-- Tabela: {$tableName}\n";
                $sql .= "-- ===================================================\n";
                
                // Obter schema da tabela
                $schema = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$tableName]);
                
                if (!empty($schema)) {
                    $createSQL = $schema[0]->sql;
                    $sql .= "DROP TABLE IF EXISTS \"{$tableName}\";\n";
                    $sql .= $createSQL . ";\n\n";
                }
                
                // Buscar dados da tabela
                $rows = DB::table($tableName)->get();
                
                if (count($rows) > 0) {
                    $sql .= "-- Inserindo " . count($rows) . " registros\n";
                    
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $columns = array_keys($rowArray);
                        $values = array_values($rowArray);
                        
                        $values = array_map(function($value) {
                            if (is_null($value)) return 'NULL';
                            if (is_numeric($value)) return $value;
                            if (is_bool($value)) return $value ? '1' : '0';
                            
                            $value = str_replace("'", "''", $value);
                            return "'" . $value . "'";
                        }, $values);
                        
                        $sql .= "INSERT INTO \"{$tableName}\" (\"" . implode('", "', $columns) . "\") VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }
            
            $sql .= "COMMIT;\n";
            
            // Salvar arquivo
            file_put_contents($path, $sql);
            
            // Verificar se o arquivo foi criado
            if (!file_exists($path)) {
                throw new \Exception('Erro ao criar arquivo de backup');
            }
            
            // Registrar backup no banco ANTES de enviar o download
            $backup = Backup::create([
                'user_id' => $user->id,
                'filename' => $filename,
                'size' => filesize($path),
                'type' => 'manual',
                'status' => 'sucesso'
            ]);
            
            // Log para debug
            \Log::info('Backup criado:', [
                'id' => $backup->id,
                'filename' => $filename,
                'path' => $path
            ]);
            
            // Retornar o download SEM deletar o arquivo
            return response()->download($path, $filename);
            
        } catch (\Exception $e) {
            \Log::error('Erro no backup manual: ' . $e->getMessage());
            return back()->with('error', 'Erro ao fazer backup: ' . $e->getMessage());
        }
    }

    /**
     * Importar dados de backup (versão SQLite - CORRIGIDA)
     */
    public function importarBackup(Request $request)
    {
        // Validação mais permissiva
        $request->validate([
            'backup_file' => 'required|file|max:102400' // 100MB max
        ]);

        try {
            $file = $request->file('backup_file');
            
            // Criar diretório temp se não existir
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            // Gerar nome único para evitar conflitos
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = $tempDir . DIRECTORY_SEPARATOR . $filename;
            
            // Mover o arquivo diretamente
            $file->move($tempDir, $filename);
            
            \Log::info('Arquivo salvo em: ' . $filePath);
            
            // Verificar se o arquivo existe
            if (!file_exists($filePath)) {
                throw new \Exception('Arquivo não encontrado após upload: ' . $filePath);
            }

            // Verificar extensão
            $extension = strtolower($file->getClientOriginalExtension());
            
            $sqlFile = $filePath;
            
            // Se for ZIP, extrair
            if ($extension == 'zip') {
                $zip = new \ZipArchive;
                if ($zip->open($filePath) === TRUE) {
                    $zip->extractTo($tempDir);
                    $zip->close();
                    
                    // Encontrar o arquivo .sql extraído
                    $sqlFiles = glob($tempDir . '/*.sql');
                    if (empty($sqlFiles)) {
                        throw new \Exception('Nenhum arquivo SQL encontrado dentro do ZIP');
                    }
                    $sqlFile = $sqlFiles[0];
                } else {
                    throw new \Exception('Erro ao abrir arquivo ZIP');
                }
            }

            // Backup antes de importar (segurança)
            $this->backupAntesImportar();
            
            // Ler o arquivo SQL
            $sql = file_get_contents($sqlFile);
            if ($sql === false) {
                throw new \Exception('Erro ao ler arquivo SQL');
            }
            
            // Desabilitar foreign keys temporariamente
            DB::statement('PRAGMA foreign_keys = OFF');
            
            // Executar importação (dividir em comandos individuais)
            $commands = explode(';', $sql);
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($commands as $command) {
                $command = trim($command);
                if (!empty($command)) {
                    try {
                        DB::statement($command);
                        $successCount++;
                    } catch (\Exception $e) {
                        $errorCount++;
                        \Log::warning('Erro ao executar comando: ' . substr($command, 0, 100) . '... - ' . $e->getMessage());
                    }
                }
            }
            
            // Reabilitar foreign keys
            DB::statement('PRAGMA foreign_keys = ON');
            
            // Limpar arquivos temporários
            $this->cleanTempDirectory($tempDir);
            
            // Registrar importação
            Backup::create([
                'user_id' => Auth::id(),
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => 'importacao',
                'status' => 'sucesso'
            ]);
            
            $message = "Dados importados com sucesso! Comandos executados: {$successCount}";
            if ($errorCount > 0) {
                $message .= " (ignorados: {$errorCount})";
            }
            
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            // Limpar arquivos temporários em caso de erro
            if (isset($tempDir)) {
                $this->cleanTempDirectory($tempDir);
            }
            
            // Reabilitar foreign keys
            DB::statement('PRAGMA foreign_keys = ON');
            
            // Registrar erro
            try {
                Backup::create([
                    'user_id' => Auth::id(),
                    'filename' => $request->file('backup_file')->getClientOriginalName(),
                    'size' => $request->file('backup_file')->getSize(),
                    'type' => 'importacao',
                    'status' => 'erro',
                    'error_message' => $e->getMessage()
                ]);
            } catch (\Exception $logError) {
                \Log::error('Erro ao registrar falha: ' . $logError->getMessage());
            }
            
            return back()->with('error', 'Erro ao importar: ' . $e->getMessage());
        }
    }

    /**
     * Limpar diretório temporário
     */
    private function cleanTempDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }
        
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($dir);
    }

    /**
     * Listar backups disponíveis
     */
    public function listarBackups()
    {
        $backups = Backup::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(20) // Aumentei para 20 para mostrar mais
            ->get()
            ->map(function($backup) {
                // Definir cor e ícone baseado no tipo
                $cor = 'secondary';
                $icone = 'fa-database';
                
                switch ($backup->type) {
                    case 'manual':
                        $cor = 'primary';
                        $icone = 'fa-download';
                        $tipoTexto = 'Manual';
                        break;
                    case 'automatico':
                        $cor = 'success';
                        $icone = 'fa-clock';
                        $tipoTexto = 'Automático';
                        break;
                    case 'seguranca':
                        $cor = 'warning';
                        $icone = 'fa-shield-alt';
                        $tipoTexto = 'Segurança';
                        break;
                    case 'importacao':
                        $cor = 'info';
                        $icone = 'fa-upload';
                        $tipoTexto = 'Importado';
                        break;
                    default:
                        $tipoTexto = ucfirst($backup->type);
                }
                
                return [
                    'id' => $backup->id,
                    'filename' => $backup->filename,
                    'size_formatted' => $backup->size_formatted,
                    'type' => $backup->type,
                    'type_text' => $tipoTexto,
                    'type_color' => $cor,
                    'type_icon' => $icone,
                    'status' => $backup->status,
                    'created_at' => $backup->created_at->format('d/m/Y H:i:s'),
                    'file_exists' => $backup->fileExists()
                ];
            });
            
        return response()->json($backups);
    }

    /**
     * Backup de segurança antes de importar (versão SQLite)
     */
    private function backupAntesImportar()
    {
        try {
            $user = Auth::user();
            
            // Criar diretório de segurança se não existir
            if (!file_exists(storage_path('app/backups/seguranca'))) {
                mkdir(storage_path('app/backups/seguranca'), 0755, true);
            }
            
            $filename = "pre_import_backup_{$user->id}_" . now()->format('Y-m-d_H-i-s') . ".sqlite";
            $path = storage_path("app/backups/seguranca/{$filename}");
            
            // Copiar o arquivo SQLite atual
            $databasePath = config('database.connections.sqlite.database');
            
            if (file_exists($databasePath)) {
                copy($databasePath, $path);
                
                // Registrar backup de segurança
                Backup::create([
                    'user_id' => $user->id,
                    'filename' => $filename,
                    'size' => filesize($path),
                    'type' => 'seguranca',
                    'status' => 'sucesso'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Erro no backup de segurança: ' . $e->getMessage());
        }
    }

    // ==================== MÉTODOS ADICIONAIS ====================

    /**
     * Download de backup específico
     */
    public function downloadBackup($filename)
    {
        try {
            $user = Auth::user();
            
            // Verificar se o backup pertence ao usuário
            $backup = Backup::where('user_id', $user->id)
                            ->where('filename', $filename)
                            ->firstOrFail();
            
            $path = storage_path("app/backups/{$filename}");
            
            if (!file_exists($path)) {
                throw new \Exception('Arquivo não encontrado');
            }
            
            return response()->download($path, $filename);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao baixar backup: ' . $e->getMessage());
        }
    }

    /**
     * Restaurar backup específico (versão SQLite)
     */
    public function restoreBackup($filename)
    {
        try {
            $user = Auth::user();
            
            // Verificar se o backup pertence ao usuário
            $backup = Backup::where('user_id', $user->id)
                            ->where('filename', $filename)
                            ->firstOrFail();
            
            $path = storage_path("app/backups/{$filename}");
            
            if (!file_exists($path)) {
                throw new \Exception('Arquivo de backup não encontrado');
            }

            // Backup de segurança antes de restaurar
            $this->backupAntesImportar();
            
            // Ler o arquivo SQL
            $sql = file_get_contents($path);
            
            // Desabilitar foreign keys
            DB::statement('PRAGMA foreign_keys = OFF');
            
            // Limpar banco atual (remover todas as tabelas)
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($tables as $table) {
                DB::statement("DROP TABLE IF EXISTS \"{$table->name}\"");
            }
            
            // Executar importação
            $commands = explode(';', $sql);
            foreach ($commands as $command) {
                $command = trim($command);
                if (!empty($command)) {
                    try {
                        DB::statement($command);
                    } catch (\Exception $e) {
                        // Ignorar erros de comandos individuais
                        \Log::warning('Erro ao executar comando: ' . $command);
                    }
                }
            }
            
            // Reabilitar foreign keys
            DB::statement('PRAGMA foreign_keys = ON');
            
            return response()->json([
                'success' => true,
                'message' => 'Backup restaurado com sucesso!'
            ]);
            
        } catch (\Exception $e) {
            DB::statement('PRAGMA foreign_keys = ON');
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Encerrar todas as sessões
     */
    public function encerrarSessoes(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Encerrar todas as sessões exceto a atual
            if (method_exists($user, 'tokens')) {
                $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id ?? 0)->delete();
            }
            
            // Se estiver usando session do Laravel
            Auth::logoutOtherDevices($request->password ?? '');
            
            return response()->json([
                'success' => true,
                'message' => 'Sessões encerradas com sucesso!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao encerrar sessões: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar dados do usuário
     */
    public function exportarDados()
    {
        try {
            $user = Auth::user();
            
            $dados = [
                'usuario' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'telefone' => $user->telefone,
                    'created_at' => $user->created_at->format('d/m/Y H:i:s'),
                ],
                'configuracoes' => UserSetting::where('user_id', $user->id)->first() ?? [],
                'backups' => Backup::where('user_id', $user->id)->get()->map(function($backup) {
                    return [
                        'filename' => $backup->filename,
                        'size' => $backup->size_formatted,
                        'type' => $backup->type,
                        'status' => $backup->status,
                        'created_at' => $backup->created_at->format('d/m/Y H:i:s')
                    ];
                }),
                'data_exportacao' => now()->format('d/m/Y H:i:s')
            ];
            
            $filename = 'export_dados_' . $user->id . '_' . now()->format('Y-m-d_H-i-s') . '.json';
            
            // Criar diretório se não existir
            if (!file_exists(storage_path('app/exports'))) {
                mkdir(storage_path('app/exports'), 0755, true);
            }
            
            $path = storage_path("app/exports/{$filename}");
            
            file_put_contents($path, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            return response()->download($path, $filename)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao exportar dados: ' . $e->getMessage());
        }
    }

    /**
     * Excluir conta permanentemente
     */
    public function excluirConta(Request $request)
    {
        try {
            $user = Auth::user();
            $userId = $user->id;
            
            // Backup final antes de excluir
            $this->backupAntesImportar();
            
            // Remover configurações
            UserSetting::where('user_id', $userId)->delete();
            
            // Remover backups (os arquivos serão deletados pelo boot do model)
            Backup::where('user_id', $userId)->delete();
            
            // Logout
            Auth::logout();
            
            // Excluir usuário
            $user->delete();
            
            // Invalidar sessão
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return response()->json([
                'success' => true,
                'message' => 'Conta excluída com sucesso!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir conta: ' . $e->getMessage()
            ], 500);
        }
    }
}