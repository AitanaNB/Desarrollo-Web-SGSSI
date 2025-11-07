<?php

ini_set('session.cookie_httponly', 1); 
ini_set('session.cookie_secure', 1);  
ini_set('session.use_only_cookies', 1); 

session_start();

// Para PHP 7.2.2, configuramos SameSite manualmente
// Se obtiene información de la sesión actual
$session_name = session_name();
$session_id = session_id();
if (!empty($session_id)) {
    $path = $current_params['path'];
    $domain = $current_params['domain'];
    $secure = $current_params['secure'] ? 'Secure;' : '';
    // SameSite Attribute
    // Aquí enviamos todos los atributos explícitamente en una sola cabecera segura.
    header("Set-Cookie: {$session_name}={$session_id}; Path={$path}; {$secure}HttpOnly; SameSite=Lax", true); 
}

// Content-Security-Policy (CSP)
/* Nota: Se requiere 'unsafe-inline' y 'unsafe-eval' para que el código actual funcione
debido al uso de JavaScript/CSS en línea y jQuery. Para una solución completa, 
se debe migrar el código en línea a archivos externos o usar Nonces/Hashes. */
$csp_policy = "default-src 'self'; ";
$csp_policy .= "script-src 'self'; "; 
$csp_policy .= "style-src 'self'; ";
$csp_policy .= "img-src 'self' data:; "; 
$csp_policy .= "frame-ancestors 'none';"; // Alternativa al X-Frame-Options

// Agrega directivas sin fallback (base-uri y form-action)
$csp_policy .= "base-uri 'self'; ";
$csp_policy .= "form-action 'self'; ";
header("Content-Security-Policy: " . $csp_policy);

//NOTA toda la parte de borrar usuarios funciona igual que en catalogoCocheAdmin.php, recomiendo mirarla en esa hoja para entenderla, porque a demás es más sencilla, en esta el mataUsuarios tiene dos sqls, no solo 1 (pero al margen de eso es lo mismo)

// Verificar que el usuario esté logueado y sea admin
if (!isset($_SESSION['usuario'])) {
    echo "Debes iniciar sesión para acceder a esta página.";
    exit();
}

// Solo permitir si es admin
if ($_SESSION['es_admin'] != 1) {
    echo "Acceso denegado. Solo el administrador puede ver esta página.";
    exit();
}

// Conexión a la base de datos
include 'bdcon.php';

try{
	// Consultar todos los usuarios del sistema (La otra consulta en que no insertamos nada)
	$sql = "SELECT id,nombre,apellidos,dni,telefono,fecha_nacimiento,email,dinero,username,password
        FROM usuarios 
		WHERE es_admin = 0" ;
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
}catch (PDOException $e) {
		echo "<p>Error al coger todos los usuarios: " . htmlspecialchars($e->getMessage()) . "</p>";
		exit();
} 
?>

<!DOCTYPE html>
<html>
<head class="admin-header">
    <title>Catálogo Universal de Usuarios - Admin</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
</head>

<!-- popup confirmación -->
<div id="popup-confirmacion-admin">
	<h2  id="texto-confirmacion-admin" >¿Admin, seguro que quieres  ELIMINAR al usuario?</h2>
	<p>Esta acción no se puede deshacer.</p>
	<button class="boton-popup" id="boton-confirmar-eliminacion" >Si</button>
    <button class="boton-popup" id="boton-remordimientos">No</button>
</div>


<body>
    <header class="admin-header">
        <div class="header-container">
            <div>
                <h1>Catálogo Universal de Usuarios (Admin)</h1>
                <p>Gestión completa de todos los usuarios - Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
            </div>
        </div>
    </header>

    <nav>
        <a href="inicio.php">Inicio</a> |
        <a href="catalogoCocheAdmin.php">Catálogo de coches</a> |
        <a href="logout.php">Cerrar sesión (<?php echo htmlspecialchars($_SESSION['usuario']); ?>)</a>
    </nav>

    <main>
        <?php
            // Mostrar mensaje de éxito si se eliminó un coche
            if (isset($_GET['eliminado']) && $_GET['eliminado'] == 1) {
                echo '<div class="alert alert-success"> Usuario eliminado correctamente</div>';
            }

            // Verificar si la consulta fue exitosa y tiene resultados
            if ($result) {
                echo "<table border='1' cellpadding='8' cellspacing='0'>";
                echo "<tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>DNI</th>
                        <th>Telefono</th>
                        <th>Fecha de Nacimiento</th>
                        <th>Email</th>
                        <th>Dinero</th>
                        <th>Nombre De Usuario</th>
                        <th>Contraseña</th>
                        <th>Acción</th>
                    </tr>";

                foreach($result as $row) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['apellidos']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['dni']) . "</td>";
                    echo "<td>" . number_format($row['telefono']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['fecha_nacimiento']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>€" . number_format($row['dinero'], 2) . "</td>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['password']) . "</td>";
                    //Como le vamos a meter un onClick en javaScript, no hace falta que aquí tenga nada de eso
                    echo "<td><a class='catalogo-admins-eliminar'>Eliminar</a></td>";
                    echo "</tr>";
                }

                echo "</table>";
                
            } else {
                echo "No hay usuarios (no admin) registrados en el catálogo.";
            }
            
            // No hace falta cerrar la conexión
        ?>
    </main>
    <footer>
        <a href="https://github.com/AitanaNB/Desarrollo-Web-SGSSI">Nuestro maravilloso y organizado código está disponible en Github</a>
    </footer>
    </body>
</html>

<!-- esto de aquí no es un script, es un link a la libreria de las Jqueris que le permite al java script de esta página utilizarla-->
<script src="../js/jquery-3.5.1.min.js"></script>

<!-- a continuación, un link a el codigo de los onClick event de eliminar-->
<script src="../js/catalogoCuentaAdmin.js"></script>