<?php
require_once __DIR__ . '/dados/salas.php';

$bloco = $_GET['bloco'] ?? '';
$salaCodigo = $_GET['sala'] ?? '';
$equipamentoNome = $_GET['equipamento'] ?? '';

$sala = obterSala($bloco, $salaCodigo);
$setores = salasSistema();

if (!$sala || empty($equipamentoNome)) {
    die('Equipamento não encontrado.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Mover Equipamento</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">
                <i class="bi bi-arrow-left-right"></i>
            </div>
            <div>
                <h1>Mover</h1>
                <span>Equipamento</span>
            </div>
        </div>

        <div class="menu-title">Menu</div>

        <a href="sala.php?bloco=<?= e($bloco) ?>&sala=<?= e($salaCodigo) ?>" class="menu-link active">
            <i class="bi bi-arrow-left"></i> Voltar para sala
        </a>
    </aside>

    <main class="content">
        <div class="page-header">
            <div>
                <h2>Mover equipamento</h2>
                <p><?= e($equipamentoNome) ?> atualmente em <?= e($sala['titulo']) ?> - <?= e(nomeBloco($bloco)) ?></p>
            </div>
        </div>

        <section class="maintenance-card">
            <form action="salvar_movimentacao.php" method="POST" class="maintenance-form">
                <input type="hidden" name="bloco_origem" value="<?= e($bloco) ?>">
                <input type="hidden" name="sala_origem" value="<?= e($salaCodigo) ?>">
                <input type="hidden" name="equipamento" value="<?= e($equipamentoNome) ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nova sala</label>
                        <select name="sala_destino" class="form-select" required>
                            <option value="">Selecione a nova sala</option>

                            <?php foreach ($setores as $setor): ?>
                                <optgroup label="<?= e($setor['nome']) ?>">
                                    <?php foreach ($setor['salas'] as $salaDestino): ?>
                                        <option value="<?= e($setor['id'] . '|' . $salaDestino['codigo']) ?>">
                                            <?= e($setor['nome']) ?> - <?= e($salaDestino['titulo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Responsável</label>
                        <input type="text" name="responsavel" class="form-control" placeholder="Nome de quem realizou a movimentação">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Motivo da movimentação</label>
                        <textarea name="motivo" class="form-control" rows="4" required></textarea>
                    </div>
                </div>

                <div class="maintenance-actions">
                    <button type="submit" class="action-btn info">
                        <i class="bi bi-arrow-left-right"></i> Confirmar movimentação
                    </button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>