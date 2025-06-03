<?php
session_start();
include 'config/database.php';

// Sin validación de sesión adecuada
if(!isset($_SESSION['usuario_codigo'])) {
    header("Location: index.php");
    exit();
}

// Obtener datos del usuario y formulario
$usuario_codigo = $_SESSION['usuario_codigo'];
$formulario_id = $_POST['formulario_id'];

// Procesar todas las respuestas
foreach($_POST as $key => $value) {
    // Solo procesar claves que empiezan con 'respuesta_'
    if(strpos($key, 'respuesta_') === 0) {
        $pregunta_id = str_replace('respuesta_', '', $key);
        
        // Insertar respuesta en la base de datos
        $query = "INSERT INTO respuestas (usuario_codigo, formulario_id, pregunta_id, respuesta) 
                  VALUES ('$usuario_codigo', '$formulario_id', '$pregunta_id', '$value')";
        
        $resultado_insert = mysqli_query($conexion, $query);
        if (!$resultado_insert) {
            // Manejo de errores
            echo "Error al guardar respuesta para pregunta $pregunta_id: " . mysqli_error($conexion) . "<br>";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuestas Enviadas - VirtualPhysics</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="css/procesar_formulario.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-container">
            <div class="logo">
                <img src="img/logo.png" alt="VirtualPhysics Logo">
                <span class="logo-text">VirtualPhysics</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="logout.php" class="logout-btn">Salir</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <div class="success-card">
                <svg class="success-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                
                <h2 class="success-title">¡Respuestas Enviadas!</h2>
                <p class="success-message">Tus respuestas han sido guardadas correctamente en la base de datos.</p>
                
                <div class="action-buttons">
                    <a href="dashboard.php" class="btn btn-primary">
                        Volver al Dashboard
                    </a>
                    <a href="formularios.php?id=<?php echo htmlspecialchars($formulario_id); ?>" class="btn btn-secondary">
                        Ver Formulario de Nuevo
                    </a>
                </div>
            </div>
            
            <!-- Debug info -->
            <div class="debug-box">
                <p>
                    <strong>Información:</strong> Respuestas procesadas para usuario 
                    <?php echo $usuario_codigo; ?> 
                    en formulario <?php echo $formulario_id; ?>.
                    <br>
                    <i>(Esta información es solo para fines de desarrollo)</i>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Cerrar conexión
mysqli_close($conexion);
?>
