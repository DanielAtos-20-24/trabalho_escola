<?php
require_once __DIR__ . '/dados/salas.php';

$bloco = $_GET['bloco'] ?? '';
$salaCodigo = $_GET['sala'] ?? '';
$equipamentoNome = $_GET['equipamento'] ?? '';

$sala = obterSala($bloco, $salaCodigo);
$equipamentos = $sala ? equipamentosDaSala($sala) : [];

$equipamentoAtual = null;

foreach ($equipamentos as $equipamento) {
    if (strtolower($equipamento['nome']) === strtolower($equipamentoNome)) {
        $equipamentoAtual = $equipamento;
        break;
    }
}

$arquivoManutencoes = __DIR__ . '/dados/manutencoes.json';

if (!file_exists($arquivoManutencoes)) {
    file_put_contents($arquivoManutencoes, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$manutencoes = json_decode(file_get_contents($arquivoManutencoes), true) ?? [];

$historico = array_filter($manutencoes, function ($item) use ($bloco, $salaCodigo, $equipamentoNome) {
    return ($item['bloco'] ?? '') === $bloco
        && ($item['sala'] ?? '') === $salaCodigo
        && strtolower($item['equipamento'] ?? '') === strtolower($equipamentoNome);
});
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manutenção - <?= e($equipamentoNome) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-icon">
                    <i class="bi bi-tools"></i>
                </div>
                <div>
                    <h1>Manutenção</h1>
                    <span>Controle de Equipamentos</span>
                </div>
            </div>

            <div class="menu-title">Menu</div>

            <a href="index.php" class="menu-link">
                <i class="bi bi-grid-1x2"></i> Setores
            </a>

            <a href="sala.php?bloco=<?= e($bloco) ?>&sala=<?= e($salaCodigo) ?>" class="menu-link active">
                <i class="bi bi-arrow-left"></i> Voltar para sala
            </a>
            <?php include __DIR__ . '/includes/agent.php'; ?>
        </aside>

        <main class="content">
            <div class="page-header">
                <div>
                    <h2>Manutenção</h2>
                    <p>
                        <?= e(nomeBloco($bloco)) ?> -
                        <?= e($sala['titulo'] ?? $salaCodigo) ?> -
                        <?= e($equipamentoNome) ?>
                    </p>
                </div>

                <a href="sala.php?bloco=<?= e($bloco) ?>&sala=<?= e($salaCodigo) ?>" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>

            <?php if (!$sala || !$equipamentoAtual): ?>
                <div class="empty-state">
                    Equipamento não encontrado.
                    <a href="index.php">Voltar para os setores</a>.
                </div>
            <?php else: ?>

                <section class="maintenance-card">
                    <div class="maintenance-header">
                        <div class="maintenance-icon">
                            <i class="bi <?= e($equipamentoAtual['icone']) ?>"></i>
                        </div>

                        <div>
                            <h3><?= e($equipamentoAtual['nome']) ?></h3>
                            <p><?= e($equipamentoAtual['descricao']) ?></p>
                        </div>
                    </div>

                    <form action="salvar_manutencao.php" method="POST" class="maintenance-form">
                        <input type="hidden" name="bloco" value="<?= e($bloco) ?>">
                        <input type="hidden" name="sala" value="<?= e($salaCodigo) ?>">
                        <input type="hidden" name="equipamento" value="<?= e($equipamentoNome) ?>">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Data</label>
                                <input type="date" name="data" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tipo de manutenção</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="">Selecione</option>
                                    <option value="Preventiva">Preventiva</option>
                                    <option value="Corretiva">Corretiva</option>
                                    <option value="Troca de peça">Troca de peça</option>
                                    <option value="Limpeza">Limpeza</option>
                                    <option value="Teste">Teste</option>
                                    <option value="Configuração">Configuração</option>
                                    <option value="Retirada para análise">Retirada para análise</option>
                                    <option value="Baixa do equipamento">Baixa do equipamento</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="Aberta">Aberta</option>
                                    <option value="Em andamento">Em andamento</option>
                                    <option value="Aguardando peça">Aguardando peça</option>
                                    <option value="Resolvida">Resolvida</option>
                                    <option value="Sem solução">Sem solução</option>
                                    <option value="Encaminhada">Encaminhada</option>
                                    <option value="Cancelada">Cancelada</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Problema identificado</label>
                                <textarea name="problema" class="form-control" rows="3" required></textarea>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Serviço realizado</label>
                                <textarea name="servico" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Peça trocada</label>
                                <input type="text" name="peca" class="form-control" placeholder="Ex: cabo HDMI, memória RAM, fonte...">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Técnico responsável</label>
                                <input type="text" name="tecnico" class="form-control" placeholder="Nome do responsável">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Observações</label>
                                <textarea name="observacoes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="maintenance-actions">
                            <button type="submit" class="action-btn warning">
                                <i class="bi bi-save"></i> Salvar manutenção
                            </button>
                        </div>
                    </form>
                </section>

                <section class="maintenance-card mt-4">
                    <h3>Histórico de manutenções</h3>

                    <?php if (empty($historico)): ?>
                        <p class="text-secondary mt-3">Nenhuma manutenção registrada para este equipamento.</p>
                    <?php else: ?>
                        <div class="table-responsive mt-3">
                            <table class="table table-dark table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Tipo</th>
                                        <th>Problema</th>
                                        <th>Serviço</th>
                                        <th>Peça</th>
                                        <th>Técnico</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_reverse($historico) as $item): ?>
                                        <tr>
                                            <td><?= e($item['data']) ?></td>
                                            <td><?= e($item['tipo']) ?></td>
                                            <td><?= e($item['problema']) ?></td>
                                            <td><?= e($item['servico']) ?></td>
                                            <td><?= e($item['peca']) ?></td>
                                            <td><?= e($item['tecnico']) ?></td>
                                            <td><?= e($item['status']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>

            <?php endif; ?>
        </main>
    </div>
<script src="assets/js/agente.js"></script>
</body>
</html>