<?php
session_start();
include 'config/database.php';

// VULNERABILIDAD: Sin validación de método POST
$correo = $_POST['correo'];
$contrasena = $_POST['contrasena'];

// VULNERABILIDAD: SQL Injection - consulta directa sin prepared statements
$query = "SELECT * FROM usuarios WHERE correo = '$correo' AND contrasena = SHA2('$contrasena', 256)";

// Sin límite de intentos (vulnerable a fuerza bruta)
$resultado = mysqli_query($conexion, $query);

if(mysqli_num_rows($resultado) > 0) {
    $usuario = mysqli_fetch_assoc($resultado);
    
    // Almacenar datos en sesión sin validación
    $_SESSION['usuario_codigo'] = $usuario['codigo'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_correo'] = $usuario['correo'];
    
    // Redirección sin validación
    header("Location: dashboard.php");
    exit();
} else {
    // VULNERABILIDAD: XSS - parámetros sin filtrar
    header("Location: index.php?error=Credenciales incorrectas. Intenta con: ' OR '1'='1");
    exit();
}
?>