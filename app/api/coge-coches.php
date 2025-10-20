<?php

// LLamada a la conexion de la base de datos 
include 'bdcon.php';

//Aqui quería haber ido de guays y haber definido una conexion PDO con las variables de bdcon, pero la imagen de apache-docker que corremos no tiene PDO instalado por defecto, y meterle la librería requeriría cambiar el yml y no creo que ninguno queramos pasar por eso
//Aun que diré, PDO es más seguro que mysqli y tiene algunas funciones predefinidas para hacer cosas más rápido

// Cogemos en funcion de que quiere el usuario que ordenemos los coches. En caso de error ordenamos según  
$orden = $_GET['orden'] ?? 'modelo'; // Por defecto ordenamos en funcion del modelo
/* Creo que es un poco superfluo trar el caso null, porque si el encode valía null en la página de donde se hace el llamamiemto nunca se siquiera lanza la 
Jquery y por eso si la variable vale null en catalogo.php ya le cambio el valor, pero nadie se a muerto por ser cuidadoso*/ 

//consulta de SQL donde de coogen los coches QUE ESTAN EN VENTA	
$sql = "
SELECT
    	c.modelo,
    	c.marca,
    	c.kilometraje,
    	c.color,
    	u.username AS vendedor,
    	c.matricula,
    	c.precio

FROM coches c
LEFT JOIN usuarios u ON u.id  = c.id_propietario
WHERE c.en_venta = '1'
ORDER BY $orden
";
//el motor no me deja hacer ORDER BY :orden y luego inyectar el valor con $params = [':orden' => $orden]; y luego $stmt->execute($params); por alguna razon, pero aún que sea menos seguro, tambien se puede insertar la variable como esta arriba, directamente en el codigo

//lanzamos las query
//Con PDO pudiesemos solo haber hecho solo $result = $conn->prepare($sql); $result->execute(); $coches = $result->fetchAll(PDO::FETCH_ASSOC); en lugar de todo esto :,c 
$result = mysqli_query($conn, $sql);
$coches = [];
while ($row = $result->fetch_assoc()) {
    $coches[] = $row;
}

//enviamos el encode
	echo json_encode($coches);
	exit;
?>
