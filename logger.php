<?php
// logger.php
require 'discord_config.php'; // Tu archivo donde está la función sendToDiscordEmbed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];
    $usuario = $_POST['usuario'];

    $embed = [
        'title' => '⚠️ Alerta de Usuario',
        'color' => 16776960, // Amarillo
        'description' => "El usuario **{$usuario}** ha realizado la acción: **{$accion}**"
    ];

    sendToDiscordEmbed([$embed], 'Logger de Eventos');
}
?>