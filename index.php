<?php
session_start();
// Sin validación de sesión existente
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VirtualPhysics - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-blue-600">VirtualPhysics</h1>
            <p class="text-gray-600">Sistema de Cuestionarios de Física</p>
        </div>
        
        <!-- Mostrar errores sin filtrar (XSS vulnerable) -->
        <?php if(isset($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                Error: <?php echo $_GET['error']; ?>
            </div>
        <?php endif; ?>
        
        <form action="procesar_login.php" method="POST" class="space-y-6">
            <div>
                <label for="correo" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="contrasena" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Iniciar Sesión
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Usuario de prueba: <strong>izi@gmail.com</strong><br>
                Contraseña: <strong>123456</strong>
            </p>
        </div>
    </div>
</body>
</html>