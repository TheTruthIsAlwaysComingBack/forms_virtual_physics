<?php
session_start();
include 'config/database.php';

// Sin validación de sesión adecuada
if(!isset($_SESSION['usuario_codigo'])) {
    header("Location: index.php");
    exit();
}

// El ID del formulario se toma de GET
$formulario_id = $_GET['id']; 

// Consulta para obtener el formulario
$query_formulario = "SELECT * FROM formularios WHERE id = $formulario_id";
$resultado_formulario = mysqli_query($conexion, $query_formulario);

if (!$resultado_formulario) {
    die("Error al consultar formulario: " . mysqli_error($conexion));
}
$formulario = mysqli_fetch_assoc($resultado_formulario);

// Consulta para obtener las preguntas
$query_preguntas = "SELECT * FROM preguntas WHERE formulario_id = $formulario_id ORDER BY orden";
$preguntas = mysqli_query($conexion, $query_preguntas);

if (!$preguntas) {
    die("Error al consultar preguntas: " . mysqli_error($conexion));
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $formulario ? $formulario['titulo'] : 'Formulario no encontrado'; ?> - VirtualPhysics</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="css/formularios.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <nav class="navbar">
        <div class="container navbar-container">
            <div class="logo">
                <img src="img/logo.png" alt="VirtualPhysics Logo">
                <span class="logo-text">VirtualPhysics</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">← Volver al Dashboard</a>
                <a href="logout.php" class="logout-btn">Salir</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <?php if ($formulario): ?>
            <div class="form-card">
                <h2 class="form-title"><?php echo $formulario['titulo']; ?></h2>
                <p class="form-description"><?php echo $formulario['descripcion']; ?></p>
                
                <form action="procesar_formulario.php" method="POST">
                    <input type="hidden" name="formulario_id" value="<?php echo $formulario_id; ?>">
                    
                    <?php while($pregunta = mysqli_fetch_assoc($preguntas)): ?>
                        <div class="question">
                            <label class="question-label">
                                <?php echo $pregunta['orden']; ?>. <?php echo $pregunta['pregunta']; ?>
                            </label>
                            
                            <?php if($pregunta['tipo'] == 'texto'): ?>
                                <textarea name="respuesta_<?php echo $pregunta['id']; ?>" 
                                        class="textarea-field"
                                        placeholder="Escribe tu respuesta aquí..."></textarea>
                            
                            <?php elseif($pregunta['tipo'] == 'multiple'): ?>
                                <?php
                                $opciones = json_decode($pregunta['opciones'], true);
                                if($opciones):
                                    foreach($opciones as $opcion):
                                ?>
                                    <div class="radio-option">
                                        <label class="radio-label">
                                            <input type="radio" name="respuesta_<?php echo $pregunta['id']; ?>" 
                                                   value="<?php echo htmlspecialchars($opcion); ?>" class="radio-input">
                                            <?php echo $opcion; ?>
                                        </label>
                                    </div>
                                <?php 
                                    endforeach;
                                endif;
                                ?>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                    <?php 
                    // Liberar resultados
                    if ($preguntas) { mysqli_free_result($preguntas); } 
                    if ($resultado_formulario) { mysqli_free_result($resultado_formulario); } 
                    ?>
                    
                    <div class="submit-container">
                        <button type="submit" class="submit-btn">
                            Enviar Respuestas
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Nota informativa -->
            <div class="info-box">
                <p>
                    <strong>Nota:</strong> Todas tus respuestas serán guardadas para evaluar tu progreso.
                    Puedes volver a intentar este cuestionario más tarde si lo deseas.
                </p>
            </div>
            <?php else: ?>
            <div class="error-card">
                <h2 class="error-title">Error</h2>
                <p class="error-message">El formulario solicitado no existe o no se pudo cargar.</p>
                <a href="dashboard.php" class="back-btn">
                    Volver al Dashboard
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
// Cerrar conexión
mysqli_close($conexion);
?>