<?php
// discord_config.php
error_reporting(0);

// 1. CONFIGURACIÓN DEL WEBHOOK
// ===================================
// ¡IMPORTANTE! Pega tu URL de Webhook de Discord aquí (solo una URL).
define('DISCORD_WEBHOOK_URL', 'https://discordapp.com/api/webhooks/1436450316458655744/iYYkRFe8bdyntd2JtcDH02nDLddH5YPTuouClFWsqTrKMQjwakwN4NAY5jPdYtHJg15W');

// 2. FUNCIÓN DE ENVÍO CENTRALIZADA USANDO EMBEDS
// ===================================
// Esta función ahora espera un array de Embeds para un formato visual limpio.
function sendToDiscordEmbed($embeds, $username, $avatarUrl = 'https://i.imgur.com/k6lPqS2.png') {

    // El payload de Discord debe contener los embeds.
    $payload = [
        'username' => $username,
        'avatar_url' => $avatarUrl,
        'embeds' => $embeds
    ];

    $json_data = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // Enviar a Discord usando cURL
    $ch = curl_init(DISCORD_WEBHOOK_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code != 204) {
        error_log("Error al enviar Embed a Discord. Código: {$http_code}. Respuesta: {$response}");
    }
}
?>