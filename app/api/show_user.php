<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Conexión a la base de datos
include 'bdcon.php';

if (!$conn) {
    die("<div class='alert alert-error'>Conexión fallida: " . mysqli_connect_error() . "</div>");
}
// Obtener el ID del usuario logueado
$user_id = $_SESSION['user_id'];

// Si se ha enviado el formulario, actualizamos los datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $dni = $_POST['dni'];
    $telefono = $_POST['telefono'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $email = $_POST['email'];
    $dinero = $_POST['dinero'];
    $username = $_POST['username'];

    // Consulta UPDATE
    $sql_update = "UPDATE usuarios SET
        nombre = '$nombre',
        apellidos = '$apellidos',
        dni = '$dni',
        telefono = $telefono,
        fecha_nacimiento = '$fecha_nacimiento',
        email = '$email',
        dinero = '$dinero',
        username = '$username'
        WHERE id = $user_id";

    if (mysqli_query($conn, $sql_update)) {
        echo "<div class='alert alert-success'>Datos actualizados correctamente.</div>";
    } else {
        echo "<div class='alert alert-error'>Error al actualizar: " . mysqli_error($conn) . "</div>";
    }
}
// Obtener los datos actualizados del usuario
$sql = "SELECT id, nombre, apellidos, dni, telefono, fecha_nacimiento, email, dinero, username  
        FROM usuarios WHERE id = $user_id";
$result = mysqli_query($conn, $sql);
$usuario = mysqli_fetch_assoc($result);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>COMPRAMOS TU COCHE</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="shortcut icon" href="/media/icon.svg" />
</head>
<body>
    <header>
        <h1>FORO COMPRAMOS TU COCHE</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></p> 
        <p>Aquí se pueden cambiar los datos personales.</p>      
    </header>
    <nav>
        <a href="inicio.php">Inicio</a> |
        <a href="../index.php">Cerrar sesión (<?php echo htmlspecialchars($_SESSION['usuario']); ?>)</a>
    </nav>
    <main>    
        <h2>Tus datos personales</h2>
        <form method="POST">
            <table>
                <tr>
                        <th>Nombre:</th>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']); ?>"></td>
                </tr>
                <tr>
                         <th>Apellidos:</th>
                             <td><?= htmlspecialchars($usuario['apellidos']); ?></td>
                            <td><input type="text" name="apellidos" value="<?= htmlspecialchars($usuario['apellidos']); ?>"></td>
                </tr>
                <tr>
                    <th>DNI:</th>
                    <td><?= htmlspecialchars($usuario['dni']); ?></td>
                    <td><input type="text" name="dni" value="<?= htmlspecialchars($usuario['dni']); ?>"></td>
                </tr>
                <tr>
                    <th>Teléfono:</th>
                    <td><?= htmlspecialchars($usuario['telefono']); ?></td>
                    <td><input type="text" name="telefono" value="<?= htmlspecialchars($usuario['telefono']); ?>"></td>
                </tr>
                <tr>
                    <th>Fecha de nacimiento:</th>
                    <td><?= htmlspecialchars($usuario['fecha_nacimiento']); ?></td>
                    <td><input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($usuario['fecha_nacimiento']); ?>"></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?= htmlspecialchars($usuario['email']); ?></td>
                    <td><input type="email" name="email" value="<?= htmlspecialchars($usuario['email']); ?>"></td>
                </tr>
                <tr>
                    <th>Usuario:</th>
                    <td><?= htmlspecialchars($usuario['username']); ?></td>
                    <td><input type="text" name="username" value="<?= htmlspecialchars($usuario['username']); ?>"></td>
                </tr>
                <tr>
                    <th>Dinero disponible:</th>
                    <td><?= htmlspecialchars($usuario['dinero']); ?></td>
                    <td><input type="text" name="dinero" value="<?= htmlspecialchars($usuario['dinero']); ?>"></td>
                </tr>
            </table>
            <button type="submit">Guardar cambios</button>
        </form>
        
</main>   
    <footer>
    <!--&copy; <?= date('Y') ?> <br>-->
     <a href="https://github.com/AitanaNB/Desarrollo-Web-SGSSI">Nuestro maravilloso y organizado código está disponible en Github</a>
     
</footer>
</body>