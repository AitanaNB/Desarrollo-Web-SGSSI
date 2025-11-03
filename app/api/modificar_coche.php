<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Verificar que se proporciona un ID de coche
if (!isset($_GET['id'])) {
    header("Location: mis_coches.php");
    exit();
}

$coche_id = $_GET['id'];

// Conexión a la base de datos
include 'bdcon.php';

if (!$conn) {
    die("<div class='alert alert-error'>Conexión fallida: " . mysqli_connect_error() . "</div>");
}

// Obtener info del coche
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM coches WHERE id = $coche_id AND id_propietario = $user_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    // El coche no existe o no pertenece al usuario
    header("Location: mis_coches.php");
    exit();
}

$coche = mysqli_fetch_assoc($result);

// Procesar el formulario de modificación de datos del coche
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir datos del formulario
    $matricula = $_POST['matricula'];
    $modelo = $_POST['modelo'];
    $marca = $_POST['marca'];
    $color = $_POST['color'];
    $kilometraje = $_POST['kilometraje'];
    $precio = $_POST['precio'];
    $en_venta = isset($_POST['en_venta']) ? 1 : 0;

    // Validación simple
    if (empty($matricula) || empty($modelo) || empty($marca) || empty($precio)) {
        die("<div class='alert alert-error'>Todos los campos obligatorios deben ser completados.</div>");
    } else {
        // Verificar si la matrícula ya existe en otro coche
        $sql_check = "SELECT id FROM coches WHERE matricula = '$matricula' AND id != $coche_id";
        $res_check = mysqli_query($conn, $sql_check);
        if (mysqli_num_rows($res_check) > 0) {
            $mensaje = "<div class='alert alert-error'>Error: Ya existe otro coche con esa matrícula.</div>";
        } else {
            // Actualizar en la base de datos
            $sql = "UPDATE coches SET 
                    matricula = '$matricula',
                    modelo = '$modelo',
                    marca = '$marca',
                    color = '$color',
                    kilometraje = $kilometraje,
                    precio = $precio,
                    en_venta = $en_venta
                    WHERE id = $coche_id AND id_propietario = $user_id";

            // Ejecutar la consulta
            if (mysqli_query($conn, $sql)) {
                header("Location: mis_coches.php?id=$coche_id"); // Redirige a mis_coches.php con un parámetro de éxito
                exit();
            } else { // Si falla, crea un mensaje de error
                $mensaje = "<div class='alert alert-error'>Error al actualizar el coche. Por favor, revise los datos.</div>";
            }
        }
        
    }

}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
    <title>Modificar Coche</title>
    <link rel="shortcut icon" href="/media/icon.svg" />
    <script src="../js/validarDatos.js"></script>
</head>
<body>
    <header>
        <h1>Modificar Coche</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
        <p> Actualiza la información de tu coche a continuación:</p>
    </header>
    <nav>
        <a href="inicio.php">Inicio</a> |
        <a href="catalogo.php">Catálogo</a> |
        <a href="mis_coches.php"> Mis coches </a> |
        <a href="../index.php">Cerrar sesión (<?php echo htmlspecialchars($_SESSION['usuario']); ?>)</a>
    </nav>

    <main>
        <div class="form-container">
            <?php
            // Mostrar mensaje de error si existe
            if (isset($mensaje)) {
                echo $mensaje;
            }
            ?>
            <form id="item_modify_form" method="POST" onsubmit="return validarCoche()">
                <table>
                    <tr>
                        <td><label for="matricula">Matrícula:</label></td>
                        <td><input type="text" id="matricula" name="matricula" value="<?php echo htmlspecialchars($coche['matricula']); ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="modelo">Modelo:</label></td>
                        <td><input type="text" id="modelo" name="modelo" value="<?php echo htmlspecialchars($coche['modelo']); ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="marca">Marca:</label></td>
                        <td><input type="text" id="marca" name="marca" value="<?php echo htmlspecialchars($coche['marca']); ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="color">Color:</label></td>
                        <td><input type="text" id="color" name="color" value="<?php echo htmlspecialchars($coche['color']); ?>"></td>
                    </tr>
                    <tr>
                        <td><label for="kilometraje">Kilometraje:</label></td>
                        <td><input type="number" id="kilometraje" name="kilometraje" min="0" max="9999999" value="<?php echo htmlspecialchars($coche['kilometraje']); ?>"></td>
                    </tr>
                    <tr>
                        <td><label for="precio">Precio:</label></td>
                        <td><input type="number" id="precio" name="precio" max="99999999.99" step="0.01" value="<?php echo htmlspecialchars($coche['precio']); ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="en_venta">En venta:</label></td>
                        <td><input type="checkbox" id="en_venta" name="en_venta" <?php if ($coche['en_venta']) echo 'checked'; ?>></td>
                    </tr>
                </table>
                <button id="item_modify_submit" type="submit" class="boton">Guardar cambios</button>
                <a href="mis_coches.php" class="boton">Cancelar</a>
            </form>
        </div>
    </main>

    <footer>
        <a href="https://github.com/AitanaNB/Desarrollo-Web-SGSSI">Nuestro maravilloso y organizado código está disponible en Github</a>
    </footer>
</body>
</html>
<?php
mysqli_close($conn);
?>