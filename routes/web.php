<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\RecebimentoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\SobreController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\BackupController; // Opcional
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Helpers\WPPConnect;

// ==================== PÁGINA INICIAL ====================
Route::get('/', function () {
    return redirect('/dashboard');
});

// ==================== ROTAS ADMINISTRATIVAS ====================
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard Admin
    Route::get('/', function () {
        // Verificação manual de admin
        if (auth()->user()->tipo !== 'admin') {
            abort(403, 'Acesso negado! Apenas administradores podem acessar esta área.');
        }
        return view('admin.dashboard');
    })->name('dashboard');
    
    // Gerenciamento de usuários
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
});

// ==================== ROTAS QUE PRECISAM DE AUTENTICAÇÃO ====================
Route::middleware('auth')->group(function () {
    
    // ========== DASHBOARD ==========
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // ========== PERFIL (BREEZE) ==========
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // ========== PERFIL PERSONALIZADO ==========
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/', [PerfilController::class, 'edit'])->name('edit');
        Route::put('/', [PerfilController::class, 'update'])->name('update');
        Route::put('/senha', [PerfilController::class, 'updatePassword'])->name('password');
    });
    
    // ========== CONFIGURAÇÕES COM BACKUP ==========
    Route::prefix('configuracoes')->name('configuracoes.')->group(function () {
        // Página principal
        Route::get('/', [PerfilController::class, 'configuracoes'])->name('index');
        Route::put('/', [PerfilController::class, 'updateConfiguracoes'])->name('update');
        
        // ===== ROTA PARA SALVAR TEMA =====
        Route::post('/tema', [PerfilController::class, 'salvarTema'])->name('tema');
        
        // ===== ROTAS DE BACKUP =====
        Route::post('/backup-manual', [PerfilController::class, 'backupManual'])->name('backup-manual');
        Route::post('/importar-backup', [PerfilController::class, 'importarBackup'])->name('importar-backup');
        Route::get('/listar-backups', [PerfilController::class, 'listarBackups'])->name('listar-backups');
        Route::get('/download/{filename}', [PerfilController::class, 'downloadBackup'])->name('download-backup');
        Route::post('/restore/{filename}', [PerfilController::class, 'restoreBackup'])->name('restore-backup');
        
        // ===== ROTAS DE SEGURANÇA =====
        Route::post('/encerrar-sessoes', [PerfilController::class, 'encerrarSessoes'])->name('encerrar-sessoes');
        Route::get('/exportar-dados', [PerfilController::class, 'exportarDados'])->name('exportar-dados');
        Route::delete('/excluir-conta', [PerfilController::class, 'excluirConta'])->name('excluir-conta');
    });
    
    // ========== RECURSOS DO SISTEMA (CRUDs) ==========
    Route::resource('clientes', ClienteController::class);
    Route::resource('produtos', ProdutoController::class);
    Route::resource('vendas', VendaController::class);
    Route::resource('recebimentos', RecebimentoController::class)->only(['index', 'edit', 'update']);
    
    // ========== RELATÓRIOS ==========
    Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
    Route::post('/relatorios/exportar-pdf', [RelatorioController::class, 'exportarPDF'])->name('relatorios.exportar-pdf');
    Route::post('/relatorios/compartilhar-whatsapp', [RelatorioController::class, 'compartilharWhatsApp'])->name('relatorios.whatsapp');
    
    // ========== WHATSAPP ==========
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/', [WhatsAppController::class, 'index'])->name('index');
        Route::post('/conectar', [WhatsAppController::class, 'conectar'])->name('conectar');
        Route::post('/desconectar', [WhatsAppController::class, 'desconectar'])->name('desconectar');
        Route::post('/reiniciar', [WhatsAppController::class, 'reiniciar'])->name('reiniciar');
        Route::post('/enviar-atrasados', [WhatsAppController::class, 'enviarAtrasados'])->name('enviar-atrasados');
        Route::get('/qrcode', [WhatsAppController::class, 'getQRCode'])->name('qrcode');
        Route::get('/status', [WhatsAppController::class, 'getStatus'])->name('status');
    });
    
    // ========== SOBRE ==========
    Route::get('/sobre', function () {
        return view('sobre.index');
    })->name('sobre.index');
    
    // ========== IDIOMAS ==========
    Route::get('/language/{locale}', function ($locale) {
        if (!in_array($locale, ['pt-BR', 'en', 'es'])) {
            abort(400);
        }
        
        session(['locale' => $locale]);
        app()->setLocale($locale);
        
        return back();
    })->name('language.switch');
});

