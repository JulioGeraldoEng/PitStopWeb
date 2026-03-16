/**
 * CONFIGURAÇÕES.JS
 * Gerencia todas as interações da página de configurações
 */

// ==================== VARIÁVEIS GLOBAIS ====================
let processandoBackup = false; // <-- ADICIONADO PARA EVITAR DUPLICAÇÃO

const API = {
    BACKUP_MANUAL: '/configuracoes/backup-manual',
    LISTAR_BACKUPS: '/configuracoes/listar-backups',
    IMPORTAR_BACKUP: '/configuracoes/importar-backup',
    DOWNLOAD_BACKUP: '/backups/download',
    RESTORE_BACKUP: '/backups/restore',
    SALVAR_TEMA: '/configuracoes/tema',
    TESTAR_NOTIFICACAO: '/testar-notificacao',
    ENCERRAR_SESSOES: '/configuracoes/encerrar-sessoes',
    EXPORTAR_DADOS: '/configuracoes/exportar-dados',
    EXCLUIR_CONTA: '/configuracoes/excluir-conta'
};

// ==================== INICIALIZAÇÃO ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Página de configurações carregada');
    inicializarControleWhatsApp();
    inicializarTema();
    inicializarEventos();
    carregarListaBackups();
});

// ==================== CONTROLE WHATSAPP ====================
function inicializarControleWhatsApp() {
    const whatsappCheckbox = document.getElementById('notificacoes_whatsapp');
    const whatsappFrequency = document.getElementById('whatsappFrequency');
    
    if (whatsappCheckbox && whatsappFrequency) {
        whatsappFrequency.style.display = whatsappCheckbox.checked ? 'block' : 'none';
        
        whatsappCheckbox.addEventListener('change', function() {
            whatsappFrequency.style.display = this.checked ? 'block' : 'none';
        });
    }
}

// ==================== EVENTOS ====================
function inicializarEventos() {
    // Botão de backup manual - com prevenção de eventos duplicados
    const btnBackup = document.getElementById('btnBackup');
    if (btnBackup) {
        btnBackup.removeEventListener('click', fazerBackupManual);
        btnBackup.addEventListener('click', fazerBackupManual);
    }

    // Form de importação
    const formImportar = document.getElementById('formImportar');
    if (formImportar) {
        formImportar.removeEventListener('submit', validarImportacao);
        formImportar.addEventListener('submit', validarImportacao);
    }

    // Input de arquivo
    const fileInput = document.getElementById('backup_file');
    if (fileInput) {
        fileInput.removeEventListener('change', mostrarNomeArquivo);
        fileInput.addEventListener('change', mostrarNomeArquivo);
    }
}

// ==================== CONTROLE DE TEMA ====================
function inicializarTema() {
    const temaSelect = document.getElementById('tema');
    if (!temaSelect) return;
    
    aplicarTema(temaSelect.value);
    
    temaSelect.addEventListener('change', function() {
        const tema = this.value;
        aplicarTema(tema);
        salvarTema(tema);
    });
}

function aplicarTema(tema) {
    const html = document.documentElement;
    html.classList.remove('dark', 'light', 'auto');
    
    if (tema === 'escuro') {
        html.classList.add('dark');
        atualizarIconeTema('fa-sun', 'Modo Claro');
    } else if (tema === 'claro') {
        html.classList.add('light');
        atualizarIconeTema('fa-moon', 'Modo Escuro');
    } else if (tema === 'auto') {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            html.classList.add('dark');
        } else {
            html.classList.add('light');
        }
        atualizarIconeTema('fa-desktop', 'Automático');
    }
}

function atualizarIconeTema(icone, texto) {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        const themeIcon = themeToggle.querySelector('i');
        const themeText = themeToggle.querySelector('span:not(.ms-auto)');
        
        if (themeIcon) themeIcon.className = `fas ${icone}`;
        if (themeText) themeText.textContent = texto;
    }
    
    const switchInput = document.getElementById('darkModeSwitch');
    if (switchInput) switchInput.checked = (icone === 'fa-sun');
}

