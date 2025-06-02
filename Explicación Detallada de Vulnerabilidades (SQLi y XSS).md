# Explicación Detallada de Vulnerabilidades (SQLi y XSS)

Este documento detalla las vulnerabilidades de Inyección SQL (SQLi) y Cross-Site Scripting (XSS) introducidas intencionalmente en el código PHP del proyecto VirtualPhysics para tu asignación de pentesting. **Recuerda que este código es inseguro y solo debe usarse en un entorno controlado y con fines educativos.**

## Estructura del Proyecto

Los archivos modificados se encuentran en la carpeta `FORMS` y siguen la estructura que proporcionaste:

```
FORMS/
├── config/
│   └── database.php       (Configuración de BD - vulnerable)
├── css/                   (Archivos CSS - no modificados)
│   └── styles.css
├── img/                   (Imágenes - no modificadas)
├── includes/              (Includes - no modificados)
│   ├── footer.php
│   └── header.php
├── js/                    (JavaScript - no modificado)
│   └── scripts.js
├── dashboard.php          (Vulnerable a Stored XSS)
├── formularios.php        (Vulnerable a SQLi y Stored XSS)
├── index.php              (Vulnerable a Reflected XSS)
├── logout.php             (Funcionalidad de logout)
├── procesar_formulario.php (Vulnerable a SQLi y XSS)
├── procesar_login.php     (Vulnerable a SQLi y Reflected XSS)
└── VirtualPhysics.sql     (Archivo SQL para crear la estructura de BD)
```

## Vulnerabilidades Introducidas

### 1. Inyección SQL (SQLi)

La Inyección SQL ocurre cuando la entrada del usuario se inserta directamente en una consulta SQL sin el saneamiento adecuado (como usar consultas preparadas). Esto permite a un atacante manipular la consulta para extraer datos, modificar datos o incluso tomar control del servidor de base de datos.

**a) `procesar_login.php` (Autenticación)**

*   **Línea:** ` $query = "SELECT * FROM usuarios WHERE correo = ".$correo." AND contrasena = SHA2(".$contrasena.", 256)"; `
*   **Vulnerabilidad:** El `$correo` y `$contrasena` se concatenan directamente. Aunque la contraseña usa `SHA2`, lo que dificulta un bypass simple con `OR 1=1` en ese campo, la vulnerabilidad en `$correo` persiste.
*   **Explotación (Ejemplo en campo Correo):** Introduce como correo: `admin@example.com' OR '1'='1' -- ` (el `-- ` comenta el resto de la consulta). Esto podría permitir el login como el primer usuario de la tabla si la lógica SQL lo permite, o causar un error que revele información.
*   **Nota:** La efectividad depende de la configuración exacta de la base de datos y si existen usuarios.

**b) `formularios.php` (Carga de Formulario y Preguntas)**

*   **Línea:** ` $formulario_id = $_GET["id"]; `
*   **Líneas de Consulta:**
    *   ` $query_formulario = "SELECT * FROM formularios WHERE id = $formulario_id"; `
    *   ` $query_preguntas = "SELECT * FROM preguntas WHERE formulario_id = $formulario_id ORDER BY orden"; `
*   **Vulnerabilidad:** El parámetro `id` de la URL (`$_GET['id']`) se concatena directamente en las consultas SQL.
*   **Explotación (Ejemplo en URL):** Accede a `formularios.php?id=1 UNION SELECT null, version(), null, null, null -- `. Esto intentará unir el resultado de `version()` (la versión de la base de datos) a la consulta original. Podrías necesitar ajustar el número de `null` para que coincida con las columnas de la tabla `formularios` o `preguntas`. También puedes usar SQLi basado en errores.

**c) `procesar_formulario.php` (Guardado de Respuestas)**

*   **Línea dentro del bucle `foreach`:** ` $query = "INSERT INTO respuestas (usuario_codigo, formulario_id, pregunta_id, respuesta) VALUES (".$usuario_codigo.", ".$formulario_id.", ".$pregunta_id.", ".$value.")"; `
*   **Vulnerabilidad:** El valor de la respuesta (`$value`, que viene de `$_POST`) se concatena directamente en la consulta `INSERT`.
*   **Explotación (Ejemplo en un campo de respuesta del formulario):** Introduce como respuesta: `Mi respuesta', (SELECT @@version)) -- `. Esto intentará insertar la versión de la base de datos en la columna `respuesta` junto con tu texto.

### 2. Cross-Site Scripting (XSS)

El XSS ocurre cuando una aplicación web incluye datos no confiables (generalmente entrada del usuario) en una página web sin escaparlos correctamente. Esto permite a los atacantes ejecutar scripts maliciosos en el navegador de la víctima.

**a) `index.php` (Reflected XSS en Mensaje de Error)**

