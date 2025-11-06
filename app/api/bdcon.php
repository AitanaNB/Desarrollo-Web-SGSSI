<?php
  // Configuración de la base de datos
  // Para hacer la conexion tipo PDO se necesitan cosas ligeramente diferentes
  $username = "admin";
  $password = "test";
  //esto es lo nuevo, en lugar de tener el host (Que en el caso de docker es donde esta el servicio en el yml, osea db) y el nombre de la base de datos por separado, lo especificamos todo en una misma variable junto con el charset a usar)
  $dsn = "mysql:host=db;dbname=database;charset=utf8";
  //en cuanto al charset usamos utf8 por que es como el generico (Creo)
  
  //Nos aprobechamos de que PDO genera errores con el típico 'try,catch' (esto lo repetiremos mucho)
  try{
	$conn = new PDO($dsn, $username, $password);
	//esto lo he visto poner para que el PDO te lance los errores
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
  }
  
 //Os recomiendo que como ejemplo de cambios en las consultas ahora que usamos PDO, veais la página 'login.php', es la primera que rescribí con esta nueva filosofia y en consecuencia la que más comentarios tiene. Luego ya, por regla general, era hacer un poco lo mismo en todas
 //register.php es otro buen ejemplo, es algo más complejo que login.php y cubre inserciones 
?>