<div id="agentBox" class="agent-box closed">
    <div class="agent-header" onclick="toggleAgent()">
        <div class="agent-avatar">
            <div class="agent-face">
                <span class="agent-eye"></span>
                <span class="agent-eye"></span>
            </div>
            <span class="agent-antenna"></span>
        </div>

        <div>
            <strong>Assistente COC</strong>
            <div class="agent-summary">Escolha como deseja consultar</div>
        </div>
    </div>

    <div class="agent-mode">
        <button type="button" id="modoLocal" class="agent-mode-btn active" onclick="definirModoAgente('local')">
            Local
        </button>

        <button type="button" id="modoIA" class="agent-mode-btn" onclick="definirModoAgente('ia')">
            IA
        </button>
    </div>

    <div id="agentMessage" class="agent-message">
        Olá! Escolha se deseja consultar localmente ou pela IA.
    </div>

    <div class="agent-actions">
        <button type="button" onclick="agentGo('importar')">
            <i class="bi bi-file-earmark-arrow-up"></i> Importar planilha
        </button>

        <button type="button" onclick="agentGo('alteracoes')">
            <i class="bi bi-clock-history"></i> Últimas alterações
        </button>

        <button type="button" onclick="agentGo('chamados')">
            <i class="bi bi-exclamation-circle"></i> Salas com mais chamados
        </button>

        <button type="button" onclick="agentGo('A01')">
            <i class="bi bi-door-open"></i> Abrir sala A01
        </button>
    </div>

    <form class="agent-form" onsubmit="agentSubmit(event)">
        <input id="agentInput" type="text" placeholder="Ex: abrir sala D02">
        <button type="submit">
            <i class="bi bi-send"></i>
        </button>
    </form>
</div>