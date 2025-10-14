<?php
 // echo '<h1>COMPRAMOS TU COCHE <h1>';
 // echo "<h3>Te vamos a robar los datos</h3>";
  session_start();

  // phpinfo();
  $hostname = "db";
  $username = "admin";
  $password = "test";
  $db = "database";

  $conn = mysqli_connect($hostname,$username,$password,$db);
  if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
  }


//mostrar usuarios
$query = mysqli_query($conn, "SELECT * FROM usuarios")
   or die (mysqli_error($conn));
echo "<tr> Esto es para pruebas:</tr>";
while ($row = mysqli_fetch_array($query)) {
    echo
   "<tr>
    <td>{$row['id']}</td>
    <td>{$row['nombre']}</td>
   </tr>";
}
   

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>COMPRAMOS TU COCHE</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="shortcut icon" href="media/icon.svg" />
</head>
<body>
    <header>
        <h1>FORO COMPRAMOS TU COCHE</h1>
        <p>te vamos a robar los datos y el coche </p>
    </header>

    <div class="contenido">
        <img src="media/coche.jpg" width="300" height="200"/>
        <br>

        <button class="boton" onclick="mostrar('login')">Iniciar sesión</button>
        <button class="boton" onclick="mostrar('registro')">Registrarse</button>
    </div>

    <div id="contenido" style="margin-top:20px;"></div>

    <script>
      //para cargar contenido
        function mostrar(tipo) {
            const div = document.getElementById('contenido');
            if(tipo === 'login'){
                div.innerHTML = `
                    <div class="form-box">
                    <h3>Iniciar sesión</h3>
                    <form action="/api/login.php" method="POST">
                        <label>Usuario:</label><br><input type="text" name="usuario" required><br>
                        <label>Contraseña:</label><br><input type="password" name="password" required><br><br>
                        <input type="submit" value="Entrar">
                    </form>
                    <div id="loginMsg"></div>
                    <div id="loginError"></div>
                </div>
                `;
                //listener
                //document.getElement
            } else if(tipo === 'registro'){
                div.innerHTML = `
                    <h3>Registrarse</h3>
                    <form action="/api/signin.php" method="POST">
                        Nombre: <br><input type="text" name="nombre" required><br>
                        Apellidos: <br><input type="text" name="apellidos" required><br>
                        DNI: <br><input type="text" name="dni" maxlength="10" required><br>
                        Tlf: <br><input type="text" name="telefono" required><br>
                        Fecha nacimiento: <br><input type="date" name="fecha_nacimiento" required><br>
                        Email: <br><input type="text" name="email" required><br>
                        Username: <br><input type="text" name="username" required><br>
                        Contraseña: <br><input type="password" name="password" required><br><br>
                        <input type="submit" value="Registrarse">
                    </form>
                `;
            }
                    
        }
    </script>

<br>
<footer>
    <!--&copy; <?= date('Y') ?> <br>-->
     <a href="https://github.com/AitanaNB/Desarrollo-Web-SGSSI">Nuestro maravilloso y organizado código está disponible en Github</a>
     
</footer>
</body>
</html>