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

//funcion para eliminar los coches
function matacoches($id){
	$sql = "DELETE FROM coches WHERE id = $id";
	//Importamos la variable global conn de fuera de la función a matacoches para usarla en la consulta sql
        global $conn;
	mysqli_query($conn, $sql);
	return;
}

/*
-vale, el problema aqui es que desde el OnClick event de debajo no podemos lanzar funciones de python como el matacoches de aqui arriba porque como es un evento que se lee durante la ejecucion de la página solo acepta javascript
-entonces, mi idea de una solución, le metemos a la url la id que queremos borrar y creamos una funcion de php que si detecta ese id en la url, lanza matacoches y borra el id de la url
-es esto inseguro ? Muy seguramente, pero eh, eso es un problema para los nosotros del futuro
*/

//lo que revisa la url en busca del id
if (isset($_GET['br'])) {
    matacoches($_GET['br']);
    //esto resetea la url
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?eliminado=1");
    exit();
}

// Consultar todos los coches con datos del propietario
$sql = "SELECT c.id, c.matricula, c.marca, c.modelo, c.color, c.kilometraje, c.precio, u.username AS propietario
        FROM coches c
        INNER JOIN usuarios u ON c.id_propietario = u.id";
$result = mysqli_query($conn, $sql);

// Mostrar los coches
echo "<h2>Catálogo Universal de Coches</h2>";

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
        //vale, en esta parte metemos br=id en la url para notificar al codigo de php que tiene que empezar a borrar, y si, br el nombre al que asociamos el id, que es un stand in para borra, necesita el ?
        //lo verdaderamente jodido es que para que funcione bien, hay que concatenar todo cutre, dividiendo la cadena de caracteres en dos, para unir el eliminar y el id porque si pones href='?eliminar_id=$row['id']' se liaría con las comillas el compilador
        echo "<td><a href='?br=" . $row['id'] . "' onclick=\"return confirm('¿Seguro que deseas eliminar este coche?');\">Eliminar</a></td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No hay coches registrados en el catálogo.";
}
?>
