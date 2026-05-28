<?php
require_once __DIR__ . '/dados/salas.php';

$id = $_GET['id'] ?? '';
$bloco = $_GET['bloco'] ?? '';
$sala = $_GET['sala'] ?? '';

if ($id === '') {
    header('Location: index.php');
    exit;
}

$arquivo = __DIR__ . '/dados/manutencoes.json';

if (!file_exists($arquivo)) {
    header('Location: index.php');
    exit;
}

$manutencoes = json_decode(file_get_contents($arquivo), true) ?? [];

$manutencoesFiltradas = array_values(array_filter($manutencoes, function ($item) use ($id) {
    return ($item['id'] ?? '') !== $id;
}));

file_put_contents(
    $arquivo,
    json_encode($manutencoesFiltradas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

if ($bloco !== '' && $sala !== '') {
    header('Location: sala.php?bloco=' . urlencode($bloco) . '&sala=' . urlencode($sala));
    exit;
}

header('Location: index.php');
exit;