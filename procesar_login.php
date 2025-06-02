<?php
session_start();
include 'config/database.php';

// --- VULNERABILIDAD: Sin validación de método POST ---
// Se asume que los datos siempre vienen por POST.

// --- VULNERABILIDAD: Obtener datos directamente de POST sin saneamiento ---
$correo = $_POST['correo'];
$contrasena = $_POST['contrasena'];

// --- VULNERABILIDAD: SQL Injection ---
// La consulta concatena directamente las variables $correo y $contrasena.
// Un atacante puede inyectar código SQL malicioso.
// Ejemplo de explotación en correo: admin@example.com' OR '1'='1' -- 
// Ejemplo de explotación en contraseña: ' OR '1'='1' -- 
// Nota: La base de datos usa SHA2 para la contraseña, lo que dificulta la inyección directa 
// en la contraseña para bypass simple, pero la vulnerabilidad en el correo sigue presente 
// y otras técnicas de SQLi (ej. basadas en errores, unión) podrían ser posibles.
// Para fines demostrativos, simplificaremos asumiendo que un atacante podría encontrar una forma
// o que la función SHA2 no se aplicara correctamente en todos los casos.
// Mantenemos la estructura vulnerable como se solicitó.
$query = "SELECT * FROM usuarios WHERE correo = '$correo' AND contrasena = SHA2('$contrasena', 256)";

// --- VULNERABILIDAD: Sin protección contra fuerza bruta ---
// No hay límite de intentos de login.

$resultado = mysqli_query($conexion, $query);

// --- VULNERABILIDAD: Manejo de errores inseguro ---
// Podría revelar información si mysqli_query falla y se muestran errores.
if (!$resultado) {
    // En un entorno real, loguear el error, no mostrarlo directamente.
    // header("Location: index.php?error=Error en la consulta: " . mysqli_error($conexion));
    // Para la demo, simplemente redirigimos a un error genérico
    header("Location: index.php?error=Error interno del servidor.");
    exit();
}

if(mysqli_num_rows($resultado) > 0) {
    $usuario = mysqli_fetch_assoc($resultado);
    
    // --- VULNERABILIDAD: Almacenar datos en sesión sin saneamiento ---
    // El nombre de usuario se guarda tal cual viene de la BD.
    // Si un nombre en la BD contiene script malicioso (ej. por otra vulnerabilidad),
    // se almacenará aquí y podría ejecutarse en otras páginas (Stored XSS).
    $_SESSION['usuario_codigo'] = $usuario['codigo'];
    $_SESSION['usuario_nombre'] = $usuario['nombre']; // Potencial Stored XSS
    $_SESSION['usuario_correo'] = $usuario['correo'];
    
    // Redirección simple tras login exitoso
    header("Location: dashboard.php");
    exit();
} else {
    // --- VULNERABILIDAD: XSS Reflejado (Reflected XSS) ---
    // El mensaje de error incluye la entrada del usuario ($correo) sin escapar adecuadamente.
    // Si un atacante manipula la URL o el POST para incluir script en 'correo',
    // y el index.php muestra el parámetro 'error' sin escapar, el script se ejecutará.
    // Ejemplo: correo = <script>alert('XSS')</script>
    $error_msg = "Credenciales incorrectas para el correo: " . $correo; 
    // ¡Importante! No usamos urlencode() aquí para permitir la inyección XSS en el parámetro 'error'.
    // En index.php, el mensaje de error debe mostrarse sin htmlspecialchars() para que funcione.
    header("Location: index.php?error=" . $error_msg);
    exit();
}

// Cerrar conexión (aunque en scripts cortos como este, no es estrictamente necesario)
mysqli_close($conexion);
?>