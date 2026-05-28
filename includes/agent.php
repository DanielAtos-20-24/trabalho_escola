<div class="agent-box closed" id="agentBox">
    <div class="agent-header" id="agentHeader">
        <i class="bi bi-robot"></i>
        <div>
            <strong>Agente virtual</strong>
            <div class="agent-summary">Clique para abrir o agente e solicitar algo</div>
        </div>
    </div>

    <div class="agent-message" id="agentMessage">Escolha uma opção ou digite uma ação.</div>

    <div class="agent-actions">
        <button type="button" data-action="index">Ver setores</button>
        <button type="button" data-action="importar planilha">Importar planilha</button>
        <button type="button" data-action="manutencao">Ver manutenções</button>
        <button type="button" data-action="dados">Abrir pasta dados</button>
        <button type="button" data-action="uploads">Abrir pasta uploads</button>
    </div>

    <form id="agentForm" class="agent-form">
        <input id="agentInput" type="text" placeholder="Digite por exemplo: importar planilha" autocomplete="off" />
        <button type="submit">Ir</button>
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
