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

// Content-Security-Policy (CSP)
/* Nota: Se requiere 'unsafe-inline' y 'unsafe-eval' para que el código actual funcione
debido al uso de JavaScript/CSS en línea y jQuery. Para una solución completa, 
se debe migrar el código en línea a archivos externos o usar Nonces/Hashes. */
$csp_policy = "default-src 'self'; ";
$csp_policy .= "script-src 'self' 'unsafe-inline'; "; 
$csp_policy .= "style-src 'self' 'unsafe-inline'; ";
$csp_policy .= "img-src 'self' data:; "; 
$csp_policy .= "frame-ancestors 'none';"; // Alternativa al X-Frame-Options

// Agrega directivas sin fallback (base-uri y form-action)
$csp_policy .= "base-uri 'self'; ";
$csp_policy .= "form-action 'self'; ";
header("Content-Security-Policy: " . $csp_policy);
	
	 /*SOBRE EL CAMBIO EN LAS JQUERYS DE getJSON() a .post()
			  
		Esto de mandar info entre páginas lo solia hacer con el metodo .getJSON() de Jquerys 
		--En en esta página, el sistema que se utilizaba era crear una espefie de formulario, pero se utilizaba la url para movernos a la nueva pagina, a si que imagino que tendria un problema similar a getJSON() de pasar datos por la url
		Sin embargo, me di cuenta de que usaba la URL para pasar y recibir la información, a si que lo sustituí por el metodo .post(), que sintactica y funcionalmente es igual pero utiliza el metodo POST para pasar la inormación
		-POST consiste meter las variables en un mensaje http, es un metodo Jquery a si que seguimos necesitando la librería
		-Tambien nos obliga a que en los backends, las variables ya no se cogen con $_GET['variable_ejemplo'] sino con $_POST['variable_elemplo']
		-Y a demas, algo que es más importante en catalogo.php
		--Asume que lo que le devuelves es un objeto del tipo string, no array, que es como son los JSON por defecto
		--Esto aqui no es tan importante porque en la funcion en la que esperamos respuesta, se lee mal el data, salta al else, y lo unico que se pierde es el refresh y el mensaje de confirmación de consola (Pero no se rompe el flujo del programa más allá de eso )
		-Aun así conviene convertir el data de un objeto string a un objeto como son los JSON, array para que el programa funcione lo mejor posible, y esta conversion la hacemos por medio de la función JSON.parse(*variable*);
	 
	 */

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$es_admin = ($_SESSION['es_admin'] == 1);
$catalogo_destino = $es_admin ? 'ver_catalogoAdmin.php' : 'catalogo.php';
$texto_catalogo = $es_admin ? 'Catálogo (Admin)' : 'Catálogo de coches';

// Conexión a la base de datos
include 'bdcon.php';

?>

<!DOCTYPE html>
<html>
<head>
    <title>Mis Coches - COMPRAMOS TU COCHE</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
</head>

<!-- popup confirmación -->
<div id="popup-confirmacion">
	<h2  id="texto-confirmacion" >¿Estás seguro de que quieres eliminar el coche?</h2>
	<p>Esta acción no se puede deshacer.</p>
	<button class="boton-popup" id="boton-confirmar-eliminacion" onclick="document.getElementById('popup-confirmacion').style.display='none'">Si</button>
	<button class="boton-popup" onclick="document.getElementById('popup-confirmacion').style.display='none'">No</button>
</div>
	
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
        <a href="<?php echo $catalogo_destino; ?>"> <?php echo $texto_catalogo; ?> </a> |
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
		
		try {
			// Consulta para obtener los coches del usuario
			$sql = "SELECT * FROM coches WHERE id_propietario = :user_id ORDER BY id DESC";
			$params = [':user_id' => $user_id];
			$stmt = $conn->prepare($sql);
			$stmt->execute($params);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// Que $result sea == false saltará cuando $result es vacio, si da un error como esta ahora se irá al catch
			// !Result es cuando es vacio
			if (!$result) {
				echo "<div class='no-coches'>
                    <h3>No tienes coches registrados</h3>
                    <p>¡Comienza a vender tu primer coche!</p>
                    <a href='subir_coche.php' class='btn-subir' style='margin-top: 15px;'>
                        Subir mi primer coche
                    </a>
                  </div>";
				exit();
			}
			
			// Si la consulta fue exitosa y tiene resultados (No hace falta poner else porque si se da el if anterior sale con el exit())
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
			
			// Esto cicla todos los casos ya guardados en $result (Inicialmente volvia a hacer $row = $stmt->fetchAll(...) pero me fallaba porque te intenta coger los casos de despúes de los que ya te ha devuelto, y en $results ya te ha devuelto todos. Tampoco se puede hacer un fetch de $results porque $results ya es un array asociativo, estarías creando un array asociativo de un array asociativo)
            foreach($result as $row) {
                $estado_venta = $row['en_venta'] ? 
                    "<span class='estado-venta en-venta'>En Venta</span>" : 
                    "<span class='estado-venta no-venta'>No en Venta</span>";
                
				//tenemos que guardar oculto al usuario el id de los coches para luego cogerlo en los onclick events, podiamos haber utilizado las matriculas, que ya estaban mostradas en la tabla y publicas, pero ya teniamos todo en funcion del id, a si que he aprovechado las estructuras existentes basadas en id
                echo "<tr data-id='" . htmlspecialchars($row["id"]) . "'>
                        <td><strong>" . htmlspecialchars($row["matricula"]) . "</strong></td>
                        <td>" . htmlspecialchars($row["modelo"]) . "</td>
                        <td>" . htmlspecialchars($row["marca"]) . "</td>
                        <td>" . htmlspecialchars($row["color"]) . "</td>
                        <td>" . number_format($row["kilometraje"]) . " km</td>
                        <td><strong>" . number_format($row["precio"], 2) . " €</strong></td>
                        <td>" . $estado_venta . "</td>
                        <td class='acciones'>
							<!-- Aquí soliamos comunicarnos con modificar_coche con un href, significando que si el usuario ponia el id del coche que quisiese podria lazar la página para modificarlo. Se ha cambiado esta funcionalidad para no utilizar la url como en CatalogoCuentaAdmin o CatalogoCocheAdmin-->
                            <button type='submit' class='btn-modificar'>Modificar</button>
							<button type='submit' class='btn-eliminar'>Eliminar</button>
                        </td>
                      </tr>";
            }
            echo "</table>";
                  
        } catch (PDOException $e) {
                 echo "<div class='alert alert-error'>Error en la consulta: ". htmlspecialchars($e->getMessage()) .  "</div>";
        }

		//No hace falta cerrar la conexion en PDO
        ?>
    </main>
	
    <footer>
        <a href="https://github.com/AitanaNB/Desarrollo-Web-SGSSI">Nuestro maravilloso y organizado código está disponible en Github</a>
    </footer>
	
