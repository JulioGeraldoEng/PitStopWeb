// ===================== WHATSAPP INDEX =====================
let socket = null;
let tentativasReconexao = 0;
const MAX_TENTATIVAS = 5;

// ===================== FUNÇÕES AUXILIARES =====================
function mostrarMensagem(texto, tipo) {
    const mensagem = document.getElementById('mensagem');
    if (!mensagem) return;
    
    mensagem.textContent = texto;
    mensagem.className = `alert alert-${tipo} mensagem`;
    mensagem.style.display = 'block';
    
    setTimeout(() => {
        mensagem.style.display = 'none';
    }, 5000);
}

function atualizarStatus(conectado, mensagem = '') {
    const statusCard = document.getElementById('status-card');
    const statusText = document.getElementById('status-text');
    const statusBadge = document.getElementById('status-badge');
    
    if (conectado) {
        statusCard.classList.add('connected');
        statusCard.classList.remove('disconnected');
        statusBadge.className = 'status-badge connected';
        statusBadge.innerHTML = '<i class="fas fa-check-circle"></i> Conectado';
        statusText.textContent = mensagem || 'WhatsApp conectado com sucesso!';
    } else {
        statusCard.classList.add('disconnected');
        statusCard.classList.remove('connected');
        statusBadge.className = 'status-badge disconnected';
        statusBadge.innerHTML = '<i class="fas fa-times-circle"></i> Desconectado';
        statusText.textContent = mensagem || 'WhatsApp desconectado';
    }
}