// ==================== ROTAS DE BACKUP ALTERNATIVAS (OPCIONAL) ====================
Route::middleware('auth')->prefix('backups')->name('backups.')->group(function () {
    Route::get('/', [BackupController::class, 'index'])->name('index');
    Route::post('/gerar', [BackupController::class, 'gerar'])->name('gerar');
    Route::post('/importar', [BackupController::class, 'importar'])->name('importar');
    Route::get('/download/{id}', [BackupController::class, 'download'])->name('download');
    Route::post('/restaurar/{id}', [BackupController::class, 'restaurar'])->name('restaurar');
    Route::delete('/excluir/{id}', [BackupController::class, 'excluir'])->name('excluir');
});

// ==================== ROTA DE TESTE DE NOTIFICAÇÃO ====================
Route::post('/testar-notificacao', function(Request $request) {
    try {
        $tipo = $request->tipo;
        $user = Auth::user();
        $wpp = new WPPConnect();
        
        if (!$user->telefone) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Telefone não cadastrado'
            ]);
        }

        // Gerar mensagem de teste baseada no tipo
        switch ($tipo) {
            case 'atrasados':
                $mensagem = "🔔 *TESTE - CONTAS ATRASADAS*\n\n";
                $mensagem .= "Esta é uma mensagem de teste.\n";
                $mensagem .= "As contas atrasadas seriam listadas aqui.\n\n";
                $mensagem .= "📱 Sistema PitStop";
                break;
                
            case 'pendentes':
                $mensagem = "🔔 *TESTE - CONTAS PENDENTES*\n\n";
                $mensagem .= "Esta é uma mensagem de teste.\n";
                $mensagem .= "As contas pendentes seriam listadas aqui.\n\n";
                $mensagem .= "📱 Sistema PitStop";
                break;
                
            case 'estoque':
                $mensagem = "🔔 *TESTE - ESTOQUE BAIXO*\n\n";
                $mensagem .= "Esta é uma mensagem de teste.\n";
                $mensagem .= "Os produtos com estoque baixo seriam listados aqui.\n\n";
                $mensagem .= "📱 Sistema PitStop";
                break;
                
            case 'zerados':
                $mensagem = "🔔 *TESTE - PRODUTOS ZERADOS*\n\n";
                $mensagem .= "Esta é uma mensagem de teste.\n";
                $mensagem .= "Os produtos esgotados seriam listados aqui.\n\n";
                $mensagem .= "📱 Sistema PitStop";
                break;
        }

        // Adicionar informações de debug
        $mensagem .= "\n\n📊 Debug:\n";
        $mensagem .= "Usuário: {$user->name}\n";
        $mensagem .= "Telefone: {$user->telefone}\n";
        $mensagem .= "Data: " . now()->format('d/m/Y H:i:s');

        // Enviar mensagem
        $telefoneFormatado = $wpp->formatPhone($user->telefone);
        $resultado = $wpp->sendMessage($telefoneFormatado, $mensagem);

        if ($resultado) {
            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Teste enviado com sucesso'
            ]);
        } else {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Falha ao enviar mensagem'
            ]);
        }

    } catch (\Exception $e) {
        return response()->json([
            'sucesso' => false,
            'mensagem' => $e->getMessage()
        ], 500);
    }
})->middleware('auth');

require __DIR__.'/auth.php';