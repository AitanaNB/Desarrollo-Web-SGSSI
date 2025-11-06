<?php

ini_set('session.cookie_httponly', 1); 
ini_set('session.cookie_secure', 1);  
ini_set('session.use_only_cookies', 1); 

session_start();

// Para PHP 7.2.2, configuramos SameSite manualmente
if (version_compare(PHP_VERSION, '7.3.0', '<')) {
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
}

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

/*
-el futuro ha llegado, y hay que corregir la inseguridad de usar la url para pasar información
-a traves de jquerys nos comunicamos con eliminar_coche.php que ya existe, asi todo es seguro y no metemos ningun dato en la url
*/

// Consultar todos los coches del sistema (Una de las pocas consultas del sistema donde no hay parametros que insertar)
try{
    $sql = "SELECT c.id, c.matricula, c.marca, c.modelo, c.color, c.kilometraje, c.precio, u.username AS propietario
        FROM coches c
        INNER JOIN usuarios u ON c.id_propietario = u.id";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
}catch (PDOException $e) {
		echo "<p>Error al coger todos los coches: " . htmlspecialchars($e->getMessage()) . "</p>";
		exit();
} 

// Mostrar los coches
?>

<!DOCTYPE html>
<html>
<head>
    <title>Catálogo de Coches - Admin</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
</head>

<!-- popup confirmación -->
<div id="popup-confirmacion-admin">
	<h2  id="texto-confirmacion-admin" >¿Admin, seguro que quieres ELIMINAR el coche?</h2>
	<p>Esta acción no se puede deshacer.</p>
	<button class="boton-popup" id="boton-confirmar-eliminacion" onclick="document.getElementById('popup-confirmacion-admin').style.display='none'">Si</button>
	<button class="boton-popup" onclick="document.getElementById('popup-confirmacion-admin').style.display='none'">No</button>
</div>

<body>
    <header style="background-color: #c71435;">
        <div class="header-container">
            <div>
                <h1>Catálogo de Coches (Admin)</h1>
                <p>Gestión completa de todos los vehículos - Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
            </div>
        </div>
    </header>

    <nav>
        <a href="inicio.php">Inicio</a> |
        <a href="catalogoCuentaAdmin.php">Gestionar usuarios</a> |
        <a href="logout.php">Cerrar sesión (<?php echo htmlspecialchars($_SESSION['usuario']); ?>)</a>
    </nav>

    <main>
        <?php
            // Mostrar mensaje de éxito si se eliminó un coche
            if (isset($_GET['eliminado']) && $_GET['eliminado'] == 1) {
                echo '<div class="alert alert-success"> Coche eliminado correctamente</div>';
            }

            // Verificar si la consulta fue exitosa y tiene resultados
            if ($result) {
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
                        <th>Acción</th>
                    </tr>";

               foreach($result as $row) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['matricula']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['marca']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['modelo']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['color']) . "</td>";
                    echo "<td>" . number_format($row['kilometraje']) . " km</td>";
                    echo "<td>€" . number_format($row['precio'], 2) . "</td>";
                    echo "<td>" . htmlspecialchars($row['propietario']) . "</td>";
                    //Como le vamos a meter un onClick en javaScript, no hace falta que aquí tenga nada de eso
                    echo "<td><a class='catalogo-admins-eliminar'>Eliminar</a></td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "No hay coches registrados en el catálogo.";
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

<!-- a continuación, el codigo de los onClick event de eliminar-->
<script>
//esto permite que funcionen las jqeries
$(document).ready(function () {
 
 //esta variable existe para recordar el id del coche selecionado previamente, por eso esta fuera de todas las funciones, para que todas la puedan leer y utilizar
 let idSeleccionado = '';
 
 //-----------------------------------------------------------------------------
 //funcion1: ONCLICK ELIMINAR
  $('.catalogo-admins-eliminar').on('click', function () {
	//Con el 'this seleccionamos el eelemento más cercano al que se ha clicado del tipo tr, y luego buscamos el primer td que debería de tener el id'
	idSeleccionado = $(this).closest('tr').find('td').eq(0).text()
	//cambiamos texto del mensaje 
	document.getElementById('texto-confirmacion-admin').innerText =`¿Admin, seguro que quieres ELIMINAR el coche de matrícula ${$(this).closest('tr').find('td').eq(1).text()} ?`;
	//hacemos la ventana pop-up visible
    document.getElementById('popup-confirmacion-admin').style.display = 'block';
  });
  //-----------------------------------------------------------------------------
  
 //-----------------------------------------------------------------------------
 //funcion2:ONCLICK BOTON DEL POPUP QUE CONFIRMA ELIMINAR
 $('#boton-confirmar-eliminacion').on('click', function () {
	//si se clica en confirmar-eliminacion, suponemos que ya tenemos un valor en idSleccionado del paso anterior, pero aun así revisamos que el valor sea valido por se acaso 
	if (idSeleccionado == null) {
          console.error('No hay coche seleccionado para eliminar.');
          return;
      }
	//enviamos el jqery y esperamos respuesta
	$.post('eliminar_coche.php', { id: idSeleccionado })
		.done(function (data) {
			//parseamos respuesta
			data = JSON.parse(data);
			//Revisamos la respueta
            if (data.esta=== 'ok') {
				console.error('Coche eliminado con exito.');
				//para recargar la página y que se vea el coche borrado
				location.reload();
		    } else {
				console.error('Algo extraño acaba de pasar, y esto es lo que devuelve el servidor: ', data);
			}
			})
		.fail(function (jqXHR, textStatus, errorThrown) {
				console.error('Error en la conexion de eliminar_coches:', textStatus, errorThrown);
		});
 });
 //-----------------------------------------------------------------------------
 
 //Ejecucion de la pagina
 //(Aqui no hay nada porque no hay nada que se ejecute nada más lanzar la página, sino que esperamos siempre a clicks)

})
</script>
