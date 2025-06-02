<?php
session_start();
include 'config/database.php';

// Sin validación de sesión adecuada
if(!isset($_SESSION['usuario_codigo'])) {
    header("Location: index.php");
    exit();
}

// --- VULNERABILIDAD: SQL Injection en parámetro GET 'id' ---
// El ID del formulario se toma directamente de GET sin saneamiento.
// Un atacante puede manipular la URL: formularios.php?id=1 UNION SELECT null, version(), null, null, null -- 
$formulario_id = $_GET['id']; 

// --- VULNERABILIDAD: SQL Injection ---
// La variable $formulario_id se concatena directamente en la consulta.
$query_formulario = "SELECT * FROM formularios WHERE id = $formulario_id";
$resultado_formulario = mysqli_query($conexion, $query_formulario);

// --- VULNERABILIDAD: Manejo de errores inseguro ---
if (!$resultado_formulario) {
    die("Error al consultar formulario: " . mysqli_error($conexion)); // Revela errores SQL
}
$formulario = mysqli_fetch_assoc($resultado_formulario);

// --- VULNERABILIDAD: SQL Injection ---
// La variable $formulario_id se concatena directamente en la consulta.
$query_preguntas = "SELECT * FROM preguntas WHERE formulario_id = $formulario_id ORDER BY orden";
$preguntas = mysqli_query($conexion, $query_preguntas);

// --- VULNERABILIDAD: Manejo de errores inseguro ---
if (!$preguntas) {
    die("Error al consultar preguntas: " . mysqli_error($conexion)); // Revela errores SQL
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- --- VULNERABILIDAD: Stored XSS --- -->
    <!-- El título del formulario se usa en el <title> sin escapar. -->
    <title><?php echo $formulario ? $formulario['titulo'] : 'Formulario no encontrado'; ?> - VirtualPhysics</title>
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
        <?php if ($formulario): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- --- VULNERABILIDAD: Stored XSS --- -->
            <!-- Título y descripción se muestran directamente desde la BD sin escapar. -->
            <h2 class="text-2xl font-bold mb-4 text-blue-600"><?php echo $formulario['titulo']; ?></h2>
            <p class="text-gray-600 mb-6"><?php echo $formulario['descripcion']; ?></p>
            
            <form action="procesar_formulario.php" method="POST" class="space-y-6">
                <!-- El ID se pasa como hidden, podría ser manipulado por el cliente -->
                <input type="hidden" name="formulario_id" value="<?php echo $formulario_id; ?>">
                
                <?php while($pregunta = mysqli_fetch_assoc($preguntas)): ?>
                    <div class="border-b pb-4">
                        <label class="block text-lg font-medium text-gray-700 mb-3">
                            <!-- --- VULNERABILIDAD: Stored XSS --- -->
                            <!-- Texto de la pregunta se muestra directamente desde la BD. -->
                            <?php echo $pregunta['orden']; ?>. <?php echo $pregunta['pregunta']; ?>
                        </label>
                        
                        <?php if($pregunta['tipo'] == 'texto'): ?>
                            <textarea name="respuesta_<?php echo $pregunta['id']; ?>" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    rows="3" placeholder="Escribe tu respuesta aquí..."></textarea>
                        
                        <?php elseif($pregunta['tipo'] == 'multiple'): ?>
                            <?php
                            // --- VULNERABILIDAD: Potencial problema si el JSON está mal formado o contiene XSS --- 
                            // Aunque json_decode es relativamente seguro, si las opciones en la BD 
                            // contienen HTML/script y se muestran sin escapar, hay XSS.
                            $opciones = json_decode($pregunta['opciones'], true);
                            if($opciones):
                                foreach($opciones as $opcion):
                            ?>
                                <div class="mb-2">
                                    <label class="flex items-center">
                                        <!-- El valor del radio button podría ser un vector si se usa inseguramente -->
                                        <input type="radio" name="respuesta_<?php echo $pregunta['id']; ?>" 
                                               value="<?php echo htmlspecialchars($opcion); // Escapamos el valor por si acaso, aunque el texto es el principal vector ?>" class="mr-2">
                                        <!-- --- VULNERABILIDAD: Stored XSS --- -->
                                        <!-- El texto de la opción se muestra directamente. -->
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
                
                <div class="text-center">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium">
                        Enviar Respuestas
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Pista de inyección SQL -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
            <p class="text-sm text-yellow-800">
                <strong>Nota Académica:</strong> El parámetro 'id' en la URL es vulnerable a SQL Injection.
                Prueba añadiendo ` UNION SELECT null, version(), null, null, null -- ` al ID.
                URL actual: formularios.php?id=<?php echo htmlspecialchars($formulario_id); // Mostramos el ID escapado aquí para no romper la página ?>
            </p>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <h2 class="text-2xl font-bold mb-4 text-red-600">Error</h2>
            <p class="text-gray-600">El formulario solicitado no existe o no se pudo cargar.</p>
            <a href="dashboard.php" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Volver al Dashboard
            </a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
// Cerrar conexión
mysqli_close($conexion);
?>
