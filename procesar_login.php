<?php
session_start();
include 'config/database.php';

// --- VULNERABILIDAD: Sin validación de método POST ---
// Se asume que los datos siempre vienen por POST.

// --- VULNERABILIDAD: Obtener datos directamente de POST sin saneamiento ---
$correo = $_POST['correo'];
$contrasena = $_POST['contrasena'];

// --- VULNERABILIDAD: SQL Injection ---
// La consulta concatena directamente la variable $correo sin saneamiento.
// Un atacante puede inyectar código SQL malicioso en el campo correo.
// Ejemplo de explotación en correo: ' OR 1=1) -- 
// Esto hará que la condición WHERE sea siempre verdadera y se saltará la validación de contraseña.

// IMPORTANTE: Reorganizamos la consulta con paréntesis para facilitar la inyección SQL
// Esta estructura permite que la inyección en el campo correo funcione sin romper la sintaxis de SHA2
$query = "SELECT * FROM usuarios WHERE (correo = '$correo') AND (contrasena = SHA2('$contrasena', 256))";

// --- VULNERABILIDAD: Sin protección contra fuerza bruta ---
// No hay límite de intentos de login.

$resultado = mysqli_query($conexion, $query);

// --- VULNERABILIDAD: Manejo de errores inseguro ---
// Podría revelar información si mysqli_query falla y se muestran errores.
if (!$resultado) {
    // En un entorno real, loguear el error, no mostrarlo directamente.
    header("Location: index.php?error=Error en la consulta: " . mysqli_error($conexion));
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
    
    // MODIFICADO: Separamos el correo de la consulta SQL para permitir pruebas de XSS
    // sin romper la sintaxis SQL. Ahora el XSS se puede probar directamente en la URL.
    $error_msg = "Credenciales incorrectas. Intente nuevamente.";
    header("Location: index.php?error=" . $error_msg . "&correo=" . $correo);
    exit();
}

// Cerrar conexión (aunque en scripts cortos como este, no es estrictamente necesario)
mysqli_close($conexion);
?>
