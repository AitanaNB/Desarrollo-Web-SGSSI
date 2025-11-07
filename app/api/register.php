<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
header("X-Frame-Options: SAMEORIGIN");
header("Content-Security-Policy: frame-ancestors 'self'");

/*
   Honestamente no se porque tenemos un signin.php a demás de esta hoja 
   -en el index solo mencionamos esta hoja, 'register.php'
   -la otra 'signin.php' parece que mete la nueva cuenta en la base de datos pero con menos comprobaciones. 
   Pero no lo he borrado por se acaso

*/

session_start();

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

// Conexión a la base de datos
include 'bdcon.php'; // Este archivo debe definir: $conn

//try,catch por si no va la conexion
try{
	// Recibir datos del formulario
	$nombre = $_POST['nombre'];
	$apellidos = $_POST['apellidos'];
	$dni = $_POST['dni'];
	$telefono = $_POST['telefono'];
	$fecha_nacimiento = $_POST['fecha_nacimiento'];
	$email = $_POST['email'];
	$username = $_POST['username'];
	$password = $_POST['password'];

	// Verificar si el DNI ya existe en la base de datos
	$sql = "SELECT id FROM usuarios WHERE dni = :dni";
	$params = [':dni' => $dni];
	$stmt = $conn->prepare($sql);
	$stmt->execute($params);
	$hay_DNI = $stmt->fetch(PDO::FETCH_ASSOC);
	
	if ($hay_DNI) {
		// Si el DNI ya existe, mostrar mensaje de error
		// Como os gusta poner </div> sin abrir contenedor eh, XD
		echo "<div>";
		echo "<h3>Error en el registro</h3>";
		echo "<p>El DNI <strong>$dni</strong> ya está registrado en el sistema.</p>";
		echo "<p>Por favor, verifica tus datos o utiliza un DNI diferente.</p>";
		echo "<a href='../index.php' style='display: inline-block; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Volver al registro</a>";
		echo "</div>";
		exit();
	}

	// También verificar si el username ya existe (Se pueden hacer variables con otro nombres que guarden el sql , lo que se devuelve y los paranetros, pero a mi me gusta reutilizar params, stmt y sql)
	$sql = "SELECT id FROM usuarios WHERE username = :username";
	$params = [':username' => $username];
	$stmt = $conn->prepare($sql);
	$stmt->execute($params);
	//fetch(PDO::FETCH_ASSOC); se encarga de devolver un "array asociativo" con los nombres de las coumnas como claves, ergo en este caso en que no vamos a utilizar los datos (Solo queremos ver si devuelve algo o si no) no es necesario, pero lo he puesto por costumbre (Si no se pone te devuelve la información desordenada/varias veces).
	$hay_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
	//Importante de cara al futuro, si queremos coger más de un registro usaremos fetchAll(PDO::FETCH_ASSOC) que nos devolverá varios arrays, uno por cada registro 

	if ($hay_usuario) {
		// Si el username ya existe, mostrar mensaje de error
		echo "<h3>Error en el registro</h3>";
		echo "<p>El nombre de usuario <strong>$username</strong> ya está registrado en el sistema.</p>";
		echo "<p>Por favor, elige un nombre de usuario diferente.</p>";
		echo "<a href='../index.php' style='display: inline-block; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Volver al registro</a>";
		echo "</div>";
		exit();
	}
	
	/*	Crea el Hash de la contraseña introducida por el usuario (Se supone que la funcion incluye la semilla que hace cada contraseña única)
	    utiliza el algoritmo Bcrypt, genera un hash en el que primero se especifica la version de Bcrypt urilizada, luego el coste, luego la semilla y por utlimo el hash
	    ej: $2y$10$8sA2Z5m9u1xV3cR7tYbGhOe6NqWpLdK4M1jS3fR7vE5tH6yB8nV 
			(version) -> $2y
			(coste) -> $10
			(semilla) -> $8sA2Z5m9u1xV3cR7tYbGhO
			(hash) -> e6NqWpLdK4M1jS3fR7vE5tH6yB8nV
		el algoritmo generará strings de 60 caracteres (2 version, 2 coste, 22 semilla, 31 Hash y 3 separadores $), pero no esta de menos darle un poco más de espacio en la variable de mysql
		*/
	$password_hash = password_hash($password, PASSWORD_DEFAULT);
	
	// No se ha creado bien el hash
	if (!$password_hash) {
        echo "<h3>Error en el registro</h3>";
		echo "<p>Lo sentimos usuario, no hemos conseguido generar bien el hash de su contraseña.</p>";
		echo "<p>Por favor, pruebe más tarde con otra.</p>";
		echo "<a href='../index.php' style='display: inline-block; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Volver al registro</a>";
		echo "</div>";
		exit();
    }
	
	//Si os acordais de compra-coches, aquí normalmente haría un try,catch, para que si hay un error en la insercion a la base de datos se heche para atrás, pero como todo esto ya esta dentro de un try,catch, no tiene mucho sentido (Si hay un error de cualquier tipo ya saltará el catch cuando se dé)
	//eso sí, lo metemos todo en una transaction para lo de hechar atrás posibles cambios 
	$conn->beginTransaction();
	$sql = "INSERT INTO usuarios
	(nombre, apellidos, dni, telefono, fecha_nacimiento, email, username, password)
	VALUES (
		:nombre, :apellidos, :dni, :telefono, :fecha_nacimiento, :email, :username, :password
	)";
	$params = [':nombre' => $nombre,':apellidos' => $apellidos,':dni' => $dni,':telefono' => $telefono,':fecha_nacimiento' => $fecha_nacimiento,':email' => $email,':username' => $username,':password' => $password_hash];
	$stmt = $conn->prepare($sql);
    $stmt->execute($params);
	
	//Si se ha llegado hasta aquí no ha habido ningún error, ergo, confirmamos la transacción
	$conn->commit();
	
	// Guardar datos de sesión (No nos preocupamos de que se lance esto si se da un error antes porque cada vez que se da un error se detendrá la ejecución y pasará al catch, osea, no hacemos if)
	// $conn->insert_id no se puede hacer porque insert_id no existe con una conexion PDO; lastInsertld() es el equivalente, llama al id auogenerado del ultimo registro insertado
	$_SESSION['user_id']  = $conn->lastInsertId();
	$_SESSION['usuario']  = $username;
	$_SESSION['nombre']   = $nombre;
	$_SESSION['es_admin'] = 0;

	// Redirigir
	header("Location: inicio.php");
	//Ponemos un exit porque hay que tener mucho cuidado después de una redirección, que no se ejecute nada más
	exit();
		
} catch (PDOException $e) {
	//En caso de estar en una transaccion, hechamos atrás, pués algo ha ido horrorosamente mal
	if ($conn->inTransaction()) $conn->rollBack();
    echo "<h3>Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
?>