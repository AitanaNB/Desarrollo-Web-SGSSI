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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mis Coches - COMPRAMOS TU COCHE</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
    <script>
        function confirmarEliminacion(id, matricula) {
            if (confirm('¿Estás seguro de que quieres eliminar el coche con matrícula ' + matricula + '? Esta acción no se puede deshacer.')) {
                form.submit()
            }
            return false;
        }
    </script>
</head>
<body>
    <header>
        <div class="header-container">
            <div>
                <h1>Mis Coches</h1>
                <p>Gestiona tus vehículos en venta - Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
            </div>
            <div>
                <a href="subir_coche.php" class="btn-subir">Subir coche</a>
            </div>
        </div>
    </header>

    <nav>
        <a href="inicio.php">Inicio</a> |
        <a href="catalogo.php">Catálogo</a> |
        <a href="logout.php">Cerrar sesión (<?php echo htmlspecialchars($_SESSION['usuario']); ?>)</a>
    </nav>

    <main>
        <?php
        // Mostrar mensaje de éxito si se eliminó un coche
        if (isset($_GET['eliminado']) && $_GET['eliminado'] == 1) {
            echo '<div class="alert alert-success"> Coche eliminado correctamente</div>';
        }
        
        // Mostrar mensaje de éxito si se modificó un coche
        if (isset($_GET['modificado']) && $_GET['modificado'] == 1) {
            echo '<div class="alert alert-success"> Coche modificado correctamente</div>';
        }

        // Obtener el ID del usuario logueado desde la sesión
        $user_id = $_SESSION['user_id'];

        // Consulta para obtener los coches del usuario - CON DEBUGGING
        $sql = "SELECT * FROM coches WHERE id_propietario = $user_id ORDER BY id DESC";
        $result = mysqli_query($conn, $sql);

        // Verificar si hay error en la consulta
        if ($result === false) {
            echo "<div class='alert alert-error'>Error en la consulta: " . mysqli_error($conn) . "</div>";
        }
        // Verificar si la consulta fue exitosa y tiene resultados
        else if (mysqli_num_rows($result) > 0) {
            echo "<table class='table-mis-coches'>
                    <tr>
                        <th>Matrícula</th>
                        <th>Modelo</th>
                        <th>Marca</th>
                        <th>Color</th>
                        <th>Kilometraje</th>
                        <th>Precio (€)</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>";

            while($row = mysqli_fetch_assoc($result)) {
                $estado_venta = $row['en_venta'] ? 
                    "<span class='estado-venta en-venta'>En Venta</span>" : 
                    "<span class='estado-venta no-venta'>No en Venta</span>";
                
                echo "<tr>
                        <td><strong>" . htmlspecialchars($row["matricula"]) . "</strong></td>
                        <td>" . htmlspecialchars($row["modelo"]) . "</td>
                        <td>" . htmlspecialchars($row["marca"]) . "</td>
                        <td>" . htmlspecialchars($row["color"]) . "</td>
                        <td>" . number_format($row["kilometraje"]) . " km</td>
                        <td><strong>" . number_format($row["precio"], 2) . " €</strong></td>
                        <td>" . $estado_venta . "</td>
                        <td class='acciones'>
                            <a href='modificar_coche.php?id=" . $row["id"] . "' class='btn-modificar'>Modificar</a>
                            <!-- Formulario para eliminar coche -->
                            <form method='POST' action='eliminar_coche.php' style='display:inline;' onsubmit='return confirmarEliminacion(" . $row["id"] . ", \"" . htmlspecialchars($row["matricula"]) . "\");'>
                                <input type='hidden' name='id_coche' value='" . $row["id"] . "'>
                                <button type='submit' class='btn-eliminar'>Eliminar</button>
                            </form>
                        </td>
                      </tr>";
            }
            echo "</table>";
                  
        } else {
            echo "<div class='no-coches'>
                    <h3>No tienes coches registrados</h3>
                    <p>¡Comienza a vender tu primer coche!</p>
                    <a href='subir_coche.php' class='btn-subir' style='margin-top: 15px;'>
                        Subir mi primer coche
                    </a>
                  </div>";
        }

        mysqli_close($conn);
        ?>
    </main>

    <footer>
        <a href="https://github.com/AitanaNB/Desarrollo-Web-SGSSI">Nuestro maravilloso y organizado código está disponible en Github</a>
    </footer>
</body>
</html>