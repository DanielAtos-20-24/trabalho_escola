<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/dados/salas.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

function limparValor($valor)
{
    return trim((string) $valor);
}

function montarEspecificacoesPorEquipamento($linha)
{
    $equipamento = strtolower(limparValor($linha['equipamento'] ?? ''));
    $situacao = limparValor($linha['situacao'] ?? 'A DEFINIR');
    $patrimonio = limparValor($linha['patrimonio'] ?? 'A definir');

    if ($equipamento === 'computador') {
        return [
            'Marca' => limparValor($linha['marca'] ?? 'A definir'),
            'Modelo' => limparValor($linha['modelo'] ?? 'A definir'),
            'Processador' => limparValor($linha['processador'] ?? 'A definir'),
            'Memória' => limparValor($linha['memoria'] ?? 'A definir'),
            'Armazenamento' => limparValor($linha['armazenamento'] ?? 'A definir'),
            'Placa de vídeo' => limparValor($linha['placa_video'] ?? 'A definir'),
            'Patrimônio' => $patrimonio,
            'Situação' => $situacao,
        ];
    }

    if ($equipamento === 'monitor') {
        return [
            'Polegada' => limparValor($linha['polegada'] ?? 'A definir'),
            'Marca' => limparValor($linha['marca'] ?? 'A definir'),
            'Patrimônio' => $patrimonio,
            'Situação' => $situacao,
        ];
    }

    if ($equipamento === 'teclado' || $equipamento === 'mouse' || $equipamento === 'som') {
        return [
            'Marca' => limparValor($linha['marca'] ?? 'A definir'),
            'Patrimônio' => $patrimonio,
            'Situação' => $situacao,
        ];
    }

    if ($equipamento === 'estabilizador') {
        return [
            'Marca' => limparValor($linha['marca'] ?? 'A definir'),
            'Voltagem' => limparValor($linha['voltagem'] ?? 'A definir'),
            'Patrimônio' => $patrimonio,
            'Situação' => $situacao,
        ];
    }

    if ($equipamento === 'projetor') {
        return [
            'Marca' => limparValor($linha['marca'] ?? 'A definir'),
            'Modelo' => limparValor($linha['modelo'] ?? 'A definir'),
            'Qualidade da lâmpada' => limparValor($linha['qualidade_lampada'] ?? 'A definir'),
            'Situação' => $situacao,
        ];
    }

    if ($equipamento === 'lousa') {
        return [
            'Marca' => limparValor($linha['marca'] ?? 'A definir'),
            'Qualidade da lousa' => limparValor($linha['qualidade_lousa'] ?? 'A definir'),
            'Situação' => $situacao,
        ];
    }

    return [
        'Marca' => limparValor($linha['marca'] ?? 'A definir'),
        'Modelo' => limparValor($linha['modelo'] ?? 'A definir'),
        'Patrimônio' => $patrimonio,
        'Situação' => $situacao,
    ];
}

if (!isset($_FILES['planilha']) || $_FILES['planilha']['error'] !== UPLOAD_ERR_OK) {
    die('Erro ao enviar o arquivo.');
}

$extensao = strtolower(pathinfo($_FILES['planilha']['name'], PATHINFO_EXTENSION));

if (!in_array($extensao, ['xlsx', 'xls'])) {
    die('Arquivo inválido. Envie apenas XLSX ou XLS.');
}

$pastaUploads = __DIR__ . '/uploads/importacoes';

if (!is_dir($pastaUploads)) {
    mkdir($pastaUploads, 0777, true);
}

$nomeUpload = 'importacao_' . date('Ymd_His') . '.' . $extensao;
$caminhoUpload = $pastaUploads . '/' . $nomeUpload;

move_uploaded_file($_FILES['planilha']['tmp_name'], $caminhoUpload);

$arquivoEquipamentos = __DIR__ . '/dados/equipamentos.json';

