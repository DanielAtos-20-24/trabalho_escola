/* assets/js/agente.js
 * Script organizado do agente virtual
 * Centraliza toda a lógica do widget e exporta comportamento via atributos
 */
let modoAgente = 'local';

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

(function () {
    'use strict';

    // Seletores e elementos principais
    const SELECTORS = {
        box: 'agentBox',
        header: 'agentHeader',
        message: 'agentMessage',
        input: 'agentInput',
        form: 'agentForm',
        actionButtons: '.agent-actions button'
    };

    // Mapa de ações simples -> url
    const ACTIONS = {
        'index': 'index.php',
        'setores': 'index.php',
        'importar': 'importar_planilha.php',
        'importar planilha': 'importar_planilha.php',
        'manutencao': 'manutencao.php',
        'manutenção': 'manutencao.php',
        'dados': 'file:///c:/xampp/htdocs/programa_equipamentos/dados/',
        'uploads': 'file:///c:/xampp/htdocs/programa_equipamentos/uploads/'
    };

    // Helpers
    function $id(id) { return document.getElementById(id); }
    function qsAll(sel) { return Array.from(document.querySelectorAll(sel)); }

    function setMessage(text) {
        const el = $id(SELECTORS.message);
        if (el) el.textContent = String(text || '');
    }

    function toggleBox() {
        const box = $id(SELECTORS.box);
        if (!box) return;
        box.classList.toggle('closed');
    }

    function openUrl(url) {
        if (!url) return;
        if (url.startsWith('file:///')) {
            window.open(url, '_blank');
            return;
        }
        window.location.href = url;
    }

    function handleActionKey(key) {
        const k = String(key || '').toLowerCase().trim();
        if (ACTIONS[k]) {
            setMessage('Abrindo ' + k + '...');
            openUrl(ACTIONS[k]);
            return true;
        }

        // pre-redirect shortcuts
        if (k === 'alteracoes' || k === 'alterações' || k === 'importacoes' || k === 'importações') {
            setMessage('Abrindo importações...');
            openUrl('index.php#ultimas-alteracoes');
            return true;
        }

        if (k === 'chamados' || k === 'manutencoes' || k === 'manutenções') {
            setMessage('Abrindo lista de chamados...');
            openUrl('index.php#salas-com-chamados');
            return true;
        }

        return false;
    }

    function abrirSala(bloco, sala) {
        setMessage('Abrindo sala ' + sala + '...');
        openUrl('sala.php?bloco=' + encodeURIComponent(bloco) + '&sala=' + encodeURIComponent(sala));
    }

    function abrirSalaPorTexto(texto) {
        const m = String(texto).toUpperCase().match(/\b([ACDE])\s?0?([1-9])\b/);
        if (!m) return false;
        const bloco = m[1];
        let numero = m[2];

        if (bloco === 'D' && Number(numero) > 3) {
            setMessage('O Bloco D possui somente D01, D02 e D03.');
            return true;
        }

        numero = String(numero).padStart(2, '0');
        abrirSala(bloco, bloco + numero);
        return true;
    }

    async function consultarIAAgente(comando) {
        setMessage('Estou analisando sua solicitação...');
        try {
            const resp = await fetch('api/agente_ia.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mensagem: comando })
            });
            const dados = await resp.json();
            if (!dados || !dados.ok) {
                setMessage(dados && dados.resposta ? dados.resposta : 'Não consegui consultar a IA agora.');
                return;
            }
            setMessage(dados.resposta || 'Encontrei uma opção para você.');
            if (dados.tipo === 'redirect' && dados.url) {
                setTimeout(() => openUrl(dados.url), 700);
            }
        } catch (err) {
            setMessage('Erro ao consultar IA. Tente comandos como: importar planilha, ver chamados, abrir sala A01.');
        }
    }

    function processarComandoAgente(comando) {
    if (modoAgente === 'ia') {
        consultarIAAgente(comando);
        return;
    }

    const texto = comando.toLowerCase();

    // restante do código local...
}

    function processarComandoAgente(texto) {
        const t = String(texto || '').toLowerCase();

        // comandos diretos
        if (t.includes('importar') || t.includes('planilha')) {
            handleActionKey('importar');
            return;
        }
        if (t.includes('alter') || t.includes('importa')) {
            handleActionKey('alteracoes');
            return;
        }
        if (t.includes('chamad') || t.includes('manuten')) {
            handleActionKey('chamados');
            return;
        }

        // variações por bloco
        if (t.includes('bloco a')) { abrirSala('A', 'A01'); return; }
        if (t.includes('bloco c')) { abrirSala('C', 'C01'); return; }
        if (t.includes('bloco d')) { abrirSala('D', 'D01'); return; }
        if (t.includes('bloco e')) { abrirSala('E', 'E01'); return; }

        // tentar extrair sala (A01, C1 etc.)
        if (abrirSalaPorTexto(t)) return;

        // fallback: consultar IA
        consultarIAAgente(texto);
    }

    // Inicialização e binding de eventos
    function initAgent() {
        const box = $id(SELECTORS.box);
        if (!box) return;

        const header = $id(SELECTORS.header);
        const form = $id(SELECTORS.form);
        const input = $id(SELECTORS.input);

        // header toggle
        if (header) header.addEventListener('click', () => {
            box.classList.toggle('closed');
            if (!box.classList.contains('closed') && input) input.focus();
        });

        // action buttons
        qsAll(SELECTORS.actionButtons).forEach(btn => {
            btn.addEventListener('click', (ev) => {
                const action = btn.getAttribute('data-action') || btn.textContent;
                if (!box.classList.contains('closed')) {
                    setMessage('Abrindo ' + action + '...');
                } else {
                    box.classList.remove('closed');
                }
                handleActionKey(action);
            });
        });

        // submit
        if (form) form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            if (!input) return;
            const val = input.value.trim();
            if (!val) return setMessage('Digite uma ação para continuar.');
            input.value = '';
            processarComandoAgente(val);
        });

        // abrir automaticamente em index
        const paginaAtual = window.location.pathname.split('/').pop();
        if (paginaAtual === '' || paginaAtual === 'index.php') {
            box.classList.remove('closed');
            setMessage('Olá! Bem-vindo ao Estoque COC. Posso abrir salas, importar planilhas, mostrar chamados ou acessar as últimas alterações.');
        }
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAgent);
    } else {
        initAgent();
    }

})();