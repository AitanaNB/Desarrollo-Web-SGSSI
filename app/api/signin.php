<?php
// Conexión a la base de datos
include 'bdcon.php'; // Este archivo debe definir: $conn

//hacemos try catch por si las moscas (No va la conexion con la bd)
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

	// Validación simple
	if (empty($nombre) || empty($email) || empty($password)) {
		die("Todos los campos son obligatorios.");
	}
	
	//La inserción normalmente se suele meter en un try,catch por si nos da un error, para hechar para atras y que no se quede insertado en la base de datos. Pero todo esto ya esta en un try,catch, a si que no tiene mucho sentido hacer dos
	$conn->beginTransaction();
	
	$sql = "INSERT INTO usuarios
	(nombre, apellidos, dni, telefono, fecha_nacimiento, email, username, password)
	VALUES (
		:nombre, :apellidos, :dni, :telefono, :fecha_nacimiento, :email, :username, :password
	)";
	
	$params = [':nombre' => $nombre,':apellidos' => $apellidos,':dni' => $dni,':telefono' => $telefono,':fecha_nacimiento' => $fecha_nacimiento,':email' => $email,':username' => $username,':password' => $password];
	
	//el 'if,else' ya no lo necesitamos porque es literalmente el 'try,catch' que guarda a la función
	$stmt = $conn->prepare($sql);
    $stmt->execute($params);
	//Cargamos los cambios de la transacción en la bd (No ha habido ningun error)
	$conn->commit();
	echo "Usuario registrado con éxito";
	
} catch (PDOException $e) {
	//En caso de estar en una transaccion, la detenemos
	if ($conn->inTransaction()) $conn->rollBack();
    echo "<h3>Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
?>