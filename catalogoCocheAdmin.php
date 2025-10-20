<?php
session_start();

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

// Consultar todos los coches con datos del propietario
$sql = "SELECT c.id, c.matricula, c.marca, c.modelo, c.color, c.kilometraje, c.precio, u.username AS propietario
        FROM coches c
        INNER JOIN usuarios u ON c.id_propietario = u.id";
$result = mysqli_query($conn, $sql);

// Mostrar los coches
echo "<h2>Catálogo de Coches</h2>";

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='8' cellspacing='0'>";
    echo "<tr>
            <th>ID</th>
            <th>Matrícula</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Color</th>
            <th>Kilometraje</th>
            <th>Precio</th>
            <th>Propietario</th>
          </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['matricula']) . "</td>";
        echo "<td>" . htmlspecialchars($row['marca']) . "</td>";
        echo "<td>" . htmlspecialchars($row['modelo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['color']) . "</td>";
        echo "<td>" . number_format($row['kilometraje']) . " km</td>";
        echo "<td>€" . number_format($row['precio'], 2) . "</td>";
        echo "<td>" . htmlspecialchars($row['propietario']) . "</td>";
        echo "<td><td><a href='#' onclick=\"return confirm('¿Seguro que deseas eliminar este coche?');\">Eliminar</a></td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No hay coches registrados en el catálogo.";
}
?>