</body>
</html>

<!-- esto de aquí no es un script, es un link a la libreria de las Jqueris que le permite al java script de esta página utilizarla-->
<script src="../js/jquery-3.5.1.min.js"></script>

<!-- a continuación, el codigo de los onClick events de los botones de eliminar y modificar-->
<script>
//esto permite que funcionen las jqeries
$(document).ready(function () {
 
 //esta variable existe para recordar el id del coche selecionado previamente, por eso esta fuera de todas las funciones, para que todas la puedan leer y utilizar
 let idSeleccionado = '';
 
 //-----------------------------------------------------------------------------
 //funcion1: ONCLICK ELIMINAR
  $('.btn-eliminar').on('click', function () {
	//'This' selecciona lo que se ha clicado (La fila de la tabla), closest() coge el elemento más cercano y le especificamos que sea del tipo tr, en el tr es donde guardamos la informacion de los campos y el boton (enronces el tr mas cercano al boton es en el que se guarda el propio boton), pero tambien hemos insertado un data-id antes, hablando del cual, data selecciona un valor del tipo data e id con la etiqueta id (data-id)
	idSeleccionado = $(this).closest('tr').data('id');
	//Cambiamos el texto del mensaje pop-up "insertando" el valor de la matrícula pero no desde una variable, sino desde un método que calcula el valor al leer la frase (Asi intentamos evitar inserciones SQL)
	document.getElementById('texto-confirmacion').innerText =`¿Estás seguro de que quieres eliminar el coche de matricula ${$(this).closest('tr').find('td').eq(0).text()} ?`;
    //hacemos la ventana pop up visible
	document.getElementById('popup-confirmacion').style.display = 'block';
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
	
	
 //funcion3:-----------------------------------------------------------------------------	
 //ONCLICK MODIFICAR
 $('.btn-modificar').on('click', function () {
	idSeleccionado = $(this).closest('tr').data('id');
	/*
	Aqui queria haber usado un .post y luego un window.location.href para primero pasar la información y luego redirigir a la página
	¿El problema ?, 2 en realidad. 
		1) Eso son dos sesiones diferentes, el href cuenta compo otra consulta que se lanza al acabar la primera, y las variables no se mantienen de consulta en consulta 
		2) Como esta escrito al menos el php, para salir de modificar_php necesitamos input del usuario, si el orden de ejecución entra solo sin usuario se queda atascado porque se queda esperando input de usuario (Pulsar el boton guardar cambios por ejemplo)
	¿La solucion ?, cookies 
    */
	//mandamos al sistema a la hoja de php que crea la cookie (Esto es porque las cookies solo se pueden crear y normalmente modificar desde php)
	$.post('hornear_cookie_coche.php', { id: idSeleccionado })
		//una vez se ha regresado del php que crea la cookie 
		.done(function (data) {
			//si todo ha ido bien con la cookie
			if (data.cookie=== 'si ^w^') {
				console.error('se horneo la cookie :D');
				//Movemos al usuario a la página de modificar coche, donde podremos acceder a la cookie, porque las cookies se pueden acceder desde cualquier sitio que queramos de la página (y son bastante seguras)
				window.location.href = 'modificar_coche.php';
		    } else {
				console.error('oh... no se ha podido hornear la cookie porque: ', data.cookie);
			}
		})
		//fallo horroroso
        .fail(function (jqXHR, textStatus, errorThrown) {
			console.error('No se ha podido hornear la cookie :C, y no sabemos porque quizá esto ayude a saber porque: ', textStatus, errorThrown);
		})
 });
 //-----------------------------------------------------------------------------
 
 
 //Ejecucion de la pagina
 //(Aqui no hay nada porque no hay nada que se ejecute nada más lanzar la página, sino que esperamos siempre a clicks)

})
</script>