*   **Línea:** ` echo $_GET["error"]; `
*   **Vulnerabilidad:** El script `procesar_login.php` redirige a `index.php` incluyendo la entrada del usuario (`$correo`) en el parámetro `error` sin escapar. `index.php` muestra este parámetro directamente.
*   **Explotación:** Intenta iniciar sesión con un correo como `<script>alert('XSS en Correo');</script>`. Cuando `procesar_login.php` falle y redirija, la URL será algo como `index.php?error=Credenciales incorrectas para el correo: <script>alert('XSS en Correo');</script>`. El script se ejecutará porque `index.php` imprime el parámetro `error` sin `htmlspecialchars()`.

**b) `dashboard.php` (Stored XSS en Nombre de Usuario, Títulos/Descripciones de Formularios)**

*   **Líneas:**
    *   ` <span>Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?></span> `
    *   ` <?php echo $formulario['titulo']; ?> `
    *   ` <?php echo $formulario['descripcion']; ?> `
*   **Vulnerabilidad:** El nombre de usuario (almacenado en la sesión desde la base de datos) y los títulos/descripciones de los formularios (leídos de la base de datos) se muestran directamente sin `htmlspecialchars()`.
*   **Explotación:** Si un atacante logra insertar código JavaScript (ej. `<script>alert('Stored XSS')</script>`) en el nombre de un usuario o en el título/descripción de un formulario en la base de datos (quizás a través de otra vulnerabilidad o directamente si tiene acceso), ese script se ejecutará cada vez que un usuario visite el dashboard.

**c) `formularios.php` (Stored XSS en Título/Descripción/Preguntas/Opciones)**

*   **Líneas:**
    *   ` <title><?php echo $formulario ? $formulario['titulo'] : '...'; ?> - ...</title> `
    *   ` <h2 ...><?php echo $formulario['titulo']; ?></h2> `
    *   ` <p ...><?php echo $formulario['descripcion']; ?></p> `
    *   ` <?php echo $pregunta['pregunta']; ?> `
    *   ` <?php echo $opcion; ?> ` (dentro del bucle de opciones múltiples)
*   **Vulnerabilidad:** Similar al dashboard, los datos leídos de la base de datos (título, descripción, texto de pregunta, texto de opción) se muestran directamente.
*   **Explotación:** Si estos campos en la base de datos contienen scripts, se ejecutarán al ver el formulario.

**d) `procesar_formulario.php` (Reflected/Stored XSS en Debug Info)**

*   **Línea:** ` <?php echo $usuario_codigo; ?> en formulario <?php echo $formulario_id; ?> `
*   **Vulnerabilidad:** La información de depuración muestra `$usuario_codigo` (de la sesión, potencial Stored XSS si el código de usuario en la BD tuviera script) y `$formulario_id` (del POST, potencial Reflected XSS si se manipula el POST) directamente.
*   **Explotación:** Si `$usuario_codigo` o `$formulario_id` contienen script, se ejecutarán en esta página de confirmación.

### 3. Otras Vulnerabilidades Menores (Presentes en el código original o introducidas)

*   **`config/database.php`:** Credenciales de base de datos hardcodeadas (root sin contraseña). En un entorno real, esto es extremadamente peligroso.
*   **Falta de Validación de Método HTTP:** Scripts como `procesar_login.php` no verifican si la solicitud es POST, aunque dependen de `$_POST`.
*   **Manejo de Errores Inseguro:** En varios puntos, `mysqli_error($conexion)` se usa o se comenta, lo que podría revelar información sensible de la base de datos si los errores se mostraran al usuario.
*   **Sin Protección CSRF:** Los formularios no incluyen tokens CSRF, haciéndolos vulnerables a ataques Cross-Site Request Forgery.
*   **Sin Límite de Intentos (Fuerza Bruta):** El login no limita los intentos fallidos.
*   **Validación de Sesión Simple:** Solo se verifica `isset($_SESSION['usuario_codigo'])`.

## Configuración del Entorno

1.  **Servidor Web:** Necesitarás un servidor web con PHP y MySQL/MariaDB (como XAMPP, WAMP, MAMP o un servidor Linux configurado).
2.  **Base de Datos:**
    *   Crea una base de datos llamada `virtualphysics`.
    *   Importa el archivo `VirtualPhysics.sql` proporcionado para crear las tablas (`usuarios`, `formularios`, `preguntas`, `respuestas`).
    *   Asegúrate de que el usuario de la base de datos (`root` sin contraseña según `database.php`) tenga permisos sobre esta base de datos.
3.  **Código:** Coloca la carpeta `FORMS` completa en el directorio raíz de tu servidor web (ej. `htdocs` en XAMPP).
4.  **Acceso:** Accede a la aplicación a través de tu navegador (ej. `http://localhost/FORMS/`).

## Advertencia Final

Este código es deliberadamente inseguro. Úsalo responsablemente para aprender y practicar en tu entorno local controlado. Nunca despliegues este código ni uses estas técnicas en sistemas reales sin autorización explícita. La forma correcta de prevenir estas vulnerabilidades incluye el uso de consultas preparadas, saneamiento y escape de todas las entradas y salidas (ej. `htmlspecialchars`), validación robusta de datos, manejo seguro de sesiones y errores, y tokens CSRF.
