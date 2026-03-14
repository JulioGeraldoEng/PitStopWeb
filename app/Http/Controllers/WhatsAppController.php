<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    private $wppConnectUrl = 'http://localhost:21465';
    private $sessionName = 'pitstop';
    private $apiToken = '$2b$10$MLzGkt7JNtFj7fwlZ8aLY.2mR32JFGWu6DnBlHpFIJwykoMBS6RF6'; // SEU TOKEN AQUI

    public function index()
    {
        return view('whatsapp.index');
    }

    public function conectar()
    {
        try {
            Log::info('Tentando conectar ao WhatsApp...');
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->wppConnectUrl . '/api/' . $this->sessionName . '/start-session', []);
            
            Log::info('Resposta do WPPConnect:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conectando ao WhatsApp...'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro: ' . $response->body()
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Erro ao conectar WhatsApp: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao conectar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reiniciar()
    {
        try {
            Log::info('🔄 Reiniciando sessão...');

            // 1️⃣ Primeiro, fechar a sessão atual
            $closeResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json'
            ])->post($this->wppConnectUrl . '/api/' . $this->sessionName . '/close-session');

            Log::info('Resposta do close-session:', [
                'status' => $closeResponse->status(),
                'body' => $closeResponse->body()
            ]);

            // Aguardar 1 segundo para garantir que fechou
            sleep(1);

            // 2️⃣ Depois, iniciar novamente
            $startResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json'
            ])->post($this->wppConnectUrl . '/api/' . $this->sessionName . '/start-session');

            Log::info('Resposta do start-session:', [
                'status' => $startResponse->status(),
                'body' => $startResponse->body()
            ]);

            if ($startResponse->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sessão reiniciada com sucesso! Novo QR Code gerado.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao iniciar sessão: ' . $startResponse->body()
                ], $startResponse->status());
            }

        } catch (\Exception $e) {
            Log::error('❌ Erro ao reiniciar sessão: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao reiniciar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function status()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json'
            ])->get($this->wppConnectUrl . '/api/' . $this->sessionName . '/status');

            if ($response->successful()) {
                $data = $response->json();
                
                $connected = isset($data['status']) && $data['status'] === 'CONNECTED';
                
                return response()->json([
                    'connected' => $connected,
                    'message' => $connected ? 'Conectado' : 'Desconectado'
                ]);
            } else {
                return response()->json([
                    'connected' => false,
                    'message' => 'Sessão não encontrada'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'connected' => false,
                'message' => 'Erro ao verificar status'
            ], 500);
        }
    }

    // ===================== FUNÇÕES AUXILIARES =====================
    
    public function enviarAtrasados(Request $request)
    {
        try {
            // Buscar clientes com vendas atrasadas
            $clientesAtrasados = $this->getClientesAtrasados();
            
            if (empty($clientesAtrasados)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Nenhum cliente com vendas atrasadas encontrado.'
                ]);
            }

            $resultados = [];
            $enviados = 0;
            $erros = 0;

            foreach ($clientesAtrasados as $cliente) {
                // Construir mensagem personalizada
                $mensagem = "Olá {$cliente['nome']}, tudo bem?\n\n";
                $mensagem .= "Identificamos que você tem as seguintes vendas em atraso:\n\n";
                
                foreach ($cliente['vendas'] as $venda) {
                    $mensagem .= "🔹 Venda #{$venda['id']}\n";
                    $mensagem .= "   📅 Data: {$venda['data']}\n";
                    $mensagem .= "   ⏰ Vencimento: {$venda['vencimento']}\n";
                    $mensagem .= "   💰 Total: R$ {$venda['total']}\n";
                    $mensagem .= "   📦 Produtos:\n{$venda['produtos']}\n\n";
                }
                
                $mensagem .= "Por favor, regularize sua situação o mais breve possível.\n";
                $mensagem .= "Se já realizou o pagamento, desconsidere esta mensagem.\n\n";
                $mensagem .= "Atenciosamente,\nPitStop";

                // Enviar mensagem
                $resultado = $this->enviarMensagemWhatsApp($cliente['telefone'], $mensagem);
                
                if ($resultado['success']) {
                    $enviados++;
                    $resultados[] = [
                        'cliente' => $cliente['nome'],
                        'telefone' => $cliente['telefone'],
                        'status' => 'sucesso'
                    ];
                } else {
                    $erros++;
                    $resultados[] = [
                        'cliente' => $cliente['nome'],
                        'telefone' => $cliente['telefone'],
                        'status' => 'erro',
                        'erro' => $resultado['error'] ?? 'Erro desconhecido'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Mensagens processadas. Enviadas: $enviados, Erros: $erros",
                'detalhes' => $resultados
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagens atrasadas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Busca clientes com vendas atrasadas
     */
    private function getClientesAtrasados()
    {
        $hoje = now()->format('Y-m-d');
        
        $recebimentos = \App\Models\Recebimento::with('venda.cliente', 'venda.itens')
            ->where('status', 'pendente')
            ->where('data_vencimento', '<', $hoje)
            ->get();

        $clientes = [];

        foreach ($recebimentos as $rec) {
            $venda = $rec->venda;
            $cliente = $venda->cliente;
            
            if (!$cliente || !$cliente->telefone) continue;

            // Formatar telefone
            $telefone = preg_replace('/\D/', '', $cliente->telefone);
            
            // Adicionar 55 se necessário (código do Brasil)
            if (strlen($telefone) === 11) {
                $telefone = '55' . $telefone;
            }

            if (!isset($clientes[$telefone])) {
                $clientes[$telefone] = [
                    'nome' => $cliente->nome,
                    'telefone' => $telefone,
                    'vendas' => []
                ];
            }

            // Montar lista de produtos
            $produtos = [];
            foreach ($venda->itens as $item) {
                $produtos[] = "• {$item->nome_produto} - {$item->quantidade}x R$ " . number_format($item->preco_unitario, 2, ',', '.');
            }

            $clientes[$telefone]['vendas'][] = [
                'id' => $venda->id,
                'data' => date('d/m/Y', strtotime($venda->data)),
                'vencimento' => date('d/m/Y', strtotime($rec->data_vencimento)),
                'total' => number_format($venda->total, 2, ',', '.'),
                'produtos' => implode("\n", $produtos)
            ];
        }

        return array_values($clientes);
    }

    /**
     * Envia mensagem para um telefone via WPPConnect
     */
    private function enviarMensagemWhatsApp($telefone, $mensagem)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json'
            ])->post($this->wppConnectUrl . '/api/' . $this->sessionName . '/send-message', [
                'phone' => $telefone,
                'message' => $mensagem
            ]);

            return [
                'success' => $response->successful(),
                'response' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error("Erro ao enviar mensagem para $telefone: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function logout()
    {
        try {
            Log::info('🔌 Tentando fazer logout da sessão...');

            // Primeiro, verificar status da sessão
            $statusResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken
            ])->get($this->wppConnectUrl . '/api/' . $this->sessionName . '/status-session');

            $statusData = $statusResponse->json();
            $isActive = isset($statusData['status']) && $statusData['status'] === 'CONNECTED';

            if (!$isActive) {
                // Se já está desconectado, apenas atualiza o frontend
                return response()->json([
                    'success' => true,
                    'message' => 'Sessão já estava desconectada.',
                    'alreadyDisconnected' => true
                ]);
            }

            // Se está ativo, faz logout
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json'
            ])->post($this->wppConnectUrl . '/api/' . $this->sessionName . '/logout-session');

            Log::info('Resposta do logout-session:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sessão encerrada com sucesso! Celular desconectado.'
                ]);
            } else {
                // Se der erro mas a sessão não está ativa, considera sucesso
                $errorData = $response->json();
                if (isset($errorData['message']) && strpos($errorData['message'], 'não está ativa') !== false) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Sessão já estava desconectada.'
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao fazer logout: ' . $response->body()
                ], $response->status());
            }

        } catch (\Exception $e) {
            Log::error('❌ Erro ao fazer logout: ' . $e->getMessage());
            
            // Se o erro for porque a sessão não existe, considera sucesso
            if (strpos($e->getMessage(), 'não está ativa') !== false || 
                strpos($e->getMessage(), 'not found') !== false) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sessão já estava desconectada.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erro ao desconectar: ' . $e->getMessage()
            ], 500);
        }
    }
}