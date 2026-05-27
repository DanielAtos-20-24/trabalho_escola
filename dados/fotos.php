<?php

function fotosEquipamentos()
{
    return [
        'C01' => [
            'computador' => 'assets/img/c01/computador.jpg',
            'monitor' => 'assets/img/monitor.jpeg',
            'teclado' => '',
            'mouse' => '',
            'estabilizador' => '',
            'som' => '',
            'projetor' => '',
            'lousa' => '',
        ],

        'A01' => [
            'computador' => 'assets/img/a01/computador.jpg',
            'monitor' => 'assets/img/monitor.jpeg',
            'teclado' => '',
            'mouse' => '',
            'estabilizador' => '',
            'som' => '',
            'projetor' => '',
            'lousa' => '',
        ],
    ];
}

function fotoEquipamento($codigoSala, $nomeEquipamento)
{
    $fotos = fotosEquipamentos();
    $equipamento = strtolower($nomeEquipamento);

    return $fotos[$codigoSala][$equipamento] ?? '';
}

function obterCaminhoImagem($codigoSala, $nomeEquipamento)
{
    return fotoEquipamento($codigoSala, $nomeEquipamento);
}