function mostrarCarregando() {
    const qrcodeContainer = document.getElementById('qrcode-container');
    qrcodeContainer.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Aguardando QR Code...</p>
        </div>
    `;
}

function mostrarQRCode(qrData) {
    console.log('Mostrando QR Code:', qrData);
    
    const qrcodeContainer = document.getElementById('qrcode-container');
    
    // Extrair a string base64 do objeto
    let qrBase64 = '';
    
    if (typeof qrData === 'object') {
        // Se for objeto, pega a propriedade 'data' ou 'qrcode'
        qrBase64 = qrData.data || qrData.qrcode || '';
    } else if (typeof qrData === 'string') {
        qrBase64 = qrData;
    }
    
    console.log('QR Base64 extraído:', qrBase64.substring(0, 50) + '...');
    
    // Se já vier com data:image, usa direto
    // Se não, adiciona o prefixo
    const src = qrBase64.startsWith('data:image') ? qrBase64 : `data:image/png;base64,${qrBase64}`;
    
    qrcodeContainer.innerHTML = `
        <div style="text-align: center;">
            <img src="${src}" alt="QR Code WhatsApp" style="max-width: 300px; border: 2px solid #25d366; border-radius: 10px; padding: 10px; background: white; margin-bottom: 15px;">
            <p style="margin-top: 10px; color: #6c757d; font-size: 1rem;">
                <i class="fas fa-qrcode" style="margin-right: 5px;"></i>
                Escaneie o QR Code com seu WhatsApp
            </p>
        </div>
    `;
}

// ===================== WEBSOCKET =====================
function conectarWebSocket() {
    if (socket && socket.connected) return;

    socket = io('http://localhost:21465', {
        transports: ['websocket'],
        reconnectionAttempts: 5
    });

    socket.onAny((eventName, ...args) => {
        console.log(`📡 Evento recebido: ${eventName}`, args);
    });

    socket.on('connect', () => {
        console.log('✅ WebSocket conectado');
        mostrarCarregando();
    });

    socket.on('qrCode', (data) => {
        console.log('📱 QR Code recebido', data);
        mostrarQRCode(data);
        atualizarStatus(false, 'Escaneie o QR Code com seu WhatsApp');
    });

    // ✅ NOVO EVENTO - SESSÃO LOGADA
    socket.on('session-logged', (data) => {
        console.log('✅ Sessão logada com sucesso!', data);
        atualizarStatus(true, 'WhatsApp conectado com sucesso!');
        
        const qrcodeContainer = document.getElementById('qrcode-container');
        qrcodeContainer.innerHTML = `
            <div style="text-align: center;">
                <i class="fas fa-check-circle" style="font-size: 5rem; color: #28a745; margin-bottom: 15px;"></i>
                <p style="color: #28a745; font-size: 1.2rem; font-weight: bold;">
                    ✅ WhatsApp Conectado!
                </p>
            </div>
        `;
    });

    socket.on('status', (data) => {
        console.log('📊 Status recebido:', data);
        
        const isConnected = 
            data.status === 'CONNECTED' || 
            data.status === 'isConnected' ||
            data.status === 'connected' ||
            data.isConnected === true ||
            data.connected === true;
        
        if (isConnected) {
            atualizarStatus(true, 'WhatsApp conectado com sucesso!');
        } else {
            atualizarStatus(false, data.message || 'Desconectado');
        }
    });

    socket.on('disconnect', () => {
        console.log('🔴 WebSocket desconectado');
        atualizarStatus(false, 'Conexão perdida');
        mostrarCarregando();
    });
}

// ===================== FUNÇÕES DOS BOTÕES =====================
async function conectarWhatsApp() {
    try {
        const btn = document.getElementById('btnConectar');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Conectando...';
        btn.disabled = true;
        
        const token = document.querySelector('meta[name="csrf-token"]').content;
        
        const response = await fetch('/api/whatsapp/conectar', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json'
            }
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Erro ao conectar');
        }

        mostrarMensagem(result.message || 'Solicitação de conexão enviada!', 'success');
        
    } catch (error) {
        console.error('Erro ao conectar:', error);
        mostrarMensagem('Erro ao conectar com o WhatsApp: ' + error.message, 'danger');
    } finally {
        const btn = document.getElementById('btnConectar');
        btn.innerHTML = '<i class="fas fa-plug"></i> Conectar';
        btn.disabled = false;
    }
}

async function reiniciarSessao() {
    try {
        const btn = document.getElementById('btnReiniciar');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Reiniciando...';
        btn.disabled = true;
        
        const token = document.querySelector('meta[name="csrf-token"]').content;
        
        const response = await fetch('/api/whatsapp/reiniciar', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json'
            }
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Erro ao reiniciar');
        }

        mostrarMensagem(result.message || 'Sessão reiniciada com sucesso!', 'success');
        mostrarCarregando();
        atualizarStatus(false, 'Sessão reiniciada. Aguardando QR Code...');
        
    } catch (error) {
        console.error('Erro ao reiniciar:', error);
        mostrarMensagem('Erro ao reiniciar sessão: ' + error.message, 'danger');
    } finally {
        const btn = document.getElementById('btnReiniciar');
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> Reiniciar';
        btn.disabled = false;
    }
}

async function enviarAtrasados() {
    try {
        const btn = document.getElementById('btnEnviar');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        btn.disabled = true;
        
        const token = document.querySelector('meta[name="csrf-token"]').content;
        
        const response = await fetch('/api/whatsapp/enviar-atrasados', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json'
            }
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Erro ao enviar');
        }

        mostrarMensagem(result.message || 'Mensagens enviadas com sucesso!', 'success');
        
    } catch (error) {
        console.error('Erro ao enviar:', error);
        mostrarMensagem('Erro ao enviar mensagens: ' + error.message, 'danger');
    } finally {
        const btn = document.getElementById('btnEnviar');
        btn.innerHTML = '<i class="fab fa-whatsapp"></i> Enviar Atrasados';
        btn.disabled = false;
    }
}

// ===================== FUNÇÃO DE LOGOUT =====================
async function fazerLogout() {
    try {
        const btn = document.getElementById('btnLogout');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Desconectando...';
        btn.disabled = true;
        
        const token = document.querySelector('meta[name="csrf-token"]').content;
        
        const response = await fetch('/api/whatsapp/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json'
            }
        });

        const result = await response.json();

        if (!response.ok && !result.alreadyDisconnected) {
            throw new Error(result.message || 'Erro ao desconectar');
        }

        mostrarMensagem(result.message || 'Sessão encerrada', 'success');
        
        // Atualizar status
        atualizarStatus(false, 'Sessão encerrada');
        
        // Voltar para tela de carregamento
        mostrarCarregando();

    } catch (error) {
        console.error('Erro ao desconectar:', error);
        mostrarMensagem('Erro: ' + error.message, 'danger');
    } finally {
        const btn = document.getElementById('btnLogout');
        btn.innerHTML = '<i class="fas fa-sign-out-alt"></i> Desconectar';
        btn.disabled = false;
    }
}

// ===================== INICIALIZAÇÃO =====================
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Página WhatsApp carregada');
    
    const btnConectar = document.getElementById('btnConectar');
    const btnReiniciar = document.getElementById('btnReiniciar');
    const btnEnviar = document.getElementById('btnEnviar');
    const btnLogout = document.getElementById('btnLogout');

    // Conectar WebSocket automaticamente
    conectarWebSocket();

    // Eventos dos botões
    if (btnConectar) {
        btnConectar.addEventListener('click', conectarWhatsApp);
    }

    if (btnReiniciar) {
        btnReiniciar.addEventListener('click', reiniciarSessao);
    }

    if (btnEnviar) {
        btnEnviar.addEventListener('click', enviarAtrasados);
    }

    if (btnLogout) {
        btnLogout.addEventListener('click', fazerLogout);
    }

    // Verificar status inicial
    // Tentar reconectar automaticamente se já estiver conectado
    setTimeout(() => {
        fetch('/api/whatsapp/status')
            .then(r => r.json())
            .then(data => {
                if (data.connected) {
                    atualizarStatus(true, 'WhatsApp conectado!');
                } else {
                    // Se não estiver conectado, tenta iniciar a sessão
                    fetch('/api/whatsapp/conectar', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                }
            });
    }, 2000);
});