<?php

$arquivo = __DIR__ . '/dados/equipamentos.json';

$teste = [
    [
        'id' => 'TESTE-001',
        'bloco' => 'T',
        'sala' => 'T01',
        'equipamento' => 'Teste',
        'foto' => '',
        'especificacoes' => [
            'Marca' => 'Teste',
            'Situação' => 'FUNCIONANDO',
        ],
        'atualizado_em' => date('Y-m-d H:i:s'),
    ]
];

$resultado = file_put_contents(
    $arquivo,
    json_encode($teste, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

if ($resultado === false) {
    echo 'Erro: não conseguiu gravar no arquivo.';
} else {
    echo 'Gravou com sucesso. Bytes: ' . $resultado;
}