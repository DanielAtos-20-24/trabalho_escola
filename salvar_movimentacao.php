<?php

$blocoOrigem = $_POST['bloco_origem'] ?? '';
$salaOrigem = $_POST['sala_origem'] ?? '';
$equipamento = $_POST['equipamento'] ?? '';
$salaDestinoComposta = $_POST['sala_destino'] ?? '';
$responsavel = $_POST['responsavel'] ?? '';
$motivo = $_POST['motivo'] ?? '';

[$blocoDestino, $salaDestino] = explode('|', $salaDestinoComposta);

$arquivoMovimentacoes = __DIR__ . '/dados/movimentacoes.json';

if (!file_exists($arquivoMovimentacoes)) {
    file_put_contents($arquivoMovimentacoes, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$movimentacoes = json_decode(file_get_contents($arquivoMovimentacoes), true) ?? [];

$movimentacoes[] = [
    'id' => uniqid('mov_', true),
    'equipamento' => $equipamento,
    'bloco_origem' => $blocoOrigem,
    'sala_origem' => $salaOrigem,
    'bloco_destino' => $blocoDestino,
    'sala_destino' => $salaDestino,
    'responsavel' => $responsavel,
    'motivo' => $motivo,
    'data' => date('Y-m-d'),
    'criado_em' => date('Y-m-d H:i:s'),
];

file_put_contents(
    $arquivoMovimentacoes,
    json_encode($movimentacoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

header('Location: sala.php?bloco=' . urlencode($blocoDestino) . '&sala=' . urlencode($salaDestino));
exit;