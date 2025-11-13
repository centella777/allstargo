<?php
// setkeys.php  →  php setkeys.php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('CLI only');
}

echo "Nuevo bot-token: ";
$token = trim(fgets(STDIN));

echo "Nuevo chat-id: ";
$chatId = trim(fgets(STDIN));

$config = [
    'bot_token' => $token,
    'chat_id'   => $chatId
];

file_put_contents(__DIR__ . '/secrets.json', json_encode($config, JSON_PRETTY_PRINT));
echo "✅ Claves guardadas en secrets.json\n";
?>