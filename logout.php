<?php
session_start();

// Destruir sesión sin validaciones adicionales
session_destroy();

// Redirección simple
header("Location: index.php");
exit();
?>