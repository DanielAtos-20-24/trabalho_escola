<?php
require_once __DIR__ . '/dados/salas.php';

$setores = salasSistema();
// --- Estatísticas para dashboard na página inicial ---
$arquivoManutencoes = __DIR__ . '/dados/manutencoes.json';
if (!file_exists($arquivoManutencoes)) {
    file_put_contents($arquivoManutencoes, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
$manutencoes = json_decode(file_get_contents($arquivoManutencoes), true) ?? [];

// Preparar conjuntos e contagens
$openStatuses = ['ABERTA','EM ANDAMENTO','AGUARDANDO PEÇA','ENCAMINHADA'];
$openEquipKeys = [];
$manutencoesPorSala = [];
foreach ($manutencoes as $m) {
    $mb = $m['bloco'] ?? '';
    $ms = $m['sala'] ?? '';
    $me = strtolower($m['equipamento'] ?? '');
    $status = strtoupper($m['status'] ?? '');

    // contar chamados por sala
    $keySala = $mb . '|' . $ms;
    if (!isset($manutencoesPorSala[$keySala])) $manutencoesPorSala[$keySala] = 0;
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
            $pat = trim(strtolower($eq['patrimonio'] ?? ''));
            if ($pat === '' || $pat === 'a definir' || $pat === 'a definir') {
                $equipSemPatrimonio++;
            }
        }
    }
}

$equipEmManutencaoCount = count($openEquipKeys);
$equipFuncionando = $totalEquip - $equipEmManutencaoCount;

// Últimas manutenções (mais recentes)
usort($manutencoes, function($a, $b) {
    $ta = strtotime($a['data'] ?? '0');
    $tb = strtotime($b['data'] ?? '0');
    return $tb <=> $ta;
});
$ultimasManutencoes = array_slice($manutencoes, 0, 6);

// Salas com mais chamados
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
        </aside>

        <main class="content">
            <div class="page-header compact-header">
                <h2>Setores</h2>
                <p>Selecione uma sala para visualizar os equipamentos.</p>
            </div>

            <!-- Dashboard resumo -->
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

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <section class="maintenance-card">
                        <h4>Últimas manutenções</h4>
                        <div class="list-compact mt-3">
                            <?php if (empty($ultimasManutencoes)): ?>
                                <p class="text-muted">Nenhuma manutenção registrada.</p>
                            <?php else: ?>
                                <?php foreach ($ultimasManutencoes as $m): ?>
                                    <div class="recent-item">
                                        <div class="recent-left">
                                            <strong><?= e($m['equipamento'] ?? '') ?></strong>
                                            <div class="text-muted small"><?= e(($m['bloco'] ?? '') . ' - ' . ($m['sala'] ?? '')) ?></div>
                                        </div>
                                        <div class="recent-right text-end">
                                            <div class="small text-muted"><?= e($m['data'] ?? '') ?></div>
                                            <div class="status-badge <?= 'status-' . str_replace(' ', '-', $m['status'] ?? '') ?>"><?= e($m['status'] ?? '') ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>

                <div class="col-lg-6">
                    <section class="maintenance-card">
                        <h4>Salas com mais chamados</h4>
                        <div class="list-compact mt-3">
                            <?php if (empty($topSalas)): ?>
                                <p class="text-muted">Nenhum chamado registrado.</p>
                            <?php else: ?>
                                <ul class="top-salas">
                                    <?php foreach ($topSalas as $key => $count):
                                        list($b, $s) = explode('|', $key);
                                        $room = obterSala($b, $s);
                                        $link = $room ? urlSala($room) : '#';
                                    ?>
                                        <li>
                                            <a href="<?= e($link) ?>"><?= e(($room['titulo'] ?? ($b . $s))) ?></a>
                                            <span class="text-muted"> (<?= e($count) ?>)</span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($setores as $setor): ?>
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <section class="sector-card">
                            <div class="sector-icon">
                                <i class="bi <?= e($setor['icone']) ?>"></i>
                            </div>

                            <h5><?= e($setor['nome']) ?></h5>

                            <div class="room-links">
                                <?php foreach ($setor['salas'] as $sala): ?>
                                    <a href="<?= e(urlSala($sala)) ?>" class="room-link">
                                        <?= e($sala['nome']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>