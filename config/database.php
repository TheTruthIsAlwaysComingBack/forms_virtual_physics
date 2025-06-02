<?php
// Configuración de base de datos (VULNERABLE - credenciales expuestas)
$servidor = "127.0.0.1";
$usuario = "root";
$password = "";
$base_datos = "virtualphysics";

// Conexión vulnerable sin validación
$conexion = mysqli_connect($servidor, $usuario, $password, $base_datos);

// Sin validación de conexión
if (!$conexion) {
    // En un entorno real, no deberías exponer detalles del error así.
    die("Error de conexión: " . mysqli_connect_error()); 
}

// Sin configuración de charset seguro explícito después de la conexión (aunque utf8 es común)
mysqli_set_charset($conexion, "utf8");
?>