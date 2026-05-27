<?php
require_once __DIR__ . '/dados/salas.php';

$setores = salasSistema();
$sala = obterSala($_GET['bloco'] ?? '', $_GET['sala'] ?? '');
$equipamentos = $sala ? equipamentosDaSala($sala) : [];
$blocoAtual = $sala['bloco'] ?? '';
$tituloPagina = $sala ? $sala['titulo'] . ' - ' . nomeBloco($sala['bloco']) : 'Sala não encontrada';

$arquivoManutencoes = __DIR__ . '/dados/manutencoes.json';

if (!file_exists($arquivoManutencoes)) {
    file_put_contents($arquivoManutencoes, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$manutencoes = json_decode(file_get_contents($arquivoManutencoes), true) ?? [];

// Navegação entre salas do mesmo setor
$salasDoSetor = [];
$indiceSalaAtual = null;
foreach (salasSistema() as $setorInfo) {
    if (($setorInfo['id'] ?? '') === ($sala['bloco'] ?? '')) {
        $salasDoSetor = array_values($setorInfo['salas']);
        break;
    }
}
if ($sala) {
    foreach ($salasDoSetor as $i => $s) {
        if (($s['codigo'] ?? '') === ($sala['codigo'] ?? '')) {
            $indiceSalaAtual = $i;
            break;
        }
    }
}
$salaPrev = null;
$salaNext = null;
if ($indiceSalaAtual !== null) {
    if ($indiceSalaAtual > 0) {
        $salaPrev = $salasDoSetor[$indiceSalaAtual - 1];
    }
    if ($indiceSalaAtual < count($salasDoSetor) - 1) {
        $salaNext = $salasDoSetor[$indiceSalaAtual + 1];
    }
}
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
                    <div style="display:flex;align-items:center;gap:12px;">
                        <h2 style="margin:0;"><?= e($sala['titulo'] ?? 'Sala não encontrada') ?></h2>
                        <div class="room-nav">
                            <?php if ($salaPrev): ?>
                                <a href="<?= e(urlSala($salaPrev)) ?>" class="nav-arrow" title="Sala anterior" data-bloco="<?= e($salaPrev['bloco']) ?>" data-sala="<?= e($salaPrev['codigo']) ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            <?php else: ?>
                                <span class="nav-arrow disabled"><i class="bi bi-chevron-left"></i></span>
                            <?php endif; ?>

                            <?php if ($salaNext): ?>
                                <a href="<?= e(urlSala($salaNext)) ?>" class="nav-arrow" title="Próxima sala" data-bloco="<?= e($salaNext['bloco']) ?>" data-sala="<?= e($salaNext['codigo']) ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="nav-arrow disabled"><i class="bi bi-chevron-right"></i></span>
                            <?php endif; ?>
                        </div>
                    </div>

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
                        <?php
                            $historicoEquipamento = array_values(array_filter($manutencoes, function ($manutencao) use ($sala, $item) {
                                return ($manutencao['bloco'] ?? '') === ($sala['bloco'] ?? '')
                                    && ($manutencao['sala'] ?? '') === ($sala['codigo'] ?? '')
                                    && strtolower($manutencao['equipamento'] ?? '') === strtolower($item['nome'] ?? '');
                            }));
                            ?>

                            <button type="button"
                                    class="equipment-card"
                                    data-nome="<?= e($item['nome']) ?>"
                                    data-icone="<?= e($item['icone']) ?>"
                                    data-foto="<?= e($item['foto']) ?>"
                                    data-descricao="<?= e($item['descricao']) ?>"
                                    data-especificacoes='<?= e(json_encode($item["especificacoes"], JSON_UNESCAPED_UNICODE)) ?>'
                                    data-historico='<?= e(json_encode($historicoEquipamento, JSON_UNESCAPED_UNICODE)) ?>'>
                                <div class="equipment-icon">
                                    <i class="bi <?= e($item['icone']) ?>"></i>
                                </div>

                                <div class="equipment-name">
                                    <?= e($item['nome']) ?>
                                </div>

                                <div class="equipment-status">
                                    <?= e($item['situacao']) ?>
                                </div>
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

                       <div id="detalheEspecificacoes" class="spec-grid"></div>
                       <div class="equipment-history">
                            <div class="history-header">
                                <h4>Histórico de manutenções</h4>
                            </div>

                            <div id="historicoManutencoes" class="history-list">
                                <p class="history-empty">Selecione um equipamento para visualizar o histórico.</p>
                            </div>
                        </div>

                        <div class="details-actions">
                            <a id="btnManutencao" href="#" class="action-btn warning">
                                <i class="bi bi-tools"></i>
                                Registrar manutenção
                            </a>

                            <a id="btnTrocaPecas" href="#" class="action-btn info">
                                <i class="bi bi-cpu"></i>
                                Troca de peças
                            </a>

                            <a id="btnEditarEquipamento" href="#" class="action-btn warning">
                                <i class="bi bi-pencil-square"></i>
                                Editar equipamento
                            </a>

                            <a id="btnMoverEquipamento" href="#" class="action-btn info">
                                <i class="bi bi-arrow-left-right"></i>
                                Mover equipamento
                            </a>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>

    <script src="assets/js/sala.js"></script>
</body>
</html>