<?php
session_start();
include 'config/database.php';

// Sin validación de sesión
if(!isset($_SESSION['usuario_codigo'])) {
    header("Location: index.php");
    exit();
}

// VULNERABILIDAD: SQL Injection en parámetro GET
$formulario_id = $_GET['id'];
$query_formulario = "SELECT * FROM formularios WHERE id = $formulario_id";
$resultado_formulario = mysqli_query($conexion, $query_formulario);
$formulario = mysqli_fetch_assoc($resultado_formulario);

// Consulta vulnerable para preguntas
$query_preguntas = "SELECT * FROM preguntas WHERE formulario_id = $formulario_id ORDER BY orden";
$preguntas = mysqli_query($conexion, $query_preguntas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $formulario['titulo']; ?> - VirtualPhysics</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">VirtualPhysics</h1>
            <div class="flex items-center space-x-4">
                <a href="dashboard.php" class="hover:underline">← Volver al Dashboard</a>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8 px-4 max-w-3xl">
        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- XSS Vulnerable - sin escapar datos -->
            <h2 class="text-2xl font-bold mb-4 text-blue-600"><?php echo $formulario['titulo']; ?></h2>
            <p class="text-gray-600 mb-6"><?php echo $formulario['descripcion']; ?></p>
            
            <form action="procesar_formulario.php" method="POST" class="space-y-6">
                <input type="hidden" name="formulario_id" value="<?php echo $formulario_id; ?>">
                
                <?php while($pregunta = mysqli_fetch_assoc($preguntas)): ?>
                    <div class="border-b pb-4">
                        <label class="block text-lg font-medium text-gray-700 mb-3">
                            <?php echo $pregunta['orden']; ?>. <?php echo $pregunta['pregunta']; ?>
                        </label>
                        
                        <?php if($pregunta['tipo'] == 'texto'): ?>
                            <textarea name="respuesta_<?php echo $pregunta['id']; ?>" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    rows="3" placeholder="Escribe tu respuesta aquí..."></textarea>
                        
                        <?php elseif($pregunta['tipo'] == 'multiple'): ?>
                            <?php
                            // Vulnerable - sin validación de JSON
                            $opciones = json_decode($pregunta['opciones'], true);
                            if($opciones):
                                foreach($opciones as $opcion):
                            ?>
                                <div class="mb-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="respuesta_<?php echo $pregunta['id']; ?>" 
                                               value="<?php echo $opcion; ?>" class="mr-2">
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
                
                <div class="text-center">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium">
                        Enviar Respuestas
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Pista de inyección SQL para hackers -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
            <p class="text-sm text-yellow-800">
                <strong>Nota:</strong> Este formulario procesa datos directamente. 
                URL actual: formularios.php?id=<?php echo $formulario_id; ?>
            </p>
        </div>
    </div>
</body>
</html>