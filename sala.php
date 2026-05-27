<?php
require_once __DIR__ . '/dados/salas.php';

$setores = salasSistema();
$sala = obterSala($_GET['bloco'] ?? '', $_GET['sala'] ?? '');
$equipamentos = $sala ? equipamentosDaSala($sala) : [];
$blocoAtual = $sala['bloco'] ?? '';
$tituloPagina = $sala ? $sala['titulo'] . ' - ' . nomeBloco($sala['bloco']) : 'Sala não encontrada';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-icon"><i class="bi bi-box-seam"></i></div>
                <div>
                    <h1>Estoque COC</h1>
                    <span>Controle de Equipamentos</span>
                </div>
            </div>

            <div class="menu-title">Menu</div>
            <a href="index.php" class="menu-link"><i class="bi bi-grid-1x2"></i> Setores</a>
            <?php foreach ($setores as $setor): ?>
                <a href="<?= e(urlSala($setor['salas'][0])) ?>" class="menu-link <?= $setor['id'] === $blocoAtual ? 'active' : '' ?>">
                    <i class="bi <?= e($setor['icone']) ?>"></i> <?= e($setor['nome']) ?>
                </a>
            <?php endforeach; ?>
        </aside>

        <main class="content">
            <div class="page-header">
                <div>
                    <h2><?= e($sala['titulo'] ?? 'Sala não encontrada') ?></h2>
                    <p><?= $sala ? e(nomeBloco($sala['bloco'])) . ' - clique em um equipamento para visualizar as especificações abaixo.' : 'Confira o bloco e a sala informados.' ?></p>
                </div>

                <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i> Voltar</a>
            </div>

            <?php if (!$sala): ?>
                <div class="empty-state">
                    Não encontramos essa sala. <a href="index.php">Voltar para os setores</a>.
                </div>
            <?php else: ?>
                <div class="equipment-grid">
                    <?php foreach ($equipamentos as $item): ?>
                        <button type="button"
                                class="equipment-card"
                                data-nome="<?= e($item['nome']) ?>"
                                data-icone="<?= e($item['icone']) ?>"
                                data-foto="<?= e($item['foto']) ?>"
                                data-descricao="<?= e($item['descricao']) ?>"
                                data-processador="<?= e($item['processador']) ?>"
                                data-memoria="<?= e($item['memoria']) ?>"
                                data-armazenamento="<?= e($item['armazenamento']) ?>"
                                data-placa-video="<?= e($item['placa_video']) ?>"
                                data-patrimonio="<?= e($item['patrimonio']) ?>"
                                data-situacao="<?= e($item['situacao']) ?>">
                            <div class="equipment-icon"><i class="bi <?= e($item['icone']) ?>"></i></div>
                            <div class="equipment-name"><?= e($item['nome']) ?></div>
                            <div class="equipment-status"><?= e($item['situacao']) ?></div>
                        </button>
                    <?php endforeach; ?>
                </div>

                <section id="detalhesEquipamento" class="details-panel">
                    <div class="details-photo">
                        <img id="detalheFoto" src="" alt="Foto do equipamento">
                        <div id="detalheFotoPlaceholder" class="photo-placeholder">
                            <i id="detalheIconePlaceholder" class="bi bi-image"></i>
                            <span>Sem foto cadastrada</span>
                        </div>
                    </div>

                    <div class="details-info">
                        <h3 id="detalheNome">Equipamento</h3>
                        <p id="detalheDescricao" class="details-description"></p>

                        <div class="spec-grid">
                            <div class="spec-item"><span>Processador</span><strong id="detalheProcessador"></strong></div>
                            <div class="spec-item"><span>Memória</span><strong id="detalheMemoria"></strong></div>
                            <div class="spec-item"><span>Armazenamento</span><strong id="detalheArmazenamento"></strong></div>
                            <div class="spec-item"><span>Placa de vídeo</span><strong id="detalhePlacaVideo"></strong></div>
                            <div class="spec-item"><span>Patrimônio</span><strong id="detalhePatrimonio"></strong></div>
                            <div class="spec-item"><span>Situação</span><strong id="detalheSituacao"></strong></div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>

    <script src="assets/js/sala.js"></script>
</body>
</html>