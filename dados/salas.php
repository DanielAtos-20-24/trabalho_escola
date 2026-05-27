<?php

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

// ============================================================================
// CONFIGURAÇÃO DE IMAGENS - ADICIONE OU MODIFIQUE OS CAMINHOS AQUI
// ============================================================================

function configurarImagensEquipamentos()
{
    return [
        'computador' => true,  // true = tem foto, false = sem foto
        'monitor' => true,
        'teclado' => false,
        'mouse' => false,
        'estabilizador' => false,
        'som' => false,
        'projetor' => false,
        'lousa' => false,
    ];
}

function obterCaminhoImagem($nomeEquipamento, $codigoSala)
{
    $config = configurarImagensEquipamentos();
    $nomeNormalizado = strtolower($nomeEquipamento);
    
    if (!isset($config[$nomeNormalizado]) || !$config[$nomeNormalizado]) {
        return '';
    }
    
    return 'assets/img/' . $nomeNormalizado . '_' . $codigoSala . '.jpeg';
}

// ============================================================================
// FIM DA CONFIGURAÇÃO DE IMAGENS
// ============================================================================

function equipamentosDaSala($sala)
{
    $codigoSala = $sala['codigo'];
    $descricaoSala = $sala['descricao_equipamentos'];

    $baseEquipamentos = [
        [
            'nome' => 'Computador',
            'icone' => 'bi-pc-display-horizontal',
            'processador' => 'Intel i5 4440',
            'memoria' => '6GB DDR3',
            'armazenamento' => 'SSD 240GB',
            'placa_video' => 'NVIDIA GeForce 705',
        ],
        [
            'nome' => 'Monitor',
            'icone' => 'bi-display',
            'processador' => '-',
            'memoria' => '-',
            'armazenamento' => '-',
            'placa_video' => '-',
        ],
        [
            'nome' => 'Teclado',
            'icone' => 'bi-keyboard',
            'processador' => '-',
            'memoria' => '-',
            'armazenamento' => '-',
            'placa_video' => '-',
        ],
        [
            'nome' => 'Mouse',
            'icone' => 'bi-mouse',
            'processador' => '-',
            'memoria' => '-',
            'armazenamento' => '-',
            'placa_video' => '-',
        ],
        [
            'nome' => 'Estabilizador',
            'icone' => 'bi-lightning-charge',
            'processador' => '-',
            'memoria' => '-',
            'armazenamento' => '-',
            'placa_video' => '-',
        ],
        [
            'nome' => 'Som',
            'icone' => 'bi-speaker',
            'processador' => '-',
            'memoria' => '-',
            'armazenamento' => '-',
            'placa_video' => '-',
        ],
        [
            'nome' => 'Projetor',
            'icone' => 'bi-projector',
            'processador' => '-',
            'memoria' => '-',
            'armazenamento' => '-',
            'placa_video' => '-',
        ],
        [
            'nome' => 'Lousa',
            'icone' => 'bi-easel',
            'processador' => '-',
            'memoria' => '-',
            'armazenamento' => '-',
            'placa_video' => '-',
        ],
    ];

    $equipamentos = [];
    foreach ($baseEquipamentos as $index => $equip) {
        $foto = obterCaminhoImagem($equip['nome'], $codigoSala);
        
        $equipamentos[] = [
            'id' => $index + 1,
            'nome' => $equip['nome'],
            'icone' => $equip['icone'],
            'foto' => $foto,
            'descricao' => $equip['nome'] . ' da sala ' . $descricaoSala . '.',
            'processador' => $equip['processador'],
            'memoria' => $equip['memoria'],
            'armazenamento' => $equip['armazenamento'],
            'placa_video' => $equip['placa_video'],
            'patrimonio' => 'A definir',
            'situacao' => 'A DEFINIR',
        ];
    }

    return $equipamentos;
}