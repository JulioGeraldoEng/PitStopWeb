<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Recebimento;
use App\Models\Produto;
use Illuminate\Support\Facades\Log;
use App\Helpers\WPPConnect;

class EnviarNotificacoesWhatsApp extends Command
{
    /**
     * O nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'whatsapp:notificar {--test : Modo teste, não envia mensagens reais}';

    /**
     * A descrição do comando.
     *
     * @var string
     */
    protected $description = 'Envia notificações de contas atrasadas e estoque baixo via WhatsApp';

    /**
     * Execute o comando.
     */
    public function handle()
    {
        $this->info('🔍 Iniciando envio de notificações WhatsApp...');
        
        // Modo teste? (não envia mensagens reais)
        $modoTeste = $this->option('test');
        
        if ($modoTeste) {
            $this->warn('⚠️ Modo TESTE ativado - Nenhuma mensagem será enviada!');
        }

        // Buscar todos os usuários com notificações WhatsApp ativas
        $usuarios = User::whereHas('settings', function($query) {
            $query->where('notificacoes_whatsapp', true);
        })->get();

        if ($usuarios->isEmpty()) {
            $this->warn('❌ Nenhum usuário com WhatsApp ativo.');
            return 0;
        }

        $this->info("📱 Usuários com WhatsApp ativo: " . $usuarios->count());
        
        $totalEnviadas = 0;

        foreach ($usuarios as $usuario) {
            $enviadas = $this->enviarParaUsuario($usuario, $modoTeste);
            $totalEnviadas += $enviadas;
        }

        $this->info("✅ Notificações processadas! Total enviadas: {$totalEnviadas}");
        
        return 0;
    }

    /**
     * Envia notificações para um usuário específico
     */
    private function enviarParaUsuario($usuario, $modoTeste = false)
    {
        $settings = $usuario->settings;
        
        // Buscar telefone do usuário
        $telefone = $this->getTelefoneUsuario($usuario);
        
        if (!$telefone) {
            $this->warn("❌ Usuário {$usuario->name} não tem telefone cadastrado");
            Log::warning("Usuário {$usuario->id} não tem telefone cadastrado");
            return 0;
        }

        $this->line("📱 Processando usuário: {$usuario->name} - {$telefone}");

        $mensagens = [];
        $totalMensagens = 0;

        // 1. CONTAS ATRASADAS (se ativado)
        if ($settings->notif_atrasados) {
            $atrasados = Recebimento::with('venda.cliente')
                ->where('status', 'atrasado')
                ->whereHas('venda', function($q) {
                    $q->whereNotNull('cliente_id');
                })
                ->get();

            if ($atrasados->isNotEmpty()) {
                $mensagem = $this->formatarContasAtrasadas($atrasados);
                $mensagens[] = $mensagem;
                $totalMensagens++;
                $this->line("   ⚠️ Contas atrasadas: " . $atrasados->count());
            }
        }

        // 2. CONTAS PENDENTES (se ativado)
        if ($settings->notif_pendentes) {
            $pendentes = Recebimento::with('venda.cliente')
                ->where('status', 'pendente')
                ->whereHas('venda', function($q) {
                    $q->whereNotNull('cliente_id');
                })
                ->get();

            if ($pendentes->isNotEmpty()) {
                $mensagem = $this->formatarContasPendentes($pendentes);
                $mensagens[] = $mensagem;
                $totalMensagens++;
                $this->line("   ⏳ Contas pendentes: " . $pendentes->count());
            }
        }

        // 3. ESTOQUE BAIXO (se ativado)
        if ($settings->notif_estoque_baixo) {
            $estoqueBaixo = Produto::where('quantidade', '<', 5)
                ->where('quantidade', '>', 0)
                ->get();

            if ($estoqueBaixo->isNotEmpty()) {
                $mensagem = $this->formatarEstoqueBaixo($estoqueBaixo);
                $mensagens[] = $mensagem;
                $totalMensagens++;
                $this->line("   📦 Estoque baixo: " . $estoqueBaixo->count() . " produtos");
            }
        }

        // 4. PRODUTOS ZERADOS (se ativado)
        if ($settings->notif_produto_zerado) {
            $zerados = Produto::where('quantidade', '=', 0)->get();

            if ($zerados->isNotEmpty()) {
                $mensagem = $this->formatarProdutosZerados($zerados);
                $mensagens[] = $mensagem;
                $totalMensagens++;
                $this->line("   ❌ Produtos zerados: " . $zerados->count());
            }
        }

        // Enviar mensagens (se houver)
        if ($totalMensagens > 0) {
            if (!$modoTeste) {
                $this->enviarWhatsApp($telefone, implode("\n\n---\n\n", $mensagens));
                $this->info("   ✅ {$totalMensagens} notificações enviadas para {$usuario->name}");
            } else {
                $this->line("   📝 [TESTE] {$totalMensagens} notificações preparadas para {$usuario->name}");
                $this->line("   Mensagem de exemplo:\n" . $mensagens[0]);
            }
        } else {
            $this->line("   ℹ️ Nenhuma notificação para {$usuario->name}");
        }

        return $totalMensagens;
    }

