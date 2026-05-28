<?php
require_once __DIR__ . '/dados/salas.php';

$setores = salasSistema();

function carregarJson($caminho)
{
    if (!file_exists($caminho)) {
        return [];
    }

    return json_decode(file_get_contents($caminho), true) ?? [];
}

$importacoes = carregarJson(__DIR__ . '/dados/importacoes.json');
$ultimasImportacoes = array_slice(array_reverse($importacoes), 0, 5);

// --- Bloco 1: preparação dos dados ---
$arquivoManutencoes = __DIR__ . '/dados/manutencoes.json';
if (!file_exists($arquivoManutencoes)) {
    file_put_contents($arquivoManutencoes, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$manutencoes = json_decode(file_get_contents($arquivoManutencoes), true) ?? [];

$openStatuses = ['ABERTA', 'EM ANDAMENTO', 'AGUARDANDO PEÇA', 'ENCAMINHADA'];
$openEquipKeys = [];
$manutencoesPorSala = [];

foreach ($manutencoes as $m) {
    $mb = $m['bloco'] ?? '';
    $ms = $m['sala'] ?? '';
    $me = strtolower($m['equipamento'] ?? '');
    $status = strtoupper($m['status'] ?? '');

    $keySala = $mb . '|' . $ms;
    if (!isset($manutencoesPorSala[$keySala])) {
        $manutencoesPorSala[$keySala] = 0;
    }
    $manutencoesPorSala[$keySala]++;

    if (in_array($status, $openStatuses, true)) {
        $openEquipKeys[$mb . '|' . $ms . '|' . $me] = true;
    }
}

$totalEquip = 0;
$equipSemPatrimonio = 0;

foreach (salasSistema() as $setor) {
    foreach ($setor['salas'] as $s) {
        $equips = equipamentosDaSala($s);
        foreach ($equips as $eq) {
            $totalEquip++;
            $patrimonio = 'a definir';
            if (!empty($eq['especificacoes']['Patrimônio'])) {
                $patrimonio = trim(strtolower($eq['especificacoes']['Patrimônio']));
            }
            if ($patrimonio === '' || $patrimonio === 'a definir') {
                $equipSemPatrimonio++;
            }
        }
    }
}

$equipEmManutencaoCount = count($openEquipKeys);
$equipFuncionando = $totalEquip - $equipEmManutencaoCount;

usort($manutencoes, function ($a, $b) {
    $ta = strtotime($a['data'] ?? '0');
    $tb = strtotime($b['data'] ?? '0');
    return $tb <=> $ta;
});

$ultimasManutencoes = array_slice($manutencoes, 0, 6);

arsort($manutencoesPorSala);
$topSalas = array_slice($manutencoesPorSala, 0, 6, true);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setores - Controle de Equipamentos COC</title>
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
            <a href="index.php" class="menu-link active"><i class="bi bi-grid-1x2"></i> Setores</a>
            <a href="importar_planilha.php" class="menu-link"><i class="bi bi-file-earmark-arrow-up"></i> Importar planilha</a>
            <?php include __DIR__ . '/includes/agent.php'; ?>
        </aside>
        <main class="content">
            <div class="page-header compact-header">
                <h2>Setores</h2>
                <p>Selecione uma sala para visualizar os equipamentos.</p>
            </div>

            <!-- Bloco 1: resumo e indicadores -->
            <section class="dashboard-block mb-5">
                <div class="row g-4 mb-4">
                    <div class="col-6 col-md-3">
                        <section class="stat-card">
                            <div class="stat-label">Total de equipamentos</div>
                            <div class="stat-value"><?= e($totalEquip) ?></div>
                        </section>
                    </div>
                    <div class="col-6 col-md-3">
                        <section class="stat-card">
                            <div class="stat-label">Equipamentos funcionando</div>
                            <div class="stat-value"><?= e($equipFuncionando) ?></div>
                        </section>
                    </div>
                    <div class="col-6 col-md-3">
                        <section class="stat-card">
                            <div class="stat-label">Equipamentos em manutenção</div>
                            <div class="stat-value"><?= e($equipEmManutencaoCount) ?></div>
                        </section>
                    </div>
                    <div class="col-6 col-md-3">
                        <section class="stat-card">
                            <div class="stat-label">Sem patrimônio</div>
                            <div class="stat-value"><?= e($equipSemPatrimonio) ?></div>
                        </section>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <section class="dashboard-list-card">
                            <div class="dashboard-list-header">
                                <div>
                                    <h4>Últimas manutenções</h4>
                                    <span>Chamados recentes registrados</span>
                                </div>
                                <i class="bi bi-tools"></i>
                            </div>

                            <?php if (empty($ultimasManutencoes)): ?>
                                <div class="dashboard-empty">
                                    Nenhuma manutenção registrada.
                                </div>
                            <?php else: ?>
                                <div class="dashboard-list">
                                    <?php foreach ($ultimasManutencoes as $m): ?>
                                        <?php
                                            $statusClasse = strtolower(str_replace([' ', 'ç', 'ã'], ['-', 'c', 'a'], $m['status'] ?? ''));
                                        ?>

                                        <div class="dashboard-list-item">
                                            <div class="item-icon warning">
                                                <i class="bi bi-wrench-adjustable"></i>
                                            </div>

                                            <div class="item-main">
                                                <strong><?= e($m['equipamento'] ?? 'Equipamento') ?></strong>
                                                <span><?= e(($m['bloco'] ?? '') . ' - ' . ($m['sala'] ?? '')) ?></span>
                                            </div>

                                            <div class="item-side">
                                                    <small><?= e($m['data'] ?? '') ?></small>

                                                    <span class="mini-status <?= e($statusClasse) ?>">
                                                        <?= e($m['status'] ?? '') ?>
                                                    </span>

                                                    <?php if (!empty($m['id'])): ?>
                                                        <a href="excluir_manutencao.php?id=<?= e($m['id']) ?>&bloco=<?= e($m['bloco'] ?? '') ?>&sala=<?= e($m['sala'] ?? '') ?>"
                                                           class="delete-mini-btn"
                                                           onclick="return confirm('Tem certeza que deseja excluir esta manutenção?')"
                                                           title="Excluir manutenção">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>

                    <div class="col-lg-6">
                        <section id="salas-com-chamados" class="dashboard-list-card">
                            <div class="dashboard-list-header">
                                <div>
                                    <h4>Modificações feitas</h4>
                                    <span>Salas com mais chamados e alterações</span>
                                </div>
                                <i class="bi bi-activity"></i>
                            </div>

                            <?php if (empty($topSalas)): ?>
                                <div class="dashboard-empty">
                                    Nenhuma modificação registrada.
                                </div>
                            <?php else: ?>
                                <div class="dashboard-list">
                                    <?php foreach ($topSalas as $key => $count): ?>
                                        <?php
                                            [$b, $s] = explode('|', $key);
                                            $room = obterSala($b, $s);
                                            $link = $room ? urlSala($room) : '#';
                                        ?>

                                        <a href="<?= e($link) ?>" class="dashboard-list-item link-item">
                                            <div class="item-icon info">
                                                <i class="bi bi-door-open"></i>
                                            </div>

                                            <div class="item-main">
                                                <strong><?= e($room['titulo'] ?? ($b . $s)) ?></strong>
                                                <span><?= e(nomeBloco($b)) ?></span>
                                            </div>

                                            <div class="item-count">
                                                <?= e($count) ?>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    </div>
                </div>
            </section>

            <!-- Bloco 2: salas e importações -->
            <section class="content-block mb-4">
                <div class="row g-3 mb-4">
                    <?php foreach ($setores as $setor): ?>
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <section class="sector-card">
                                <div class="sector-icon"><i class="bi <?= e($setor['icone']) ?>"></i></div>
                                <h5><?= e($setor['nome']) ?></h5>
                                <div class="room-links">
                                    <?php foreach ($setor['salas'] as $sala): ?>
                                        <a href="<?= e(urlSala($sala)) ?>" class="room-link"><?= e($sala['nome']) ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        </div>
                    <?php endforeach; ?>
                </div>

                <section id="ultimas-alteracoes" class="dashboard-list-card mt-4">
                    <div class="dashboard-list-header">
                        <div>
                            <h4>Importações de planilhas</h4>
                            <span>Últimas atualizações realizadas por arquivo XLSX</span>
                        </div>

                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </div>

                    <?php if (empty($ultimasImportacoes)): ?>
                        <div class="dashboard-empty">
                            Nenhuma importação registrada até o momento.
                        </div>
                    <?php else: ?>
                        <div class="import-history-list">
                            <?php foreach ($ultimasImportacoes as $importacao): ?>
                                <div class="import-history-item">
                                    <div class="item-icon success">
                                        <i class="bi bi-file-earmark-arrow-up"></i>
                                    </div>

                                    <div class="item-main">
                                        <strong><?= e($importacao['arquivo'] ?? 'Planilha importada') ?></strong>
                                        <span>
                                            <?= e($importacao['data'] ?? '') ?>
                                            às
                                            <?= e($importacao['hora'] ?? '') ?>
                                        </span>
                                    </div>

                                    <div class="import-stats">
                                        <div>
                                            <strong><?= e($importacao['atualizados'] ?? 0) ?></strong>
                                            <span>atualizados</span>
                                        </div>

                                        <div>
                                            <strong><?= e($importacao['criados'] ?? 0) ?></strong>
                                            <span>criados</span>
                                        </div>

                                        <div>
                                            <strong><?= e($importacao['erros'] ?? 0) ?></strong>
                                            <span>erros</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </section>
        </main>
    </div>
<script src="assets/js/agente.js"></script>
</body>
</html>
