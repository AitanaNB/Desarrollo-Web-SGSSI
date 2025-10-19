<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Conexión a la base de datos
include 'bdcon.php';

if (!$conn) {
    die("<div class='alert alert-error'>Conexión fallida: " . mysqli_connect_error() . "</div>");
}

// Procesar el formulario de subida de coche
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir datos del formulario
    $matricula = $_POST['matricula'];
    $modelo = $_POST['modelo'];
    $marca = $_POST['marca'];
    $color = $_POST['color'];
    $kilometraje = $_POST['kilometraje'];
    $precio = $_POST['precio'];
    $en_venta = isset($_POST['en_venta']) ? 1 : 0;
    $id_propietario = $_SESSION['user_id'];

    if (empty($matricula) || empty($modelo) || empty($marca) || empty($color) || empty($kilometraje) || empty($precio)) {
        $mensaje = "<div class='alert alert-error'>Todos los campos son obligatorios.</div>";
    } else {
        // verificar si la matrícula ya existe
        $check_sql = "SELECT id FROM coches WHERE matricula = '$matricula'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            $mensaje = "<div class='alert alert-error'>Error: Ya existe otro coche con esa matrícula.</div>";
        } else {
            $sql = "INSERT INTO coches (matricula, modelo, marca, color, kilometraje, precio, en_venta, id_propietario)
                    VALUES ('$matricula', '$modelo', '$marca', '$color', $kilometraje, $precio, $en_venta, $id_propietario)";

            if (mysqli_query($conn, $sql)) {
                echo "Coche subido con éxito";
            } else {
                echo "Error: " . $conn->error;
            }
        }
    }

    
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Subir coche nuevo</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="shortcut icon" href="media/icon.svg" />
    <script src="../js/validarDatos.js"></script>
</head>
<body>
    <header>
        <div class="header-container">
            <div>
                <h1>Subir coche</h1>
                <p>Añade un coche nuevo a tu catálogo - Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
            </div>
        </div>
    </header>

    <nav>
        <a href="inicio.php">Inicio</a> |
        <a href="catalogo.php">Catálogo</a> |
        <a href="mis_coches.php"> Mis coches </a> |
        <a href="logout.php">Cerrar sesión (<?php echo htmlspecialchars($_SESSION['usuario']); ?>)</a>
    </nav>

    <main>
        <div class="form-container">
            <?php
            // Mostrar mensaje de error si existe
            if (isset($mensaje) && !empty($mensaje)) {
                echo $mensaje;
            }
            ?>
            <form id="item_add_form" method="POST" onsubmit="return validarCoche()">
                <table>
                    <tr>
                        <td><label for="matricula">Matrícula:</label></td>
                        <td><input type="text" id="matricula" name="matricula" required placeholder="1234-ABC"></td>
                    </tr>
                    <tr>
                        <td><label for="modelo">Modelo:</label></td>
                        <td><input type="text" id="modelo" name="modelo" required></td>
                    </tr>
                    <tr>
                        <td><label for="marca">Marca:</label></td>
                        <td><input type="text" id="marca" name="marca" required></td>
                    </tr>
                    <tr>
                        <td><label for="color">Color:</label></td>
                        <td><input type="text" id="color" name="color" required></td>
                    </tr>
                    <tr>
                        <td><label for="kilometraje">Kilometraje:</label></td>
                        <td><input type="number" id="kilometraje" name="kilometraje" required></td>
                    </tr>
                    <tr>
                        <td><label for="precio">Precio:</label></td>
                        <td><input type="number" step="0.01" id="precio" name="precio" required></td>
                    </tr>
                    <tr>
                        <td><label for="en_venta">En venta:</label></td>
                        <td><input type="checkbox" id="en_venta" name="en_venta"></td>
                    </tr>
                </table>
                <button id="item_add_submit" type="submit" class="boton">Subir coche</button>
                <a href="mis_coches.php" class="boton">Cancelar</a>
            </form>
        </div>
</body>
</html>
