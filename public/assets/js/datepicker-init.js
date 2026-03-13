// Inicialização global do Datepicker
$(document).ready(function() {
    $('.datepicker-field').datepicker({
        format: 'dd/mm/yyyy',
        language: 'pt-BR',
        autoclose: true,
        todayHighlight: true,
        orientation: 'bottom auto',
        daysOfWeekHighlighted: "0,6",
        clearBtn: true,
        todayBtn: "linked"
    });
});

// Função para formatar data ISO (para envio ao backend)
function formatarDataISO(dataBr) {
    if (!dataBr || dataBr.length !== 10) return null;
    const partes = dataBr.split('/');
    return `${partes[2]}-${partes[1]}-${partes[0]}`;
}

// Função para formatar data para exibição
function formatarData(data) {
    if (!data) return '-';
    const partes = data.split('-');
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    return data;
}

// Máscara de data (fallback para quando o datepicker não for usado)
function mascaraData(input) {
    input.addEventListener('input', function() {
        let valor = this.value.replace(/\D/g, '');
        if (valor.length > 2 && valor.length <= 4) {
            valor = valor.replace(/(\d{2})(\d{1,2})/, '$1/$2');
        } else if (valor.length > 4) {
            valor = valor.replace(/(\d{2})(\d{2})(\d{1,4})/, '$1/$2/$3');
        }
        this.value = valor.substring(0, 10);
    });
}

// Aplicar máscara a todos os campos de data (caso o datepicker não carregue)
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.datepicker-field').forEach(function(input) {
        mascaraData(input);
    });
});