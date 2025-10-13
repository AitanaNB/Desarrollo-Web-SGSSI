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

//while ($row = mysqli_fetch_array($query)) {
 // echo
   //"<tr>
   // <td>{$row['id']}</td>
    //<td>{$row['nombre']}</td>
   //</tr>";
   

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>COMPRAMOS TU COCHE</title>
</head>
<body>
    <h1>FORO COMPRAMOS TU COCHE</h1>
    <p>te vamos a robar los datos y el coche </p>

    <button onclick="mostrar('login')">Iniciar sesión</button>
    <button onclick="mostrar('registro')">Registrarse</button>

    <div id="contenido" style="margin-top:20px;"></div>

    <script>
      //para cargar contenido
        function mostrar(tipo) {
            const div = document.getElementById('contenido');
            if(tipo === 'login'){
                div.innerHTML = `
                    <div class="form-box">
                    <h3>Iniciar sesión</h3>
                    <form id="loginForm">
                        <label>Usuario:</label><br><input type="text" name="usuario" required><br>
                        <label>Contraseña:</label><br><input type="password" name="password" required><br><br>
                        <input type="submit" value="Entrar">
                    </form>
                    <div id="loginMsg"></div>
                    <div id="loginError"></div>
                </div>
                `;
                //listener
                document.getElement
            } else if(tipo === 'registro'){
                div.innerHTML = `
                    <h3>Registrarse</h3>
                    <form onsubmit="alert('Registro enviado (demo)'); return false;">
                        Nombre: <br><input type="text" name="nombre" required><br>
                        Usuario: <br><input type="text" name="usuario" required><br>
                        Contraseña: <br><input type="password" name="password" required><br><br>
                        <input type="submit" value="Registrarse">
                    </form>
                `;
            }
        }
    </script>
</body>
</html>