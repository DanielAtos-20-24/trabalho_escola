function toggleAgent() {
    const box = document.getElementById('agentBox');

    if (!box) {
        return;
    }

    box.classList.toggle('closed');
}

function agentMessage(texto) {
    const msg = document.getElementById('agentMessage');

    if (msg) {
        msg.textContent = texto;
    }
}

function agentGo(destino) {
    const comando = String(destino || '').toLowerCase();

    if (comando === 'importar') {
        agentMessage('Abrindo a tela de importação de planilha...');
        window.location.href = 'importar_planilha.php';
        return;
    }

    if (comando === 'alteracoes') {
        agentMessage('Abrindo últimas alterações...');
        window.location.href = 'index.php#ultimas-alteracoes';
        return;
    }

    if (comando === 'chamados') {
        agentMessage('Abrindo salas com mais chamados...');
        window.location.href = 'index.php#salas-com-chamados';
        return;
    }

    abrirSalaPorTexto(comando);
}

function agentSubmit(event) {
    event.preventDefault();

    const input = document.getElementById('agentInput');

    if (!input) {
        return;
    }

    const comando = input.value.trim();

    if (comando === '') {
        return;
    }

    input.value = '';
    processarComandoAgente(comando);
}

function processarComandoAgente(comando) {
    const texto = comando.toLowerCase();

    if (texto.includes('importar') || texto.includes('planilha')) {
        agentGo('importar');
        return;
    }

    if (texto.includes('alterações') || texto.includes('alteracoes') || texto.includes('importações') || texto.includes('importacoes')) {
        agentGo('alteracoes');
        return;
    }

    if (texto.includes('chamado') || texto.includes('manutenção') || texto.includes('manutencao')) {
        agentGo('chamados');
        return;
    }

    if (texto.includes('bloco a')) {
        abrirSala('A', 'A01');
        return;
    }

    if (texto.includes('bloco c')) {
        abrirSala('C', 'C01');
        return;
    }

    if (texto.includes('bloco d')) {
        abrirSala('D', 'D01');
        return;
    }

    if (texto.includes('bloco e')) {
        abrirSala('E', 'E01');
        return;
    }

    abrirSalaPorTexto(texto);
}

function abrirSalaPorTexto(texto) {
    const match = String(texto).toUpperCase().match(/\b([ACDE])\s?0?([1-9])\b/);

    if (!match) {
        agentMessage('Não entendi. Tente: importar planilha, abrir sala D02, ver chamados ou últimas alterações.');
        return;
    }

    const bloco = match[1];
    let numero = match[2];

    if (bloco === 'D' && Number(numero) > 3) {
        agentMessage('O Bloco D possui somente D01, D02 e D03.');
        return;
    }

    numero = numero.padStart(2, '0');
    const sala = bloco + numero;

    abrirSala(bloco, sala);
}

function abrirSala(bloco, sala) {
    agentMessage('Abrindo sala ' + sala + '...');
    window.location.href = 'sala.php?bloco=' + encodeURIComponent(bloco) + '&sala=' + encodeURIComponent(sala);
}

document.addEventListener('DOMContentLoaded', function() {
    const box = document.getElementById('agentBox');

    if (!box) {
        return;
    }

    const paginaAtual = window.location.pathname.split('/').pop();

    if (paginaAtual === '' || paginaAtual === 'index.php') {
        box.classList.remove('closed');
        agentMessage('Olá! Bem-vindo ao Estoque COC. Precisa de ajuda? Posso abrir salas, importar planilhas, mostrar chamados ou acessar as últimas alterações.');
    }
});