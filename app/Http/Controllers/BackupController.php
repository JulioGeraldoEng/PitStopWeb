<?php
// app/Http/Controllers/BackupController.php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    /**
     * Display a listing of backups.
     */
    public function index()
    {
        $backups = Backup::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return response()->json([
            'success' => true,
            'data' => $backups
        ]);
    }

    /**
     * Generate a new backup.
     */
    public function gerar(Request $request)
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
            
            // Obter todas as tabelas do banco
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
            
            // Cabeçalho do backup
            $sql = "-- ===================================================\n";
            $sql .= "-- BACKUP DO BANCO DE DADOS - PITSTOP\n";
            $sql .= "-- ===================================================\n";
            $sql .= "-- Data: " . now()->format('d/m/Y H:i:s') . "\n";
            $sql .= "-- Usuário: " . $user->name . " (ID: " . $user->id . ")\n";
            $sql .= "-- Banco: SQLite\n";
            $sql .= "-- ===================================================\n\n";
            
            $sql .= "BEGIN TRANSACTION;\n\n";
            
            foreach ($tables as $table) {
                $tableName = $table->name;
                
                // Pular tabelas do sistema
                if (in_array($tableName, ['migrations', 'failed_jobs', 'password_reset_tokens'])) {
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
                
                if ($rows->count() > 0) {
                    $sql .= "-- Inserindo " . $rows->count() . " registros\n";
                    
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
            
            // Registrar backup no banco
            $backup = Backup::create([
                'user_id' => $user->id,
                'filename' => $filename,
                'size' => filesize($path),
                'type' => 'manual',
                'status' => 'sucesso'
            ]);
            
            return response()->download($path, $filename);
            
        } catch (\Exception $e) {
            Log::error('Erro no backup manual: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a specific backup.
     */
    public function download($id)
    {
        try {
            $backup = Backup::where('user_id', Auth::id())
                            ->where('id', $id)
                            ->firstOrFail();
            
            $path = storage_path("app/backups/{$backup->filename}");
            
            if (!file_exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo de backup não encontrado'
                ], 404);
            }
            
            return response()->download($path, $backup->filename);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao baixar backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import a backup file.
     */
    public function importar(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql|max:102400'
        ]);

        try {
            $file = $request->file('backup_file');
            
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = $tempDir . DIRECTORY_SEPARATOR . $filename;
            
            $file->move($tempDir, $filename);
            
            // Ler o arquivo SQL
            $sql = file_get_contents($filePath);
            
            // Backup de segurança antes de importar
            $this->backupSeguranca();
            
            // Desabilitar foreign keys
            DB::statement('PRAGMA foreign_keys = OFF');
            
            // Executar comandos
            $commands = explode(';', $sql);
            $successCount = 0;
            
            foreach ($commands as $command) {
                $command = trim($command);
                if (!empty($command)) {
                    try {
                        DB::statement($command);
                        $successCount++;
                    } catch (\Exception $e) {
                        Log::warning('Erro ao executar comando: ' . substr($command, 0, 100));
                    }
                }
            }
            
            DB::statement('PRAGMA foreign_keys = ON');
            
            // Limpar temporários
            Storage::deleteDirectory('temp');
            
            // Registrar importação
            Backup::create([
                'user_id' => Auth::id(),
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => 'importacao',
                'status' => 'sucesso'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Backup importado com sucesso! ({$successCount} comandos executados)"
            ]);
            
        } catch (\Exception $e) {
            Storage::deleteDirectory('temp');
            DB::statement('PRAGMA foreign_keys = ON');
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a specific backup.
     */
    public function restaurar($id)
    {
        try {
            $backup = Backup::where('user_id', Auth::id())
                            ->where('id', $id)
                            ->firstOrFail();
            
            $path = storage_path("app/backups/{$backup->filename}");
            
            if (!file_exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo de backup não encontrado'
                ], 404);
            }

            // Backup de segurança antes de restaurar
            $this->backupSeguranca();
            
            $sql = file_get_contents($path);
            
            DB::statement('PRAGMA foreign_keys = OFF');
            
            // Limpar banco atual
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($tables as $table) {
                DB::statement("DROP TABLE IF EXISTS \"{$table->name}\"");
            }
            
            // Executar comandos
            $commands = explode(';', $sql);
            foreach ($commands as $command) {
                $command = trim($command);
                if (!empty($command)) {
                    try {
                        DB::statement($command);
                    } catch (\Exception $e) {
                        Log::warning('Erro: ' . substr($command, 0, 100));
                    }
                }
            }
            
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
     * Delete a specific backup.
     */
    public function excluir($id)
    {
        try {
            $backup = Backup::where('user_id', Auth::id())
                            ->where('id', $id)
                            ->firstOrFail();
            
            $path = storage_path("app/backups/{$backup->filename}");
            
            if (file_exists($path)) {
                unlink($path);
            }
            
            $backup->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Backup excluído com sucesso!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a security backup before import/restore.
     */
    private function backupSeguranca()
    {
        try {
            $user = Auth::user();
            
            if (!file_exists(storage_path('app/backups/seguranca'))) {
                mkdir(storage_path('app/backups/seguranca'), 0755, true);
            }
            
            $filename = "pre_import_backup_{$user->id}_" . now()->format('Y-m-d_H-i-s') . ".sqlite";
            $path = storage_path("app/backups/seguranca/{$filename}");
            
            // Copiar o arquivo SQLite atual
            $databasePath = config('database.connections.sqlite.database');
            
            if (file_exists($databasePath)) {
                copy($databasePath, $path);
                
                Backup::create([
                    'user_id' => $user->id,
                    'filename' => $filename,
                    'size' => filesize($path),
                    'type' => 'seguranca',
                    'status' => 'sucesso'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro no backup de segurança: ' . $e->getMessage());
        }
    }
}