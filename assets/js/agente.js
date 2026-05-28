let modoAgente = 'local';

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
        msg.innerHTML = texto;
    }
}

function definirModoAgente(modo) {
    modoAgente = modo;

    const btnLocal = document.getElementById('modoLocal');
    const btnIA = document.getElementById('modoIA');
    const input = document.getElementById('agentInput');

    if (btnLocal && btnIA) {
        btnLocal.classList.toggle('active', modo === 'local');
        btnIA.classList.toggle('active', modo === 'ia');
    }

    if (input) {
        input.placeholder = modo === 'ia'
            ? 'Pergunte para a IA...'
            : 'Ex: abrir sala D02';
    }

    if (modo === 'ia') {
        agentMessage('Modo IA ativado. Digite sua pergunta e eu consultarei a IA configurada.');
    } else {
        agentMessage('Modo local ativado. Posso abrir salas, importar planilhas e acessar áreas do sistema.');
    }
}

function agentGo(destino) {
    processarComandoAgente(destino);
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

function normalizarTexto(texto) {
    return String(texto || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

function processarComandoAgente(comando) {
    if (modoAgente === 'ia') {
        consultarIAAgente(comando);
        return;
    }

    const texto = normalizarTexto(comando);

    if (texto.includes('importar') || texto.includes('planilha') || texto.includes('excel') || texto.includes('xlsx')) {
        agentMessage('Abrindo a tela de importação de planilha...');
        setTimeout(() => {
            window.location.href = 'importar_planilha.php';
        }, 300);
        return;
    }

    if (texto.includes('alteracoes') || texto.includes('importacoes') || texto.includes('ultimas')) {
        agentMessage('Abrindo últimas alterações...');
        setTimeout(() => {
            window.location.href = 'index.php#ultimas-alteracoes';
        }, 300);
        return;
    }

    if (texto.includes('chamado') || texto.includes('manutencao') || texto.includes('modificacoes')) {
        agentMessage('Abrindo salas com mais chamados...');
        setTimeout(() => {
            window.location.href = 'index.php#salas-com-chamados';
        }, 300);
        return;
    }

    if (texto.includes('inicio') || texto.includes('dashboard') || texto.includes('setores') || texto.includes('principal')) {
        agentMessage('Abrindo painel inicial...');
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 300);
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
        agentMessage('Não entendi no modo local. Tente: importar planilha, abrir sala D02, ver chamados ou mude para o modo IA.');
        return;
    }

    const bloco = match[1];
    let numero = match[2];

    if (bloco === 'D' && Number(numero) > 3) {
        agentMessage('O Bloco D possui somente D01, D02 e D03.');
        return;
    }

    if ((bloco === 'A' || bloco === 'C' || bloco === 'E') && Number(numero) > 9) {
        agentMessage('Esse bloco possui salas de 01 até 09.');
        return;
    }

    numero = numero.padStart(2, '0');
    const sala = bloco + numero;

    abrirSala(bloco, sala);
}

function abrirSala(bloco, sala) {
    agentMessage('Abrindo sala ' + sala + '...');

    setTimeout(() => {
        window.location.href = 'sala.php?bloco=' + encodeURIComponent(bloco) + '&sala=' + encodeURIComponent(sala);
    }, 300);
}

async function consultarIAAgente(comando) {
    agentMessage('Estou consultando a IA...');

    try {
        const resposta = await fetch('api/agente_ia.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ mensagem: comando })
        });

        const dados = await resposta.json();

        if (!dados.ok) {
            agentMessage(dados.resposta || 'Não consegui consultar a IA agora.');
            return;
        }

        agentMessage(dados.resposta || 'Encontrei uma resposta.');

        if (dados.tipo === 'redirect' && dados.url) {
            setTimeout(() => {
                window.location.href = dados.url;
            }, 700);
        }
    } catch (erro) {
        agentMessage('Não consegui conectar com a IA agora. Use o modo local ou tente novamente depois.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const box = document.getElementById('agentBox');

    if (!box) {
        return;
    }

    const paginaAtual = window.location.pathname.split('/').pop();

    if (paginaAtual === '' || paginaAtual === 'index.php') {
        box.classList.remove('closed');
        agentMessage('Olá! Bem-vindo ao Estoque COC. Precisa de ajuda? Você pode consultar localmente ou pela IA.');
    }
});