<?php

$bloco = $_POST['bloco'] ?? '';
$sala = $_POST['sala'] ?? '';
$equipamento = $_POST['equipamento'] ?? '';
$especificacoes = $_POST['especificacoes'] ?? [];
$fotoAtual = $_POST['foto_atual'] ?? '';
$removerFoto = isset($_POST['remover_foto']);

$arquivoEquipamentos = __DIR__ . '/dados/equipamentos.json';

if (!file_exists($arquivoEquipamentos)) {
    file_put_contents($arquivoEquipamentos, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$equipamentos = json_decode(file_get_contents($arquivoEquipamentos), true) ?? [];

$id = strtoupper($sala) . '-' . strtolower($equipamento);

$foto = $fotoAtual;

if ($removerFoto) {
    $foto = '';
}

if (!empty($_FILES['nova_foto']['name'])) {
    $pastaDestino = __DIR__ . '/assets/img/' . strtolower($sala);

    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0777, true);
    }

    $extensao = strtolower(pathinfo($_FILES['nova_foto']['name'], PATHINFO_EXTENSION));
    $nomeArquivo = strtolower($equipamento) . '.' . $extensao;
    $caminhoServidor = $pastaDestino . '/' . $nomeArquivo;
    $caminhoPublico = 'assets/img/' . strtolower($sala) . '/' . $nomeArquivo;

    move_uploaded_file($_FILES['nova_foto']['tmp_name'], $caminhoServidor);

    $foto = $caminhoPublico;
}

$novoRegistro = [
    'id' => $id,
    'bloco' => $bloco,
    'sala' => $sala,
    'equipamento' => $equipamento,
    'foto' => $foto,
    'especificacoes' => $especificacoes,
    'atualizado_em' => date('Y-m-d H:i:s'),
];

$atualizado = false;

foreach ($equipamentos as $indice => $item) {
    if (($item['id'] ?? '') === $id) {
        $equipamentos[$indice] = $novoRegistro;
        $atualizado = true;
        break;
    }
}

if (!$atualizado) {
    $equipamentos[] = $novoRegistro;
}

file_put_contents(
    $arquivoEquipamentos,
    json_encode($equipamentos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

header('Location: sala.php?bloco=' . urlencode($bloco) . '&sala=' . urlencode($sala));
exit;