<?php
// tg_creds.php
header('Content-Type: application/json; charset=utf-8');

/* 1) HTTPS-only */
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    http_response_code(403);
    die(json_encode(['error' => 'HTTPS requerido']));
}

/* 2) Clave secreta */
$secretKey = $_GET['key'] ?? '';
if (!hash_equals('CWcheMJyuGZW8oCRLpuR4w', $secretKey)) {
    http_response_code(401);
    die(json_encode(['error' => 'Clave inválida']));
}

/* 3) IP whitelist (opcional) */
$allowedIPs = [];                       // Edita o deja vacío para desactivar
if ($allowedIPs && !in_array($_SERVER['REMOTE_ADDR'], $allowedIPs, true)) {
    http_response_code(403);
    die(json_encode(['error' => 'IP no autorizada']));
}

/* 4) Rate-limit básico (10/60s) */
$rlFile = sys_get_temp_dir() . '/tg_rate_' . $_SERVER['REMOTE_ADDR'] . '.txt';
$now    = time();
$window = 60;
$max    = 10;
if (file_exists($rlFile)) {
    $data = json_decode(file_get_contents($rlFile), true) ?: ['hits' => 0, 'reset' => 0];
    if ($now > $data['reset']) {
        $data = ['hits' => 1, 'reset' => $now + $window];
    } else {
        $data['hits']++;
    }
    if ($data['hits'] > $max) {
        http_response_code(429);
        die(json_encode(['error' => 'Demasiadas peticiones']));
    }
} else {
    $data = ['hits' => 1, 'reset' => $now + $window];
}
file_put_contents($rlFile, json_encode($data));

/* 5) Leer secretos */
$secretsFile = __DIR__ . '/secrets.json';
if (!file_exists($secretsFile)) {
    http_response_code(500);
    die(json_encode(['error' => 'Archivo de secretos no encontrado']));
}
$secrets = json_decode(file_get_contents($secretsFile), true);
if (!$secrets || !isset($secrets['bot_token'], $secrets['chat_id'])) {
    http_response_code(500);
    die(json_encode(['error' => 'Secretos inválidos']));
}

/* 6) Headers de seguridad */
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store, no-cache, must-revalidate');

/* 7) Respuesta */
echo json_encode([
    'bot_token' => $secrets['bot_token'],
    'chat_id'   => $secrets['chat_id']
]);
?>