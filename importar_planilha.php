<?php
require_once __DIR__ . '/dados/salas.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Importar Planilha</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">
                <i class="bi bi-file-earmark-arrow-up"></i>
            </div>
            <div>
                <h1>Importação</h1>
                <span>Atualizar equipamentos</span>
            </div>
        </div>

        <div class="menu-title">Menu</div>

        <a href="index.php" class="menu-link">
            <i class="bi bi-grid-1x2"></i> Setores
        </a>

        <a href="importar_planilha.php" class="menu-link active">
            <i class="bi bi-file-earmark-arrow-up"></i> Importar planilha
        </a>
    </aside>

    <main class="content">
        <div class="page-header">
            <div>
                <h2>Importar planilha CSV</h2>
                <p>Atualize os dados dos equipamentos automaticamente por arquivo CSV.</p>
            </div>
        </div>

        <section class="maintenance-card">
            <div class="maintenance-header">
                <div class="maintenance-icon">
                    <i class="bi bi-table"></i>
                </div>

                <div>
                    <h3>Enviar arquivo CSV</h3>
                    <p>Use a estrutura padrão de colunas para atualizar equipamentos, patrimônios, status e especificações.</p>
                </div>
            </div>

            <form action="processar_importacao.php" method="POST" enctype="multipart/form-data" class="maintenance-form">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Arquivo CSV</label>
                        <input type="file" name="planilha" class="form-control" accept=".xlsx,.xls" required>
                    </div>
                </div>

                <div class="maintenance-actions">
                    <button type="submit" class="action-btn warning">
                        <i class="bi bi-upload"></i> Importar XLSX
                    </button>
                </div>
            </form>
        </section>

        <section class="maintenance-card mt-4">
            <h3>Modelo de colunas</h3>

            <p class="text-secondary mt-3">
                A primeira linha da planilha precisa conter exatamente estes nomes:
            </p>

            <div class="import-columns">
                <code>bloco</code>
                <code>sala</code>
                <code>equipamento</code>
                <code>marca</code>
                <code>modelo</code>
                <code>patrimonio</code>
                <code>situacao</code>
                <code>processador</code>
                <code>memoria</code>
                <code>armazenamento</code>
                <code>placa_video</code>
                <code>polegada</code>
                <code>voltagem</code>
                <code>qualidade_lampada</code>
                <code>qualidade_lousa</code>
                <code>foto</code>
            </div>
        </section>
    </main>
</div>
</body>
</html>