function salvarTema(tema) {
    fetch(API.SALVAR_TEMA, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify({ tema: tema })
    })
    .then(verificarResposta)
    .then(data => {
        console.log('✅ Tema salvo:', data);
        mostrarNotificacao('sucesso', 'Tema atualizado!');
    })
    .catch(error => {
        console.error('❌ Erro ao salvar tema:', error);
        mostrarNotificacao('erro', 'Erro ao salvar tema');
    });
}

// Detectar mudanças no tema automático
if (window.matchMedia) {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQuery.addEventListener('change', function(e) {
        const temaSelect = document.getElementById('tema');
        if (temaSelect && temaSelect.value === 'auto') {
            if (e.matches) {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
            } else {
                document.documentElement.classList.add('light');
                document.documentElement.classList.remove('dark');
            }
            atualizarIconeTema('fa-desktop', 'Automático');
        }
    });
}

// ==================== BACKUP MANUAL (VERSÃO CORRIGIDA) ====================
function fazerBackupManual() {
    // IMPEDIR CLIQUE DUPLICADO
    if (processandoBackup) {
        console.log('⏳ Backup já em andamento...');
        return;
    }
    
    processandoBackup = true;
    
    const btn = document.getElementById('btnBackup');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando...';
    }

    Swal.fire({
        title: 'Gerando backup...',
        text: 'Aguarde, isso pode levar alguns segundos',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch(API.BACKUP_MANUAL, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json'
        }
    })
    .then(verificarResposta)
    .then(response => {
        const contentDisposition = response.headers.get('Content-Disposition');
        let filename = `backup_${formatarDataHora()}.sql`;
        
        if (contentDisposition) {
            const match = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
            if (match && match[1]) {
                filename = match[1].replace(/['"]/g, '');
            }
        }

        return response.blob().then(blob => ({ blob, filename }));
    })
    .then(({ blob, filename }) => {
        // CRIAR E CLICAR UMA ÚNICA VEZ
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        
        // Limpar
        setTimeout(() => {
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }, 100);
        
        Swal.close();
        mostrarNotificacao('sucesso', 'Backup gerado com sucesso!');
        carregarListaBackups();
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: error.message || 'Erro ao gerar backup'
        });
    })
    .finally(() => {
        // Liberar o bloqueio
        processandoBackup = false;
        
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-download"></i> Fazer backup agora';
        }
    });
}

// 🚫 FUNÇÃO processarDownload REMOVIDA (estava causando duplicação)

// ==================== LISTAR BACKUPS ====================
function carregarListaBackups() {
    const container = document.getElementById('lista-backups');
    if (!container) return;
    
    container.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Carregando backups...</div>';

    fetch(API.LISTAR_BACKUPS, { headers: { 'Accept': 'application/json' } })
        .then(response => response.json())
        .then(backups => {
            if (backups.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-database"></i> Nenhum backup encontrado</div>';
                return;
            }
            
            const manuais = backups.filter(b => b.type === 'manual');
            const automaticos = backups.filter(b => b.type === 'automatico');
            const segurancas = backups.filter(b => b.type === 'seguranca');
            
            let html = '';
            
            if (automaticos.length > 0) {
                html += '<div class="mb-3"><small class="text-muted"><i class="fas fa-clock"></i> BACKUPS AUTOMÁTICOS</small></div>';
                automaticos.forEach(backup => html += gerarItemBackup(backup));
            }
            
            if (manuais.length > 0) {
                html += '<div class="mt-3 mb-3"><small class="text-muted"><i class="fas fa-download"></i> BACKUPS MANUAIS</small></div>';
                manuais.forEach(backup => html += gerarItemBackup(backup));
            }
            
            if (segurancas.length > 0) {
                html += '<div class="mt-3 mb-3"><small class="text-muted"><i class="fas fa-shield-alt"></i> BACKUPS DE SEGURANÇA</small></div>';
                segurancas.forEach(backup => html += gerarItemBackup(backup));
            }
            
            container.innerHTML = html;
        })
        .catch(() => {
            container.innerHTML = '<div class="text-center text-danger py-3"><i class="fas fa-exclamation-circle"></i> Erro ao carregar backups</div>';
        });
}

