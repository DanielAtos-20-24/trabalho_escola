<?php
require_once __DIR__ . '/fotos.php';
function e($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function criarSalaSequencial($bloco, $numero)
{
    $codigo = $bloco . str_pad((string) $numero, 2, '0', STR_PAD_LEFT);

    return [
        'bloco' => $bloco,
        'codigo' => $codigo,
        'nome' => $bloco . $numero,
        'titulo' => 'Sala ' . $codigo,
        'descricao_equipamentos' => $codigo,
        'aliases' => [$codigo, $bloco . $numero],
    ];
}

function criarSalasSequenciais($bloco, $quantidade)
{
    $salas = [];

    for ($i = 1; $i <= $quantidade; $i++) {
        $salas[] = criarSalaSequencial($bloco, $i);
    }

    return $salas;
}

function salasSistema()
{
    $salasC = criarSalasSequenciais('C', 9);
    $salasC[] = [
        'bloco' => 'C',
        'codigo' => 'EI',
        'nome' => 'EI',
        'titulo' => 'Sala Escola da Inteligência',
        'descricao_equipamentos' => 'escola da inteligência',
        'aliases' => ['EI', 'ESCOLAINTELIGENCIA', 'ESCOLADAINTELIGENCIA', 'ESCOLA_INTELIGENCIA'],
    ];

    return [
        [
            'id' => 'A',
            'nome' => 'Bloco A',
            'icone' => 'bi-buildings',
            'salas' => criarSalasSequenciais('A', 9),
        ],
        [
            'id' => 'C',
            'nome' => 'Bloco C',
            'icone' => 'bi-building',
            'salas' => $salasC,
        ],
        [
            'id' => 'D',
            'nome' => 'Bloco D',
            'icone' => 'bi-building-fill',
            'salas' => criarSalasSequenciais('D', 3),
        ],
        [
            'id' => 'E',
            'nome' => 'Bloco E',
            'icone' => 'bi-bank',
            'salas' => criarSalasSequenciais('E', 9),
        ],
    ];
}

function normalizarChave($valor)
{
    return preg_replace('/[^A-Z0-9]/', '', strtoupper(trim((string) $valor)));
}

function obterSala($blocoInformado, $salaInformada)
{
    $blocoBuscado = normalizarChave($blocoInformado);
    $salaBuscada = normalizarChave($salaInformada);

    foreach (salasSistema() as $setor) {
        if (normalizarChave($setor['id']) !== $blocoBuscado) {
            continue;
        }

        foreach ($setor['salas'] as $sala) {
            $aliases = array_merge([$sala['codigo'], $sala['nome']], $sala['aliases'] ?? []);

            foreach ($aliases as $alias) {
                if (normalizarChave($alias) === $salaBuscada) {
                    return $sala;
                }
            }
        }
    }

    return null;
}

function nomeBloco($bloco)
{
    foreach (salasSistema() as $setor) {
        if ($setor['id'] === $bloco) {
            return $setor['nome'];
        }
    }

    return 'Bloco';
}

function urlSala($sala)
{
    return 'sala.php?bloco=' . rawurlencode($sala['bloco']) . '&sala=' . rawurlencode($sala['codigo']);
}


function equipamentosDaSala($sala)
{
    $codigoSala = $sala['codigo'];
    $descricaoSala = $sala['descricao_equipamentos'];

    $baseEquipamentos = [
        [
            'nome' => 'Computador',
            'icone' => 'bi-pc-display-horizontal',
            'especificacoes' => [
                'Processador' => 'Intel i5 4440',
                'Memória' => '6GB DDR3',
                'Armazenamento' => 'SSD 240GB',
                'Placa de vídeo' => 'NVIDIA GeForce 705',
                'Patrimônio' => 'A definir',
                'Situação' => 'A DEFINIR',
            ],
        ],
        [
            'nome' => 'Monitor',
            'icone' => 'bi-display',
            'especificacoes' => [
                'Polegada' => 'A definir',
                'Marca' => 'A definir',
                'Patrimônio' => 'A definir',
                'Situação' => 'A DEFINIR',
            ],
        ],
        [
            'nome' => 'Teclado',
            'icone' => 'bi-keyboard',
            'especificacoes' => [
                'Marca' => 'A definir',
                'Patrimônio' => 'A definir',
                'Situação' => 'A DEFINIR',
            ],
        ],
        [
            'nome' => 'Mouse',
            'icone' => 'bi-mouse',
            'especificacoes' => [
                'Marca' => 'A definir',
                'Patrimônio' => 'A definir',
                'Situação' => 'A DEFINIR',
            ],
        ],
        [
            'nome' => 'Estabilizador',
            'icone' => 'bi-lightning-charge',
            'especificacoes' => [
                'Marca' => 'A definir',
                'Voltagem' => 'A definir',
                'Patrimônio' => 'A definir',
                'Situação' => 'A DEFINIR',
            ],
        ],
        [
            'nome' => 'Som',
            'icone' => 'bi-speaker',
            'especificacoes' => [
                'Marca' => 'A definir',
                'Patrimônio' => 'A definir',
                'Situação' => 'A DEFINIR',
            ],
        ],
        [
            'nome' => 'Projetor',
            'icone' => 'bi-projector',
            'especificacoes' => [
                'Marca' => 'A definir',
                'Modelo' => 'A definir',
                'Qualidade da lâmpada' => 'A definir',
                'Situação' => 'A DEFINIR',
            ],
        ],
        [
            'nome' => 'Lousa',
            'icone' => 'bi-easel',
            'especificacoes' => [
                'Marca' => 'A definir',
                'Qualidade da lousa' => 'A definir',
                'Situação' => 'A DEFINIR',
            ],
        ],
    ];

    $equipamentos = [];

    foreach ($baseEquipamentos as $index => $equip) {
        $foto = fotoEquipamento($codigoSala, $equip['nome']);

        $equipamentos[] = [
            'id' => $index + 1,
            'nome' => $equip['nome'],
            'icone' => $equip['icone'],
            'foto' => $foto,
            'descricao' => $equip['nome'] . ' da sala ' . $descricaoSala . '.',
            'especificacoes' => $equip['especificacoes'],
            'situacao' => $equip['especificacoes']['Situação'] ?? 'A DEFINIR',
        ];
    }

    return $equipamentos;
}