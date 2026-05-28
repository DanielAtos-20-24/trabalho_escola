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
    <style>
        .agent-box {
            background: #ffffff;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 1.2rem;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.95rem;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.12);
            transition: transform .25s ease, box-shadow .25s ease, background .25s ease;
            overflow: hidden;
        }
        .agent-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
        }
        .agent-box.closed {
            padding-bottom: 0.6rem;
        }
        .agent-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
        }
        .agent-header i {
            font-size: 1.7rem;
            color: #0d6efd;
        }
        .agent-box.closed .agent-message,
        .agent-box.closed .agent-actions,
        .agent-box.closed .agent-form {
            display: none;
        }
        .agent-message {
            background: #f8f9ff;
            border: 1px solid #dfe7ff;
            border-radius: 0.85rem;
            padding: 0.85rem;
            margin-bottom: 0.85rem;
            min-height: 4rem;
            color: #212529;
            font-weight: 500;
        }
        .agent-actions {
            display: grid;
            gap: 0.55rem;
            margin-bottom: 0.75rem;
        }
        .agent-actions button {
            border: 1px solid rgba(13, 110, 253, 0.15);
            background: #f8f9ff;
            color: #0d6efd;
            padding: 0.75rem 0.85rem;
            border-radius: 0.85rem;
            text-align: left;
            cursor: pointer;
            transition: background .2s ease, transform .2s ease;
        }
        .agent-actions button:hover {
            background: #eef4ff;
            transform: translateX(2px);
        }
        .agent-form {
            display: flex;
            gap: 0.5rem;
        }
        .agent-form input {
            flex: 1;
            border-radius: 0.85rem;
            border: 1px solid rgba(13, 110, 253, 0.2);
            padding: 0.7rem 0.9rem;
            background: #f8f9ff;
            color: #212529;
        }
        .agent-form button {
            border: none;
            border-radius: 0.85rem;
            background: #0d6efd;
            color: white;
            padding: 0 1rem;
            cursor: pointer;
            transition: background .2s ease;
        }
        .agent-form button:hover {
            background: #094dbc;
        }
        .agent-summary {
            color: #6c757d;
            font-size: 0.88rem;
            margin-top: 0.25rem;
        }
    </style>
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
                            <h4>Modificações feitas</h4>
                            <div class="text-muted small mb-3">Modificações recentes por sala</div>
                            <div class="list-compact mt-3">
                                <?php if (empty($topSalas)): ?>
                                    <p class="text-muted">Nenhum chamado registrado.</p>
                                <?php else: ?>
                                    <ul class="top-salas">
                                        <?php foreach ($topSalas as $key => $count): ?>
                                            <?php
                                                [$b, $s] = explode('|', $key);
                                                $room = obterSala($b, $s);
                                                $link = $room ? urlSala($room) : '#';
                                            ?>
                                            <li>
                                                <a href="<?= e($link) ?>"><?= e($room['titulo'] ?? ($b . $s)) ?></a>
                                                <span class="text-muted">(<?= e($count) ?>)</span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </section>
                    </div>
                </div>
            </section>

            <!-- Bloco 2: salas e importações -->
            <section class="content-block">
                <div class="row g-4 mb-4">
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

                <section id="ultimas-alteracoes" class="maintenance-card">
                    <h4>Importações de planilhas</h4>
                    <p class="text-muted mb-3">Importações de planilhas com data e horários</p>
                    <div class="list-compact mt-3">
                        <?php if (empty($ultimasImportacoes)): ?>
                            <p class="text-muted">Nenhuma importação registrada.</p>
                        <?php else: ?>
                            <?php foreach ($ultimasImportacoes as $importacao): ?>
                                <div class="recent-item">
                                    <div class="recent-left">
                                        <strong><?= e($importacao['arquivo'] ?? 'Planilha importada') ?></strong>
                                        <div class="text-muted small"><?= e($importacao['data'] ?? '') ?> às <?= e($importacao['hora'] ?? '') ?></div>
                                    </div>
                                    <div class="recent-right text-end">
                                        <div class="status-badge"><?= e($importacao['atualizados'] ?? 0) ?> atualizados</div>
                                        <div class="small text-muted mt-1"><?= e($importacao['criados'] ?? 0) ?> criados</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </section>
        </main>
    </div>
</body>
</html>
