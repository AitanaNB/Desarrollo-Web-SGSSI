<?php
ini_set('session.cookie_httponly', 1)
ini_set('session.cookie_secure', 1)
ini_set('session.use_only_cookies', 1)
session_start();

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

//funcion para eliminar los usuarios
function matausuarios($id){
	//primero borramos los coches para que no quede coche sin usuario
	$sql = "DELETE FROM coches WHERE propietario = $id";
        global $conn;
	mysqli_query($conn, $sql);
	$sql = "DELETE FROM usuarios WHERE id = $id";
	mysqli_query($conn, $sql);
	return;
}


//lo que revisa la url en busca del id
if (isset($_GET['br'])) {
    matausuarios($_GET['br']);
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?eliminado=1");
    exit();
}

// Consultar todos los coches con datos del propietario
$sql = "SELECT id,nombre,apellidos,dni,telefono,fecha_nacimiento,email,dinero,username,password
        FROM usuarios 
		WHERE es_admin = 0" ;
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head style="background-color: #c71435;">
    <title>Catálogo Universal de Usuarios - Admin</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header style="background-color: #c71435;">
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
            if (mysqli_num_rows($result) > 0) {
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

                while ($row = mysqli_fetch_assoc($result)) {
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
                    echo "<td><a href='?br=" . $row['id'] . "' onclick=\"return confirm('¿Seguro que deseas eliminar este usuario?');\">Eliminar</a></td>";
                    echo "</tr>";
                }

                echo "</table>";
                
            } else {
                echo "No hay usuarios (no admin) registrados en el catálogo.";
            }
            
            // Cerrar la conexión
            mysqli_close($conn);
        ?>
    </main>
    <footer>
        <a href="https://github.com/AitanaNB/Desarrollo-Web-SGSSI">Nuestro maravilloso y organizado código está disponible en Github</a>
    </footer>
    </body>
</html>
