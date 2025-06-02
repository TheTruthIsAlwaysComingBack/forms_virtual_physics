<?php
session_start();
include 'config/database.php';

// Sin validación de sesión adecuada
if(!isset($_SESSION['usuario_codigo'])) {
    header("Location: index.php");
    exit();
}

// Consulta vulnerable para obtener formularios
$query_formularios = "SELECT * FROM formularios WHERE activo = 1";
$formularios = mysqli_query($conexion, $query_formularios);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - VirtualPhysics</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">VirtualPhysics</h1>
            <div class="flex items-center space-x-4">
                <!-- XSS Vulnerable - sin escapar -->
                <span>Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?></span>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8 px-4">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Cuestionarios Disponibles</h2>
            
            <div class="grid md:grid-cols-2 gap-6">
                <?php while($formulario = mysqli_fetch_assoc($formularios)): ?>
                    <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow">
                        <h3 class="text-lg font-semibold text-blue-600 mb-2">
                            <?php echo $formulario['titulo']; ?>
                        </h3>
                        <p class="text-gray-600 mb-4">
                            <?php echo $formulario['descripcion']; ?>
                        </p>
                        <a href="formularios.php?id=<?php echo $formulario['id']; ?>" 
                           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded inline-block">
                            Comenzar Cuestionario
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Información de temas -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h3 class="text-xl font-bold mb-4 text-gray-800">Temas de Física Cubiertos</h3>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-blue-50 p-3 rounded">
                    <h4 class="font-semibold text-blue-600">MRU</h4>
                    <p class="text-sm text-gray-600">Movimiento Rectilíneo Uniforme</p>
                </div>
                <div class="bg-green-50 p-3 rounded">
                    <h4 class="font-semibold text-green-600">MRUA</h4>
                    <p class="text-sm text-gray-600">Movimiento Uniformemente Acelerado</p>
                </div>
                <div class="bg-purple-50 p-3 rounded">
                    <h4 class="font-semibold text-purple-600">Caída Libre</h4>
                    <p class="text-sm text-gray-600">Movimiento bajo gravedad</p>
                </div>
                <div class="bg-red-50 p-3 rounded">
                    <h4 class="font-semibold text-red-600">Tiro Vertical</h4>
                    <p class="text-sm text-gray-600">Lanzamiento hacia arriba</p>
                </div>
                <div class="bg-yellow-50 p-3 rounded">
                    <h4 class="font-semibold text-yellow-600">Tiro Parabólico</h4>
                    <p class="text-sm text-gray-600">Movimiento de proyectiles</p>
                </div>
                <div class="bg-indigo-50 p-3 rounded">
                    <h4 class="font-semibold text-indigo-600">Movimiento Circular</h4>
                    <p class="text-sm text-gray-600">Circular Uniforme</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>