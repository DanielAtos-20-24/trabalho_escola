<?php

$arquivo = __DIR__ . '/dados/manutencoes.json';

if (!file_exists($arquivo)) {
    file_put_contents($arquivo, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$manutencoes = json_decode(file_get_contents($arquivo), true) ?? [];

$registro = [
    'id' => uniqid('manut_', true),
    'bloco' => $_POST['bloco'] ?? '',
    'sala' => $_POST['sala'] ?? '',
    'equipamento' => $_POST['equipamento'] ?? '',
    'data' => $_POST['data'] ?? date('Y-m-d'),
    'tipo' => $_POST['tipo'] ?? '',
    'status' => $_POST['status'] ?? 'Aberta',
    'problema' => $_POST['problema'] ?? '',
    'servico' => $_POST['servico'] ?? '',
    'peca' => $_POST['peca'] ?? '',
    'tecnico' => $_POST['tecnico'] ?? '',
    'observacoes' => $_POST['observacoes'] ?? '',
    'criado_em' => date('Y-m-d H:i:s'),
];

$manutencoes[] = $registro;

file_put_contents(
    $arquivo,
    json_encode($manutencoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

$bloco = urlencode($registro['bloco']);
$sala = urlencode($registro['sala']);
$equipamento = urlencode($registro['equipamento']);

header("Location: manutencao.php?bloco={$bloco}&sala={$sala}&equipamento={$equipamento}");
exit;