<?php


ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);

session_start();

// Para PHP 7.2.2, configuramos SameSite manualmente
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

// Content-Security-Policy (CSP)
$csp_policy = "default-src 'self'; ";
$csp_policy .= "script-src 'self'; "; 
$csp_policy .= "style-src 'self'; ";
$csp_policy .= "img-src 'self' data:; "; 
$csp_policy .= "frame-ancestors 'none';"; // Alternativa al X-Frame-Options

// Agrega directivas sin fallback (base-uri y form-action)
$csp_policy .= "base-uri 'self'; ";
$csp_policy .= "form-action 'self'; ";
header("Content-Security-Policy: " . $csp_policy);

/*
	¿Como funciona este script? (Nota que me pareció interesante pq me estaba liando con formularios)
	
	*Basicamente siempre intenta coger los datos por defecto del coche cuyo id pasamos por Jquery 
	*Ya de paso se revisa que exista el coche
	*En el caso de que se haya lanzado un formulario, coge los datos guardados en los campos y los intenta meter en la bd
	--El formulario se define en: <div class="form-container"><form id="item_modify_form" method="POST" onsubmit="return validarCoche()">...
	-----Aqui lo que se define son los campos que van en el formulario y en que orden van
	--Se define el boton que lo lanza en: <button id="item_modify_submit" type="submit" class="boton">Guardar cambios</button>
	-----Una vez se pulsa el boton se envian los valores guardados en los campos del formulario a la propia página (esto es porque en el form no tenemos action='', entonces por defecto lo reenvia a la página actual)
	--Al pulsar dicho boton será cierta la condición de que $_SERVER['REQUEST_METHOD'] === 'POST'
	--Se ejecutan los comandos de dentro del if, que actualizan la db con los datos del formulario (Valores que habia puesto el usuario en los campos)
	-----Para acceder a estos valores utilizamos POST, que es el metodo especificado antes
*/


// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}


// Leemos la cookie
if (isset($_COOKIE['cookie_id_coche'])) {
    //sacamos la variable de la cookie 
	$coche_id = $_COOKIE['cookie_id_coche'];
	
	//Nos comemos la cookie (Por razones de seguridad)
	setcookie(
		'cookie_id_coche',
		$coche,
		time() - 3600 , //Le quitamos a la cookie 1 hora de vida, teniendo en cuenta que inicialmente solo tenia 5 mins, asi nos aseguramos de los hackers no nos las roben
		//Lei que si no reiterabas las otras opciones de seguridad especificadas en su creación en la consulta para modificar la cookie podia haber problemas, asi que ahi van
		'/modificar_coche.php', //Por alguna razón que no me explico, aquí el path tiene que tener una barra '/' al principio, si no falla y devuelve el "Error, no hay cookies 0.0". No tiene ningún sentido porque en la hoja en la que se genera la cookie, hornear_cookie_coche.php a setcookie le da igual si llleva barra delante o no y es especialmente ridiculo porque ahí decides que hoja va a poder aceder a la cookie, entonces si de verdad 'modificar_coche.php' no permitiese a esta hoja acceder a la cookie, cambiarlo ahí debería de romperlo todo aquí. De hecho, si no haces este setcookie para borrar la cookie aqui, incluso si el path en hornear_cookie era  'modificar_coche.php'  y tu aqui no lo cambias para ponerle la barra /,  funcionará todo perfectamente. A si que no se, magia negra supongo
		false,
		true
	);
} else {
    // Si no hay cookie, avisamos a usuario (Nunca debería de no haber cookie porque si no no se hace el salto a esta página desde mis_coches, pero en el caso raro de que nuestro usuario aun use un modem de 14.4 kb por segundo y le expire la cookie antes de entrar, ponemos esto)
    echo "<h3>Error, no hay cookies 0.0 </h3>";
    exit();
}

// Conexión a la base de datos
include 'bdcon.php';

