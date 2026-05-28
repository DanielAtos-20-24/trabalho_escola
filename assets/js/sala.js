document.addEventListener('DOMContentLoaded', function() {
    // Utility: add small transition classes
    const TRANSITION_MS = 220;

    function addHide(el) {
        if (!el) return;
        el.classList.add('transition-fade', 'hidden');
    }

    function removeHide(el) {
        if (!el) return;
        el.classList.remove('hidden');
    }

    function mostrarFoto() {
        const foto = document.getElementById('detalheFoto');
        const placeholder = document.getElementById('detalheFotoPlaceholder');
        if (!foto || !placeholder) return;
        foto.style.display = 'block';
        placeholder.classList.remove('active');
    }

    function mostrarPlaceholderFoto() {
        const foto = document.getElementById('detalheFoto');
        const placeholder = document.getElementById('detalheFotoPlaceholder');
        if (!foto || !placeholder) return;
        foto.removeAttribute('src');
        foto.style.display = 'none';
        placeholder.classList.add('active');
    }

    function preencherEspecificacoes(botao) {
        const areaEspecificacoes = document.getElementById('detalheEspecificacoes');
        if (!areaEspecificacoes) return;
        const especificacoes = JSON.parse(botao.getAttribute('data-especificacoes') || '{}');
        areaEspecificacoes.innerHTML = '';
        Object.entries(especificacoes).forEach(function([titulo, valor]) {
            const item = document.createElement('div');
            item.className = 'spec-item';
            item.innerHTML = '<span>' + titulo + '</span><strong>' + (valor || '') + '</strong>';
            areaEspecificacoes.appendChild(item);
        });
    }

    function preencherHistorico(botao) {
        const areaHistorico = document.getElementById('historicoManutencoes');
        if (!areaHistorico) return;
        const historico = JSON.parse(botao.getAttribute('data-historico') || '[]');
        areaHistorico.innerHTML = '';
        if (!historico || historico.length === 0) {
            areaHistorico.innerHTML = '<p class="history-empty">Nenhuma manutenção registrada para este equipamento.</p>';
            return;
        }
        historico.slice().reverse().forEach(function(entry) {
            const registro = document.createElement('div');
            registro.className = 'history-item';

            const titulo = entry.titulo || (entry.tipo ? entry.tipo : entry.descricao) || 'Registro';
            const data = entry.data || entry.date || '';
            const tipo = entry.tipo || '';
            const tecnico = entry.tecnico || '';
            const problema = entry.problema || '';
            const servico = entry.servico || '';
            const status = entry.status || '';

            const badge = document.createElement('div');
            badge.className = 'history-badge';
            badge.textContent = (status || tipo || titulo).slice(0,3).toUpperCase();

            const content = document.createElement('div');
            content.className = 'history-content';

            const top = document.createElement('div');
            top.className = 'history-top';
            top.innerHTML = '<div><p class="history-title">' + titulo + '</p><div class="history-meta">' + (tipo ? tipo + (tecnico ? ' • ' + tecnico : '') : tecnico) + '</div></div>';

            const right = document.createElement('div');
            right.className = 'history-right';
            right.innerHTML = (data ? '<span class="history-date">' + data + '</span>' : '') + (status ? '<span class="status-badge status-' + status.replace(/[^a-zA-Z0-9\s]/g,'').replace(/\s/g,'-') + '">' + status + '</span>' : '');

            top.appendChild(right);

            const body = document.createElement('div');
            body.className = 'history-body';
            body.innerHTML = '<p><strong>Problema:</strong> ' + (problema || '-') + '</p>' + (servico ? '<p><strong>Serviço:</strong> ' + servico + '</p>' : '');

            content.appendChild(top);
            content.appendChild(body);

            registro.appendChild(badge);
            registro.appendChild(content);

            areaHistorico.appendChild(registro);
        });
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value || '';
    }

    function mostrarDetalhes(botao) {
        if (!botao) return;

        document.querySelectorAll('.equipment-card').forEach(function(card) {
            card.classList.remove('selected');
        });
        botao.classList.add('selected');

        const painel = document.getElementById('detalhesEquipamento');
        const foto = document.getElementById('detalheFoto');
        const iconePlaceholder = document.getElementById('detalheIconePlaceholder');
        const caminhoFoto = botao.getAttribute('data-foto');

        if (painel) painel.classList.add('active');
        if (iconePlaceholder) iconePlaceholder.className = 'bi ' + botao.getAttribute('data-icone');

        if (foto) {
            foto.onload = mostrarFoto;
            foto.onerror = mostrarPlaceholderFoto;
            foto.alt = 'Foto do ' + (botao.getAttribute('data-nome') || 'equipamento');
            if (caminhoFoto) {
                foto.src = caminhoFoto;
            } else {
                mostrarPlaceholderFoto();
            }
        }

        setText('detalheNome', botao.getAttribute('data-nome'));
        setText('detalheDescricao', botao.getAttribute('data-descricao'));
        setText('detalheProcessador', botao.getAttribute('data-processador'));
        setText('detalheMemoria', botao.getAttribute('data-memoria'));
        setText('detalheArmazenamento', botao.getAttribute('data-armazenamento'));
        setText('detalhePlacaVideo', botao.getAttribute('data-placa-video'));
        setText('detalhePatrimonio', botao.getAttribute('data-patrimonio'));
        setText('detalheSituacao', botao.getAttribute('data-situacao'));

        preencherEspecificacoes(botao);
        preencherHistorico(botao);

        const parametros = new URLSearchParams(window.location.search);
        const bloco = parametros.get('bloco') || '';
        const sala = parametros.get('sala') || '';
        const equipamento = botao.getAttribute('data-nome') || '';
        const btnManutencao = document.getElementById('btnManutencao');
        const btnEditarEquipamento = document.getElementById('btnEditarEquipamento');
        const btnMoverEquipamento = document.getElementById('btnMoverEquipamento');

        if (btnEditarEquipamento) {
            btnEditarEquipamento.href =
                'editar_equipamento.php?bloco=' + encodeURIComponent(bloco) +
                '&sala=' + encodeURIComponent(sala) +
                '&equipamento=' + encodeURIComponent(equipamento);
        }

        if (btnMoverEquipamento) {
            btnMoverEquipamento.href =
                'mover_equipamento.php?bloco=' + encodeURIComponent(bloco) +
                '&sala=' + encodeURIComponent(sala) +
                '&equipamento=' + encodeURIComponent(equipamento);
        }
        if (btnManutencao) {
            btnManutencao.href = 'manutencao.php?bloco=' + encodeURIComponent(bloco) + '&sala=' + encodeURIComponent(sala) + '&equipamento=' + encodeURIComponent(equipamento);
        }

        if (painel && painel.scrollIntoView) {
            painel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Initialize bindings for equipment cards and nav arrows
    function initBindings() {
        document.querySelectorAll('.equipment-card').forEach(function(card) {
            card.removeEventListener('click', card._clickHandler || function(){});
            const handler = function() { mostrarDetalhes(card); };
            card.addEventListener('click', handler);
            card._clickHandler = handler;
        });

        document.querySelectorAll('.nav-arrow').forEach(function(anchor) {
            anchor.removeEventListener('click', anchor._navHandler || function(){});
            const handler = function(e) {
                if (!anchor.dataset.bloco || !anchor.dataset.sala) return; // fallback to normal link
                e.preventDefault();
                const bloco = anchor.dataset.bloco;
                const salaCode = anchor.dataset.sala;
                loadSala(bloco, salaCode, true);
            };
            anchor.addEventListener('click', handler);
            anchor._navHandler = handler;
        });
    }

    // Fetch new sala page and replace fragments (AJAX navigation)
    function loadSala(bloco, salaCode, push) {
        const url = 'sala.php?bloco=' + encodeURIComponent(bloco) + '&sala=' + encodeURIComponent(salaCode);

        const grid = document.querySelector('.equipment-grid');
        const details = document.getElementById('detalhesEquipamento');
        const pageHeader = document.querySelector('.page-header');

        addHide(grid);
        addHide(details);

        fetch(url).then(function(resp) { return resp.text(); }).then(function(html) {
            const tmp = document.createElement('div'); tmp.innerHTML = html;
            const newGrid = tmp.querySelector('.equipment-grid');
            const newDetails = tmp.querySelector('#detalhesEquipamento');
            const newTitle = tmp.querySelector('.page-header');

            if (newGrid && grid) grid.replaceWith(newGrid);
            if (newDetails && details) details.replaceWith(newDetails);
            if (newTitle && pageHeader) pageHeader.replaceWith(newTitle);

            // Update URL and document title
            if (push) {
                history.pushState({ bloco: bloco, sala: salaCode }, '', url);
            }

            // small delay to allow DOM insertion, then reveal
            setTimeout(function() {
                const ng = document.querySelector('.equipment-grid');
                const nd = document.getElementById('detalhesEquipamento');
                if (ng) removeHide(ng);
                if (nd) removeHide(nd);
            }, 50);

            // Re-init bindings after replacement
            setTimeout(initBindings, TRANSITION_MS + 60);
        }).catch(function() {
            // on error, fallback to full navigation
            window.location.href = url;
        });
    }

    // Handle browser back/forward
    window.addEventListener('popstate', function(e) {
        const state = e.state || {};
        if (state.bloco && state.sala) {
            loadSala(state.bloco, state.sala, false);
        }
    });

    // Initial bindings
// Initial bindings
initBindings();

const params = new URLSearchParams(window.location.search);
const equipamentoUrl = params.get('equipamento');

if (equipamentoUrl) {
    const equipamentoNormalizado = equipamentoUrl.toLowerCase().trim();
    let encontrouEquipamento = false;

    document.querySelectorAll('.equipment-card').forEach(function(card) {
        const nome = (card.dataset.nome || '').toLowerCase().trim();

        if (nome === equipamentoNormalizado) {
            encontrouEquipamento = true;
            mostrarDetalhes(card);

            setTimeout(function() {
                card.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }, 250);
        }
    });

    if (!encontrouEquipamento) {
        const firstCard = document.querySelector('.equipment-card');
        if (firstCard) mostrarDetalhes(firstCard);
    }
} else {
    const firstCard = document.querySelector('.equipment-card');
    if (firstCard) mostrarDetalhes(firstCard);
}

