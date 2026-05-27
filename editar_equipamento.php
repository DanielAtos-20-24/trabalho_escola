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

if (!$sala || !$equipamentoAtual) {
    die('Equipamento não encontrado.');
}

$especificacoes = $equipamentoAtual['especificacoes'] ?? [];
$fotoAtual = $equipamentoAtual['foto'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Equipamento</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <h1>Editar</h1>
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
                <h2>Editar equipamento</h2>
                <p><?= e(nomeBloco($bloco)) ?> - <?= e($sala['titulo']) ?> - <?= e($equipamentoNome) ?></p>
            </div>
        </div>

        <section class="maintenance-card">
            <div class="maintenance-header">
                <div class="maintenance-icon">
                    <i class="bi <?= e($equipamentoAtual['icone']) ?>"></i>
                </div>

                <div>
                    <h3><?= e($equipamentoAtual['nome']) ?></h3>
                    <p>Atualize dados, situação e foto do equipamento.</p>
                </div>
            </div>

            <form action="salvar_equipamento.php" method="POST" enctype="multipart/form-data" class="maintenance-form">
                <input type="hidden" name="bloco" value="<?= e($bloco) ?>">
                <input type="hidden" name="sala" value="<?= e($salaCodigo) ?>">
                <input type="hidden" name="equipamento" value="<?= e($equipamentoNome) ?>">
                <input type="hidden" name="foto_atual" value="<?= e($fotoAtual) ?>">

                <div class="row g-3">
                    <?php foreach ($especificacoes as $campo => $valor): ?>
                        <div class="col-md-6">
                            <label class="form-label"><?= e($campo) ?></label>
                            <input type="text"
                                   name="especificacoes[<?= e($campo) ?>]"
                                   class="form-control"
                                   value="<?= e($valor) ?>">
                        </div>
                    <?php endforeach; ?>

                    <div class="col-md-12">
                        <label class="form-label">Foto atual</label>

                        <?php if (!empty($fotoAtual)): ?>
                            <div class="edit-photo-preview">
                                <img src="<?= e($fotoAtual) ?>" alt="Foto atual">
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                Sem foto cadastrada.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Nova foto</label>
                        <input type="file" name="nova_foto" class="form-control" accept="image/*">
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <label class="remove-photo-check">
                            <input type="checkbox" name="remover_foto" value="1">
                            Remover foto atual
                        </label>
                    </div>
                </div>

                <div class="maintenance-actions">
                    <button type="submit" class="action-btn warning">
                        <i class="bi bi-save"></i> Salvar alterações
                    </button>
                </div>
            </form>
        </section>
    </main>
</div>
</body>
</html>