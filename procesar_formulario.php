<?php
session_start();
include 'config/database.php';

// Sin validación de sesión adecuada
if(!isset($_SESSION['usuario_codigo'])) {
    header("Location: index.php");
    exit();
}

// --- VULNERABILIDAD: Confiar en datos POST sin validación ---
// Se asume que usuario_codigo y formulario_id son correctos.
$usuario_codigo = $_SESSION['usuario_codigo'];
$formulario_id = $_POST['formulario_id'];

// --- VULNERABILIDAD: Procesar todas las respuestas sin validación de tipo o contenido ---
foreach($_POST as $key => $value) {
    // Solo procesar claves que empiezan con 'respuesta_'
    if(strpos($key, 'respuesta_') === 0) {
        $pregunta_id = str_replace('respuesta_', '', $key);
        
        // --- VULNERABILIDAD: SQL Injection en INSERT ---
        // Los valores $usuario_codigo, $formulario_id, $pregunta_id y $value 
        // se concatenan directamente en la consulta INSERT.
        // Un atacante podría manipular el valor de una respuesta ($value) para inyectar SQL.
        // Ejemplo: Si $value es "maliciosa', (SELECT @@version)) -- ", podría insertar la versión de SQL.
        // Nota: La efectividad depende de cómo se manejen las comillas y los tipos de datos en la BD.
        $query = "INSERT INTO respuestas (usuario_codigo, formulario_id, pregunta_id, respuesta) 
                  VALUES ('$usuario_codigo', '$formulario_id', '$pregunta_id', '$value')";
        
        // --- VULNERABILIDAD: Manejo de errores inseguro ---
        $resultado_insert = mysqli_query($conexion, $query);
        if (!$resultado_insert) {
            // En producción, loguear el error, no mostrarlo.
            // Podría revelar estructura de la tabla o datos.
            echo "Error al guardar respuesta para pregunta $pregunta_id: " . mysqli_error($conexion) . "<br>";
            // Considerar si continuar o detener el script aquí.
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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">VirtualPhysics</h1>
            <div class="flex items-center space-x-4">
                <a href="dashboard.php" class="hover:underline">Dashboard</a>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8 px-4 max-w-2xl">
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-800 mb-4">¡Respuestas Enviadas!</h2>
            <p class="text-gray-600 mb-6">Tus respuestas han sido guardadas (o al menos, se intentó guardar) en la base de datos.</p>
            
            <div class="space-x-4">
                <a href="dashboard.php" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
                    Volver al Dashboard
                </a>
                <!-- --- VULNERABILIDAD: Potencial XSS si $formulario_id es manipulado --- -->
                <!-- Aunque $formulario_id viene de un hidden POST, podría ser manipulado. -->
                <!-- Si $formulario_id contuviera script y no se escapara aquí, podría haber XSS. -->
                <a href="formularios.php?id=<?php echo htmlspecialchars($formulario_id); // Escapamos aquí por seguridad ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">
                    Ver Formulario de Nuevo
                </a>
            </div>
        </div>
        
        <!-- Debug info (vulnerable a XSS si las variables contienen scripts) -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-4">
            <p class="text-sm text-red-800">
                <strong>Debug Info:</strong> Respuestas procesadas para usuario 
                <!-- --- VULNERABILIDAD: XSS Reflejado/Stored --- -->
                <!-- Se muestran $usuario_codigo y $formulario_id directamente. -->
                <!-- Si $usuario_codigo (de sesión, potencialmente Stored XSS) o $formulario_id (de POST) -->
                <!-- contienen script, se ejecutarán aquí. -->
                <?php echo $usuario_codigo; ?> 
                en formulario <?php echo $formulario_id; ?>.
                <br>
                <i>(Nota: Mostrar información de debug así es inseguro)</i>
            </p>
        </div>
    </div>
</body>
</html>
<?php
// Cerrar conexión
mysqli_close($conexion);
?>
