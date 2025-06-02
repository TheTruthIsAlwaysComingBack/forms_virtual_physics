<?php
session_start();
include 'config/database.php';

// Sin validación de sesión adecuada
if(!isset($_SESSION['usuario_codigo'])) {
    header("Location: index.php");
    exit();
}

// Consulta para obtener formularios activos
$query_formularios = "SELECT * FROM formularios WHERE activo = 1";
$formularios = mysqli_query($conexion, $query_formularios);

// Verificar si la consulta fue exitosa
if (!$formularios) {
    die("Error al consultar formularios: " . mysqli_error($conexion));
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - VirtualPhysics</title>
    <link rel="stylesheet" href="css/dashboard.css">
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
            <div class="user-actions">
                <span class="welcome-text">Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?></span>
                <a href="logout.php" class="logout-btn">Salir</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="container">
            <div class="card">
                <h2 class="card-title">Cuestionarios Disponibles</h2>
                
                <div class="formularios-grid">
                    <?php while($formulario = mysqli_fetch_assoc($formularios)): ?>
                        <div class="formulario-card">
                            <h3 class="formulario-title"><?php echo $formulario['titulo']; ?></h3>
                            <p class="formulario-description"><?php echo $formulario['descripcion']; ?></p>
                            <a href="formularios.php?id=<?php echo $formulario['id']; ?>" class="btn btn-primary">
                                Comenzar Cuestionario
                            </a>
                        </div>
                    <?php endwhile; ?>
                    <?php 
                    // Liberar resultado si es necesario
                    if ($formularios) { mysqli_free_result($formularios); } 
                    ?>
                </div>
            </div>
            
            <!-- Información de temas -->
            <div class="card">
                <h3 class="card-title">Temas de Física Cubiertos</h3>
                <div class="temas-grid">
                    <div class="tema-card">
                        <h4 class="tema-title">MRU</h4>
                        <p class="tema-description">Movimiento Rectilíneo Uniforme</p>
                    </div>
                    <div class="tema-card">
                        <h4 class="tema-title">MRUA</h4>
                        <p class="tema-description">Movimiento Uniformemente Acelerado</p>
                    </div>
                    <div class="tema-card">
                        <h4 class="tema-title">Caída Libre</h4>
                        <p class="tema-description">Movimiento bajo gravedad</p>
                    </div>
                    <div class="tema-card">
                        <h4 class="tema-title">Tiro Vertical</h4>
                        <p class="tema-description">Lanzamiento hacia arriba</p>
                    </div>
                    <div class="tema-card">
                        <h4 class="tema-title">Tiro Parabólico</h4>
                        <p class="tema-description">Movimiento de proyectiles</p>
                    </div>
                    <div class="tema-card">
                        <h4 class="tema-title">Movimiento Circular</h4>
                        <p class="tema-description">Circular Uniforme</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Cerrar conexión
mysqli_close($conexion);
?>