    /**
     * Obtém o telefone formatado do usuário
     */
    private function getTelefoneUsuario($usuario)
    {
        if (!$usuario->telefone) {
            return null;
        }

        // Remove tudo que não é número
        $telefone = preg_replace('/[^0-9]/', '', $usuario->telefone);
        
        // Adiciona código do Brasil se necessário
        if (strlen($telefone) === 10 || strlen($telefone) === 11) {
            return '55' . $telefone;
        }
        
        return $telefone;
    }

    /**
     * Formata mensagem de contas atrasadas
     */
    private function formatarContasAtrasadas($contas)
    {
        $msg = "⚠️ *CONTAS ATRASADAS*\n";
        $msg .= "📅 " . now()->format('d/m/Y') . "\n\n";
        
        $total = 0;
        foreach ($contas as $conta) {
            $cliente = $conta->venda->cliente->nome ?? 'Cliente não identificado';
            $msg .= "👤 *{$cliente}*\n";
            $msg .= "💰 R$ " . number_format($conta->valor_total, 2, ',', '.') . "\n";
            $msg .= "📅 Vencimento: " . date('d/m/Y', strtotime($conta->data_vencimento)) . "\n\n";
            $total += $conta->valor_total;
        }
        
        $msg .= "💵 *Total em atraso: R$ " . number_format($total, 2, ',', '.') . "*\n";
        $msg .= "🔗 Acesse o sistema para mais detalhes";
        
        return $msg;
    }

    /**
     * Formata mensagem de contas pendentes
     */
    private function formatarContasPendentes($contas)
    {
        $msg = "⏳ *CONTAS A VENCER*\n";
        $msg .= "📅 " . now()->format('d/m/Y') . "\n\n";
        
        foreach ($contas as $conta) {
            $cliente = $conta->venda->cliente->nome ?? 'Cliente não identificado';
            $dias = now()->diffInDays($conta->data_vencimento, false);
            
            $msg .= "👤 *{$cliente}*\n";
            $msg .= "💰 R$ " . number_format($conta->valor_total, 2, ',', '.') . "\n";
            $msg .= "📅 Vence em {$dias} dias\n\n";
        }
        
        return $msg;
    }

    /**
     * Formata mensagem de estoque baixo
     */
    private function formatarEstoqueBaixo($produtos)
    {
        $msg = "📦 *ESTOQUE BAIXO*\n";
        $msg .= "📅 " . now()->format('d/m/Y') . "\n\n";
        
        foreach ($produtos as $produto) {
            $msg .= "🔹 *{$produto->nome}*\n";
            $msg .= "📊 Quantidade: {$produto->quantidade} unidades\n\n";
        }
        
        $msg .= "🔗 Acesse o sistema para reabastecer";
        
        return $msg;
    }

    /**
     * Formata mensagem de produtos zerados
     */
    private function formatarProdutosZerados($produtos)
    {
        $msg = "❌ *PRODUTOS ESGOTADOS*\n";
        $msg .= "📅 " . now()->format('d/m/Y') . "\n\n";
        
        foreach ($produtos as $produto) {
            $msg .= "🔹 *{$produto->nome}*\n";
            $msg .= "⚠️ Estoque zerado\n\n";
        }
        
        $msg .= "🔗 Acesse o sistema para reabastecer";
        
        return $msg;
    }

    /**
     * Envia mensagem via WhatsApp (WPPConnect)
     */
    

    // ... no método enviarWhatsApp ...

    private function enviarWhatsApp($telefone, $mensagem)
    {
        try {
            $wpp = new WPPConnect();
            
            // Formata o telefone
            $telefoneFormatado = $wpp->formatPhone($telefone);
            
            // Verifica se está conectado antes de enviar
            if (!$wpp->isConnected()) {
                $this->warn("⚠️ WhatsApp não está conectado para {$telefone}");
                Log::warning("Tentativa de envio com WhatsApp desconectado", [
                    'telefone' => $telefone
                ]);
                return false;
            }
            
            // Envia a mensagem
            $resultado = $wpp->sendMessage($telefoneFormatado, $mensagem);
            
            if ($resultado) {
                $this->info("✅ Mensagem enviada para {$telefone}");
                return true;
            } else {
                $this->error("❌ Falha ao enviar para {$telefone}");
                return false;
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Erro: " . $e->getMessage());
            Log::error("Erro no comando whatsapp:notificar", [
                'erro' => $e->getMessage(),
                'telefone' => $telefone
            ]);
            return false;
        }
    }
}