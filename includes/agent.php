<div id="agentBox" class="agent-box closed">
    <div class="agent-header" onclick="toggleAgent()">
        <i class="bi bi-robot"></i>
        <div>
            <strong>Agente virtual</strong>
            <div class="agent-summary">Digite o que deseja fazer</div>
        </div>
    </div>

    <div id="agentMessage" class="agent-message">
        Olá! O que deseja fazer? Posso abrir salas, importar planilhas, mostrar chamados ou acessar alterações.
    </div>

    <div class="agent-actions">
        <button type="button" onclick="agentGo('importar')">Importar planilha</button>
        <button type="button" onclick="agentGo('alteracoes')">Últimas alterações</button>
        <button type="button" onclick="agentGo('chamados')">Salas com mais chamados</button>
        <button type="button" onclick="agentGo('A01')">Abrir sala A01</button>
    </div>

    <form class="agent-form" onsubmit="agentSubmit(event)">
        <input id="agentInput" type="text" placeholder="Ex: abrir sala D02">
        <button type="submit">
            <i class="bi bi-send"></i>
        </button>
    </form>
</div>

<script>
    (function () {
        const agentBox = document.getElementById('agentBox');
        const agentHeader = document.getElementById('agentHeader');
        const agentMessage = document.getElementById('agentMessage');
        const agentInput = document.getElementById('agentInput');
        const agentForm = document.getElementById('agentForm');

        const actions = {
            index: 'index.php',
            setores: 'index.php',
            importar: 'importar_planilha.php',
            'importar planilha': 'importar_planilha.php',
            manutencao: 'manutencao.php',
            manutenção: 'manutencao.php',
            dados: 'file:///c:/xampp/htdocs/programa_equipamentos/dados/',
            uploads: 'file:///c:/xampp/htdocs/programa_equipamentos/uploads/'
        };

        function runAction(key) {
            const action = actions[key.toLowerCase().trim()];
            if (!action) {
                agentMessage.textContent = 'Não encontrei essa ação. Tente usar uma opção ou digitar algo como "importar planilha".';
                return;
            }
            agentMessage.textContent = 'Executando ação...';
            if (action.startsWith('file:///')) {
                window.open(action, '_blank');
            } else {
                window.location.href = action;
            }
        }

        function toggleAgent() {
            agentBox.classList.toggle('closed');
            if (agentBox.classList.contains('closed')) {
                agentMessage.textContent = 'Clique para abrir o agente e solicitar algo.';
            } else {
                agentMessage.textContent = 'Escolha uma opção ou digite uma ação.';
                agentInput.focus();
            }
        }

        agentHeader.addEventListener('click', toggleAgent);

        document.querySelectorAll('.agent-actions button').forEach(button => {
            button.addEventListener('click', () => {
                const action = button.getAttribute('data-action');
                if (agentBox.classList.contains('closed')) {
                    agentBox.classList.remove('closed');
                }
                agentMessage.textContent = 'Abrindo ' + button.textContent + '...';
                runAction(action);
            });
        });

        agentForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const value = agentInput.value.trim();
            if (!value) {
                agentMessage.textContent = 'Digite uma ação para continuar.';
                return;
            }
            if (agentBox.classList.contains('closed')) {
                agentBox.classList.remove('closed');
            }
            runAction(value);
        });
    })();
</script>
