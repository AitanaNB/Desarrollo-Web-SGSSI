<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Catálogo de coches</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Catálogo de Coches</h1>
    </header>
    <nav>
        <a href="inicio.php">Inicio</a> |
        <a href="logout.php">Cerrar sesión (<?php echo htmlspecialchars($_SESSION['usuario']); ?>)</a>
    </nav>
    <main>
        <?php
        // Conexión a la base de datos
        include 'bdcon.php';

        if (!$conn) {
            die("Conexión fallida: " . mysqli_connect_error());
        } else {
            $sql = "SELECT * FROM coches";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                echo "<table border='1'>
                        <tr>
                            <th>Matricula</th>
                            <th>Modelo</th>
                            <th>Marca</th>
                            <th>Color</th>
                            <th>Kilometraje</th>
                            <th>Precio</th>
                        </tr>";
                while($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>" . $row["matricula"] . "</td>
                            <td>" . $row["modelo"] . "</td>
                            <td>" . $row["marca"] . "</td>
                            <td>" . $row["color"] . "</td>
                            <td>" . $row["kilometraje"] . "</td>
                            <td>" . $row["precio"] . "</td>
                          </tr>";
                }
                echo "</table>";
            } else {
                echo "No hay coches disponibles.";
            }
            mysqli_close($conn);
        }
        ?>
    </main>
</body>
</html>
