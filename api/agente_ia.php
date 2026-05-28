<?php
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/../config/ia.php';

$entrada = json_decode(file_get_contents('php://input'), true);
$mensagem = trim($entrada['mensagem'] ?? '');

if ($mensagem === '') {
    echo json_encode([
        'ok' => false,
        'resposta' => 'Digite uma solicitação para continuar.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$systemPrompt = <<<PROMPT
Você é o Assistente COC de um sistema PHP de estoque de equipamentos escolares.

Sua função é entender o pedido do usuário e devolver uma ação do sistema.

Ações disponíveis:
- index.php = painel inicial, setores, dashboard
- importar_planilha.php = importar planilha XLSX e baixar planilha modelo
- index.php#ultimas-alteracoes = últimas importações/alterações
- index.php#salas-com-chamados = salas com mais chamados/modificações
- sala.php?bloco=A&sala=A01 até A09
- sala.php?bloco=C&sala=C01 até C09
- sala.php?bloco=D&sala=D01 até D03
- sala.php?bloco=E&sala=E01 até E09

Quando a ação depender de escolher um equipamento, explique:
"Abra a sala, clique no equipamento desejado e use o botão correspondente."

Responda SOMENTE com JSON puro, sem markdown, sem crases e sem explicações fora do JSON.

Formato obrigatório quando for redirecionar:
{
  "tipo": "redirect",
  "url": "index.php",
  "resposta": "Abrindo o painel inicial."
}

Formato obrigatório quando for apenas orientar:
{
  "tipo": "mensagem",
  "url": "",
  "resposta": "Abra a sala, clique no equipamento desejado e use o botão correspondente."
}
PROMPT;

$payload = [
    'model' => $config['model'],
    'messages' => [
        [
            'role' => 'system',
            'content' => $systemPrompt
        ],
        [
            'role' => 'user',
            'content' => $mensagem
        ],
    ],
    'temperature' => 0.2,
];

$ch = curl_init($config['endpoint']);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $config['api_key'],
    ],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT => 25,
]);

$respostaApi = curl_exec($ch);
$erroCurl = curl_error($ch);
$statusHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($respostaApi === false || $statusHttp >= 400) {
    echo json_encode([
        'ok' => false,
        'resposta' => 'Erro ao consultar IA. Status HTTP: ' . $statusHttp,
        'erro_curl' => $erroCurl,
        'resposta_api' => $respostaApi
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$dadosApi = json_decode($respostaApi, true);
$conteudo = $dadosApi['choices'][0]['message']['content'] ?? '';

$jsonResposta = json_decode($conteudo, true);

if (!is_array($jsonResposta)) {
    echo json_encode([
        'ok' => true,
        'tipo' => 'mensagem',
        'resposta' => $conteudo ?: 'Não consegui interpretar a resposta da IA.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$jsonResposta['ok'] = true;

echo json_encode($jsonResposta, JSON_UNESCAPED_UNICODE);