try{
	// Obtener info del coche
	$user_id = $_SESSION['user_id'];
	$sql = "SELECT * FROM coches WHERE id = :coche_id AND id_propietario = :user_id";
	$params = [':user_id' => $user_id,':coche_id' => $coche_id];
	$stmt = $conn->prepare($sql);
	$stmt->execute($params);
	$coche = $stmt->fetch(PDO::FETCH_ASSOC);
	
	if (!$coche) {
		// El coche no existe o no pertenece al usuario
		header("Location: mis_coches.php");
		exit();
	}
	
	//sabemos que si se llega aquí el coche existe
	
} catch (PDOException $e) {
    echo "<h3>Error, no se enuentra el vehículo: " . htmlspecialchars($e->getMessage()) . "</h3>";
	//por defecto, después de lanzar un catch, se continua con la ejecución del código. Ponemos el exit para asegurarnos de que eso no ocurra
	exit();
}


// Procesar el formulario de modificación de datos del coche (Si se quiere modificar info del coche)
// Ahora, aqui hay que cambiar la condicion de entrada porque tanto el formulario de cambiar datos como el envio del id de mis_coches se hacen a traves de post.
// Así pues, ya no nos vale con preguntar si se ha dado una REQUEST del tipo post, sino tenemos que ver si esa request contiene datos de modificación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['matricula'])) {
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
		//No hace falta hacer else, die ya se asegura de que no pase de ahi
    } 
		
    try{
		// Verificar si la matrícula ya existe en otro coche
		$sql_check = "SELECT id FROM coches WHERE matricula = :matricula AND id != :coche_id";
		$params = [':matricula' => $matricula,':coche_id' => $coche_id];
		$stmt = $conn->prepare($sql_check);
		$stmt->execute($params);
		$res_check = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($res_check) {
			$mensaje = "<div class='alert alert-error'>Error: Ya existe otro coche con esa matrícula.</div>";
		//Aquí si necesitamos el else, porque queremos que si se da el if, sia ejecutandose el resto del código
		} else {
			// Hacemos el transaction
			$conn->beginTransaction();
			// Actualizar en la base de datos
			$sql = "UPDATE coches SET 
				matricula = :matricula,
				modelo = :modelo,
				marca = :marca,
				color = :color,
				kilometraje = :kilometraje,
				precio = :precio,
				en_venta = :en_venta
				WHERE id = :coche_id AND id_propietario = :user_id";
			$params = [':matricula' => $matricula,':modelo' => $modelo,':marca' => $marca,':color' => $color,':kilometraje' => $kilometraje,':precio' => $precio,':en_venta' => $en_venta,':coche_id' => $coche_id,':user_id' => $user_id];
			$stmt = $conn->prepare($sql);
			// Ejecutar la consulta
			$stmt->execute($params);
			
			//Inicia la transacción si nada malo ha pasado
			$conn->commit();
			
			//Ya no hace falta if aquí porque si no funciona va a catch	
			
			//Aqui antes soliamos hacer header("Location: mis_coches.php?id=$coche_id"); esta inserción es segura porque estamos insertando un valor que hemos saneado dentro de la hoja, no un valor directamente de usuario tipo '$coche_id = $_GET['id'];', sin embargo, desde la página donde se lanzaba esta, mis_coches.php, se podia abrir esta pagina poniendo el id de un coche en la url, lo cual es inseguro. A si que hubo que cambiamos filosofias y ahora ids en la url, ni para enviar ni para confirmar
			header("Location: mis_coches.php"); // Redirige a mis_coches.php con un parámetro de éxito
			exit();
		}
    }catch (PDOException $e) {
		if ($conn->inTransaction()) $conn->rollBack();
		$mensaje = "<div class='alert alert-error'>Error al actualizar el coche: " .  htmlspecialchars($e->getMessage()) . "</div>";
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
//No hace falta cerrar la Conexion con PDO
?>