<?php
// Inicia a sessão para armazenar o resultado da mensagem temporariamente entre requisições
session_start();

/**
 * 🔒 Dados de autenticação
 * Substitua com seus dados reais da API do WhatsApp Cloud:
 * - $TOKEN: Token de acesso gerado pelo Facebook Developer
 * - $PHONE_NUMBER_ID: ID do número vinculado à API (disponível no dashboard)
 * - $TO: Número de telefone de destino (deve estar cadastrado no sandbox para testes)
 */
$TOKEN = 'EAAOyuDbg2CEBPi2oVWWtQioB0KEkomOjxMMQBmrnkYAKzW4ZBvpaOhh6PmqTCes61VwQbCE8FeCkJkClTYhos3n8pNwoS76VISHjgyQUD0iZCYMwlCH3d0Iu81pSrQf4jvq5vbwfbZAednCMaWfrNiefhHZBDZA55VEcXNrnhDqfyt5sHkZAtPXIXqZArHwLh0l9yNdHBxQMXihdiR5cRY3iiPoajvqHhuyca7lZBR5mRaQxkTVxMGHchn56a5af3Q';
$PHONE_NUMBER_ID = '298740843322595';
$TO = '5519997094181'; // Número com DDI + DDD (ex: Brasil: 55 + DDD + número)

/**
 * 📨 Se o formulário foi enviado (método POST), executa o envio da mensagem
 * Isso só será executado quando o botão "Enviar mensagem" for clicado
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // URL da API do WhatsApp para envio de mensagens
    $url = "https://graph.facebook.com/v17.0/{$PHONE_NUMBER_ID}/messages";

    // Corpo da requisição: envio de mensagem do tipo template (hello_world é padrão no sandbox)
    $data = [
        "messaging_product" => "whatsapp",
        "to" => $TO,
        "type" => "template",
        "template" => [
            "name" => "hello_world", // Template obrigatório no modo sandbox
            "language" => [
                "code" => "en_US"    // Idioma configurado no template
            ]
        ]
    ];

    // Caminho completo para o arquivo cacert.pem (certificado raiz para validação SSL)
    // Deve estar salvo na mesma pasta do script
    $cacertPath = __DIR__ . '/cacert.pem';

    // Inicializa a sessão cURL
    $ch = curl_init($url);

    // Configura os headers da requisição
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$TOKEN}", // Token de autenticação
        "Content-Type: application/json"  // Tipo de conteúdo enviado
    ]);

    // Define o método como POST e passa os dados codificados em JSON
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Retorna a resposta como string em vez de imprimir diretamente
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Define o caminho para o certificado raiz (evita erro de SSL no Windows/XAMPP)
    curl_setopt($ch, CURLOPT_CAINFO, $cacertPath);

    // Executa a requisição e armazena a resposta
    $response = curl_exec($ch);

    // Captura o código HTTP de resposta (ex: 200, 400, 401, etc.)
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Captura erros cURL (se houver)
    $err = curl_error($ch);

    // Encerra a sessão cURL
    curl_close($ch);

    /**
     * 🧠 Armazena o resultado na sessão para exibir após redirecionamento
     * Evita o reenvio da mensagem ao atualizar (F5)
     */
    if ($response === false) {
        $_SESSION['result'] = "Erro cURL: {$err}";
    } else {
        $_SESSION['result'] = "HTTP {$httpcode}\n{$response}";
    }

    // 🔁 Redireciona para a mesma página com método GET (PRG - Post/Redirect/Get)
    // Evita o reenvio do formulário ao atualizar a página
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/**
 * 🖥️ Após redirecionamento, carrega o resultado da sessão (se existir)
 * Isso garante que o resultado será mostrado apenas uma vez após o envio
 */
$result = null;
if (isset($_SESSION['result'])) {
    $result = $_SESSION['result'];
    unset($_SESSION['result']); // Limpa a sessão para não repetir na próxima visita
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Envio de Mensagem via WhatsApp Cloud API (template)</title>
</head>
<body>
    <h2>Enviar mensagem WhatsApp (template: <code>hello_world</code>)</h2>

    <!-- Formulário que dispara o envio ao ser submetido via POST -->
    <form method="post">
        <button type="submit">Enviar mensagem</button>
    </form>

    <!-- Mostra o resultado da API (após envio) -->
    <?php if ($result !== null): ?>
        <h3>Resultado</h3>
        <pre><?php echo htmlspecialchars($result); ?></pre>
    <?php endif; ?>

    <hr>

    <!-- Informações úteis para o desenvolvedor -->
    <p><strong>Importante:</strong></p>
    <ul>
        <li>Este envio usa um template pré-aprovado (<code>hello_world</code>) obrigatório no modo sandbox.</li>
        <li>O número de destino (<code><?php echo htmlspecialchars($TO); ?></code>) deve estar <strong>verificado no sandbox</strong>.</li>
        <li>O arquivo <code>cacert.pem</code> precisa estar na <strong>mesma pasta</strong> deste script para evitar erros SSL com cURL.</li>
        <li>Evite publicar o <strong>token</strong> diretamente em repositórios públicos.</li>
    </ul>
</body>
</html>
