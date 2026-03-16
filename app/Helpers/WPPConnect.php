<?php
// app/Helpers/WPPConnect.php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WPPConnect
{
    protected $url;
    protected $session;
    protected $token;
    protected $timeout;

    public function __construct()
    {
        $this->url = config('wppconnect.url');
        $this->session = config('wppconnect.session');
        
        // Usar o token gerado diretamente do .env
        $this->token = env('WPPCONNECT_SECRET_KEY');
        
        // Log para debug (remova depois)
        \Log::info('WPPConnect Helper inicializado', [
            'url' => $this->url,
            'session' => $this->session,
            'token_prefix' => substr($this->token, 0, 15) . '...'
        ]);
        
        $this->timeout = config('wppconnect.timeout', 30);
    }

    /**
     * Envia mensagem via WhatsApp
     * 
     * @param string $telefone Número com código do país (ex: 5518997987391)
     * @param string $mensagem Texto da mensagem
     * @return bool
     */
    public function sendMessage($telefone, $mensagem)
    {
        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => $this->timeout,
                'verify' => false // Apenas para desenvolvimento
            ]);
            
            $response = $client->post("{$this->url}/api/{$this->session}/send-message", [
                'json' => [
                    'phone' => $telefone,
                    'message' => $mensagem
                ],
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);

            if ($statusCode === 201 || $statusCode === 200) {
                Log::info("✅ WhatsApp enviado para {$telefone}", [
                    'session' => $this->session,
                    'message_id' => $body['response'][0]['id'] ?? null
                ]);
                return true;
            }

            Log::error("❌ Erro ao enviar WhatsApp", [
                'status' => $statusCode,
                'body' => $body,
                'telefone' => $telefone
            ]);
            return false;

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);
            
            Log::error("❌ Erro HTTP ao enviar WhatsApp", [
                'status' => $statusCode,
                'erro' => $body,
                'telefone' => $telefone
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error("❌ Exceção ao enviar WhatsApp: " . $e->getMessage(), [
                'telefone' => $telefone,
                'session' => $this->session
            ]);
            return false;
        }
    }

    /**
     * Verifica status da sessão do WhatsApp
     * 
     * @return string CONNECTED | DISCONNECTED | ERROR
     */
    public function checkStatus()
    {
        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 10,
                'verify' => false
            ]);
            
            // Endpoint correto para status (baseado no Swagger)
            $response = $client->get("{$this->url}/api/{$this->session}/status-session", [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}"
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);
            
            // Log para debug
            \Log::info('Resposta status WPPConnect', [
                'status_code' => $statusCode,
                'body' => $body
            ]);
            
            // Verifica se está conectado
            if (isset($body['status']) && $body['status'] === 'CONNECTED') {
                return 'CONNECTED';
            } elseif (isset($body['status'])) {
                return $body['status'];
            }
            
            return 'DISCONNECTED';

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);
            
            \Log::error('Erro ao verificar status WPPConnect', [
                'status_code' => $statusCode,
                'body' => $body
            ]);
            
            return 'ERROR';
            
        } catch (\Exception $e) {
            \Log::error('Exceção ao verificar status: ' . $e->getMessage());
            return 'ERROR';
        }
    }

    

    /**
     * Diagnóstico completo da conexão
     * 
     * @return array
     */
    public function diagnostic()
    {
        $resultado = [
            'config' => [
                'url' => $this->url,
                'session' => $this->session,
                'token' => substr($this->token, 0, 10) . '...', // Mostra só parte do token
            ],
            'tests' => []
        ];
        
        // Teste 1: Verificar se o servidor está respondendo
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 5]);
            $response = $client->get($this->url);
            $resultado['tests']['servidor'] = [
                'status' => 'ok',
                'code' => $response->getStatusCode()
            ];
        } catch (\Exception $e) {
            $resultado['tests']['servidor'] = [
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ];
        }
        
        // Teste 2: Tentar acessar API docs
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 5]);
            $response = $client->get("{$this->url}/api-docs");
            $resultado['tests']['api_docs'] = [
                'status' => 'ok',
                'code' => $response->getStatusCode()
            ];
        } catch (\Exception $e) {
            $resultado['tests']['api_docs'] = [
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ];
        }
        
        // Teste 3: Tentar status da sessão
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 5]);
            $response = $client->get("{$this->url}/api/{$this->session}/status-session", [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}"
                ]
            ]);
            
            $body = json_decode($response->getBody(), true);
            $resultado['tests']['status_session'] = [
                'status' => 'ok',
                'code' => $response->getStatusCode(),
                'conectado' => ($body['status'] ?? '') === 'CONNECTED',
                'resposta' => $body
            ];
        } catch (\Exception $e) {
            $resultado['tests']['status_session'] = [
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ];
        }
        
        return $resultado;
    }

    /**
     * Verifica se o WhatsApp está conectado
     * 
     * @return bool
     */
    public function isConnected()
    {
        return $this->checkStatus() === 'CONNECTED';
    }

    /**
     * Formata número de telefone para o padrão do WhatsApp
     * 
     * @param string $telefone
     * @return string
     */
    public function formatPhone($telefone)
    {
        // Remove caracteres não numéricos
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        
        // Adiciona código do Brasil se necessário
        if (strlen($telefone) === 10 || strlen($telefone) === 11) {
            return '55' . $telefone;
        }
        
        return $telefone;
    }

    /**
     * Gera QR Code para conectar nova sessão
     * 
     * @return string|null
     */
    public function getQRCode()
    {
        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 30,
                'verify' => false
            ]);
            
            $response = $client->post("{$this->url}/api/{$this->session}/start-session", [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}"
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            
            return $body['qrcode'] ?? null;

        } catch (\Exception $e) {
            Log::error("❌ Erro ao gerar QR Code: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Desconecta a sessão atual
     * 
     * @return bool
     */
    public function logout()
    {
        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 30,
                'verify' => false
            ]);
            
            $response = $client->post("{$this->url}/api/{$this->session}/logout-session", [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}"
                ]
            ]);

            $statusCode = $response->getStatusCode();
            
            return $statusCode === 200 || $statusCode === 201;

        } catch (\Exception $e) {
            Log::error("❌ Erro ao fazer logout: " . $e->getMessage());
            return false;
        }
    }
}