function gerarItemBackup(backup) {
    return `
        <div class="backup-item" data-id="${backup.id}">
            <div class="backup-info">
                <span class="backup-date"><i class="far fa-calendar-alt"></i> ${backup.created_at}</span>
                <span class="backup-details">
                    <span class="badge bg-${backup.type_color}"><i class="fas ${backup.type_icon}"></i> ${backup.type_text}</span>
                    <span class="badge bg-secondary"><i class="fas fa-database"></i> ${backup.size_formatted}</span>
                    ${!backup.file_exists ? '<span class="badge bg-danger">Arquivo não encontrado</span>' : ''}
                </span>
            </div>
            <div class="backup-actions">
                <button class="btn-backup-action btn-download" onclick="baixarBackup('${backup.filename}')" title="Baixar backup" ${!backup.file_exists ? 'disabled' : ''}>
                    <i class="fas fa-download"></i>
                </button>
                <button class="btn-backup-action btn-restore" onclick="restaurarBackup('${backup.filename}')" title="Restaurar backup" ${!backup.file_exists ? 'disabled' : ''}>
                    <i class="fas fa-undo-alt"></i>
                </button>
            </div>
        </div>
    `;
}

// ==================== BAIXAR BACKUP ====================
function baixarBackup(filename) {
    Swal.fire({
        title: 'Preparando download...',
        text: 'Aguarde um momento',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch(`${API.DOWNLOAD_BACKUP}/${encodeURIComponent(filename)}`, {
        headers: { 'X-CSRF-TOKEN': getCsrfToken() }
    })
    .then(verificarResposta)
    .then(response => response.blob())
    .then(blob => {
        const downloadUrl = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = downloadUrl;
        a.download = filename;
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        
        setTimeout(() => {
            document.body.removeChild(a);
            window.URL.revokeObjectURL(downloadUrl);
        }, 100);
        
        Swal.close();
        mostrarNotificacao('sucesso', 'Download iniciado!');
    })
    .catch(tratarErro);
}

// ==================== RESTAURAR BACKUP ====================
function restaurarBackup(filename) {
    Swal.fire({
        title: 'Restaurar backup?',
        html: '<p>Esta ação <strong>substituirá todos os dados atuais</strong>.</p><p class="text-danger">⚠️ Esta operação não pode ser desfeita!</p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, restaurar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Restaurando...',
                text: 'Aguarde, isso pode levar alguns minutos',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`${API.RESTORE_BACKUP}/${encodeURIComponent(filename)}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() }
            })
            .then(verificarResposta)
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Backup restaurado!',
                    text: 'Seus dados foram restaurados com sucesso',
                    timer: 3000
                });
            })
            .catch(tratarErro);
        }
    });
}

// ==================== TESTAR NOTIFICAÇÃO ====================
function testarNotificacao() {
    const tipo = document.getElementById('teste_tipo').value;
    
    Swal.fire({
        title: 'Enviando teste...',
        text: 'Aguarde, enviando notificação via WhatsApp',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch(API.TESTAR_NOTIFICACAO, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify({ tipo: tipo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            Swal.fire({
                icon: 'success',
                title: '✅ Teste enviado!',
                text: 'Verifique seu WhatsApp',
                timer: 3000
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: '❌ Erro',
                text: data.mensagem || 'Falha ao enviar teste'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: '❌ Erro',
            text: error.message
        });
    });
}

// ==================== IMPORTAR BACKUP ====================
function validarImportacao(event) {
    const fileInput = document.getElementById('backup_file');
    const file = fileInput?.files[0];
    
    if (!file) {
        event.preventDefault();
        mostrarNotificacao('erro', 'Selecione um arquivo para importar');
        return;
    }

    const extensao = file.name.split('.').pop().toLowerCase();
    if (!['sql', 'zip'].includes(extensao)) {
        event.preventDefault();
        mostrarNotificacao('erro', 'Formato inválido. Use .sql ou .zip');
        return;
    }

    if (file.size > 100 * 1024 * 1024) {
        event.preventDefault();
        mostrarNotificacao('erro', 'Arquivo muito grande. Máximo 100MB');
        return;
    }

    if (!confirm('A importação substituirá os dados atuais. Deseja continuar?')) {
        event.preventDefault();
        return;
    }

    Swal.fire({
        title: 'Importando...',
        text: 'Aguarde, isso pode levar alguns minutos',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
}

// ==================== UTILITÁRIOS ====================
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function verificarResposta(response) {
    if (!response.ok) {
        return response.json().then(data => {
            throw new Error(data.message || 'Erro na requisição');
        });
    }
    return response;
}

function tratarErro(error) {
    console.error('Erro:', error);
    Swal.fire({
        icon: 'error',
        title: 'Ops! Algo deu errado',
        text: error.message,
        confirmButtonColor: '#3085d6'
    });
}

function mostrarNotificacao(tipo, mensagem) {
    Swal.fire({
        icon: tipo,
        title: tipo === 'sucesso' ? 'Sucesso!' : 'Erro!',
        text: mensagem,
        timer: 3000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
    });
}

function mostrarNomeArquivo(event) {
    const file = event.target.files[0];
    const label = document.querySelector('.custom-file-label');
    if (label && file) label.textContent = file.name;
}

function formatarDataHora() {
    const agora = new Date();
    return `${agora.getFullYear()}${String(agora.getMonth()+1).padStart(2,'0')}${String(agora.getDate()).padStart(2,'0')}_${String(agora.getHours()).padStart(2,'0')}${String(agora.getMinutes()).padStart(2,'0')}${String(agora.getSeconds()).padStart(2,'0')}`;
}

// ==================== FUNÇÕES DA ZONA DE PERIGO ====================
function encerrarSessoes() {
    Swal.fire({
        title: 'Encerrar todas as sessões?',
        text: 'Você será desconectado de todos os dispositivos, exceto este.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, encerrar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(API.ENCERRAR_SESSOES, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() }
            })
            .then(verificarResposta)
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Sessões encerradas!',
                    text: 'Todas as outras sessões foram encerradas',
                    timer: 2000
                });
            })
            .catch(tratarErro);
        }
    });
}

function exportarDados() {
    Swal.fire({
        title: 'Exportar dados?',
        text: 'Seus dados serão exportados em formato JSON',
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, exportar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) window.location.href = API.EXPORTAR_DADOS;
    });
}

function excluirConta() {
    Swal.fire({
        title: 'Excluir conta permanentemente?',
        html: `
            <p class="text-danger">⚠️ Esta ação não pode ser desfeita!</p>
            <p>Todos os seus dados serão perdidos.</p>
            <input type="text" id="confirmar" class="form-control mt-3" placeholder="Digite 'EXCLUIR' para confirmar">
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const confirmar = document.getElementById('confirmar')?.value;
            if (confirmar !== 'EXCLUIR') {
                Swal.showValidationMessage('Digite EXCLUIR para confirmar');
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(API.EXCLUIR_CONTA, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() }
            })
            .then(verificarResposta)
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Conta excluída!',
                    text: 'Sua conta foi excluída permanentemente',
                    timer: 2000
                }).then(() => window.location.href = '/logout');
            })
            .catch(tratarErro);
        }
    });
}