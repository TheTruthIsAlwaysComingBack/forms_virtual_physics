<?php
session_start();

// Redirigir si ya está logueado
if(isset($_SESSION["usuario_codigo"])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VirtualPhysics - Inicio de Sesión</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="login-card">
            <!-- Lado izquierdo - Imagen -->
            <div class="image-section">
                <div class="character-container">
                    <img src="img/fondo.png" alt="Estudiante VirtualPhysics" class="character-image">
                </div>
            </div>
            
            <!-- Lado derecho - Formulario -->
            <div class="form-section">
                <!-- Logo y encabezado -->
                <div class="header">
                    <div class="logo-container">
                        <img src="img/logo.png" alt="VirtualPhysics Logo" class="logo">
                    </div>
                    <p class="welcome-text">Bienvenido a VirtualPhysics</p>
                    <h1 class="page-title">Inicio de Sesión</h1>
                </div>

                <!-- Formulario -->
                <form action="procesar_login.php" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="correo">Correo</label>
                        <input type="text" id="correo" name="correo" required 
                               placeholder="login@gmail.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasena">Contraseña</label>
                        <input type="password" id="contrasena" name="contrasena" required 
                               placeholder="••••••••••••">
                    </div>
                    
                    <button type="submit" class="login-btn">
                        <span>INGRESAR</span>
                        <svg class="btn-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </button>
                </form>

                <!-- Footer links -->
                <div class="footer-links">
                    <a href="#" class="link">¿No tienes una cuenta?</a>
                    <a href="#" class="link-primary">Regístrate</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