if (!file_exists($arquivoEquipamentos)) {
    file_put_contents($arquivoEquipamentos, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$equipamentos = json_decode(file_get_contents($arquivoEquipamentos), true) ?? [];

$spreadsheet = IOFactory::load($caminhoUpload);
$sheet = $spreadsheet->getActiveSheet();
$linhas = $sheet->toArray(null, true, true, true);

if (count($linhas) < 2) {
    die('Planilha vazia ou sem dados.');
}

$cabecalhoOriginal = array_shift($linhas);
$cabecalho = [];

foreach ($cabecalhoOriginal as $coluna => $nomeCampo) {
    $cabecalho[$coluna] = strtolower(trim((string) $nomeCampo));
}

$totalLidas = 0;
$totalCriadas = 0;
$totalAtualizadas = 0;
$erros = [];

foreach ($linhas as $numeroLinha => $dadosLinha) {
    $totalLidas++;

    $linha = [];

    foreach ($cabecalho as $coluna => $nomeCampo) {
        if ($nomeCampo === '') {
            continue;
        }

        $linha[$nomeCampo] = limparValor($dadosLinha[$coluna] ?? '');
    }

    $bloco = strtoupper(limparValor($linha['bloco'] ?? ''));
    $sala = strtoupper(limparValor($linha['sala'] ?? ''));
    $equipamento = limparValor($linha['equipamento'] ?? '');

    if ($bloco === '' || $sala === '' || $equipamento === '') {
        $erros[] = "Linha {$numeroLinha}: bloco, sala ou equipamento vazio.";
        continue;
    }

    $id = $sala . '-' . strtolower($equipamento);

    $novoRegistro = [
        'id' => $id,
        'bloco' => $bloco,
        'sala' => $sala,
        'equipamento' => $equipamento,
        'foto' => limparValor($linha['foto'] ?? ''),
        'especificacoes' => montarEspecificacoesPorEquipamento($linha),
        'atualizado_em' => date('Y-m-d H:i:s'),
        'origem' => 'importacao_xlsx',
    ];

    $encontrado = false;

    foreach ($equipamentos as $indice => $item) {
        if (($item['id'] ?? '') === $id) {
            $equipamentos[$indice] = $novoRegistro;
            $encontrado = true;
            $totalAtualizadas++;
            break;
        }
    }

    if (!$encontrado) {
        $equipamentos[] = $novoRegistro;
        $totalCriadas++;
    }
}


file_put_contents(
    $arquivoEquipamentos,
    json_encode($equipamentos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

$arquivoImportacoes = __DIR__ . '/dados/importacoes.json';

if (!file_exists($arquivoImportacoes)) {
    file_put_contents($arquivoImportacoes, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$importacoes = json_decode(file_get_contents($arquivoImportacoes), true) ?? [];

$importacoes[] = [
    'id' => uniqid('imp_', true),
    'arquivo' => $nomeUpload,
    'data' => date('Y-m-d'),
    'hora' => date('H:i:s'),
    'linhas_lidas' => $totalLidas,
    'criados' => $totalCriadas,
    'atualizados' => $totalAtualizadas,
    'erros' => count($erros),
    'criado_em' => date('Y-m-d H:i:s'),
];

file_put_contents(
    $arquivoImportacoes,
    json_encode($importacoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Resultado da Importação</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div>
                <h1>Importação</h1>
                <span>Resultado</span>
            </div>
        </div>

        <div class="menu-title">Menu</div>

        <a href="index.php" class="menu-link">
            <i class="bi bi-grid-1x2"></i> Setores
        </a>

        <a href="importar_planilha.php" class="menu-link active">
            <i class="bi bi-file-earmark-arrow-up"></i> Nova importação
        </a>
    </aside>

    <main class="content">
        <div class="page-header">
            <div>
                <h2>Resultado da importação</h2>
                <p>Resumo do processamento do arquivo XLSX.</p>
            </div>
        </div>

        <section class="maintenance-card">
            <div class="spec-grid">
                <div class="spec-item">
                    <span>Linhas lidas</span>
                    <strong><?= e($totalLidas) ?></strong>
                </div>

                <div class="spec-item">
                    <span>Criados</span>
                    <strong><?= e($totalCriadas) ?></strong>
                </div>

                <div class="spec-item">
                    <span>Atualizados</span>
                    <strong><?= e($totalAtualizadas) ?></strong>
                </div>

                <div class="spec-item">
                    <span>Erros</span>
                    <strong><?= e(count($erros)) ?></strong>
                </div>
            </div>

            <?php if (!empty($erros)): ?>
                <div class="empty-state mt-4">
                    <strong>Erros encontrados:</strong>
                    <ul class="mt-3">
                        <?php foreach ($erros as $erro): ?>
                            <li><?= e($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="details-actions mt-4">
                <a href="index.php" class="action-btn info">
                    Voltar para setores
                </a>

                <a href="importar_planilha.php" class="action-btn warning">
                    Importar outro XLSX
                </a>
            </div>
        </section>
    </main>
</div>
</body>
</html>