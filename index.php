<?php
require_once __DIR__ . '/dados/salas.php';

$setores = salasSistema();
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