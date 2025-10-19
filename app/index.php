<?php
session_start();
include './api/bdcon.php';
 

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
            } else if(tipo === 'registro'){ //VALIDANDO FORMATO
                div.innerHTML = `
                    <h3>Registrarse</h3>
                    <form action="/api/register.php" method="POST" onsubmit="return validarDni()">
                        Nombre: <br><input type="text" name="nombre" required><br>
                        Apellidos: <br><input type="text" name="apellidos" required><br>
                        DNI (formato 11111111-X): <br><input type="text" name="dni" id="dni" maxlength="10" pattern="^\\d{8}-[A-Z]$" required
                        title="Debe tener 8 números, un guion y una letra mayúscula, como 12345678-Z"><br>
                        Tlf: <br><input type="text" name="telefono" pattern="^\\d{9}$" required ><br>
                        Fecha nacimiento: <br><input type="date" name="fecha_nacimiento" required><br>
                        Email: <br><input type="email" name="email" required><br>
                        Username: <br><input type="text" name="username" required><br>
                        Contraseña: <br><input type="password" name="password" required><br><br>
                        <input type="submit" value="Registrarse">
                    </form>
                `;
                
            }
                    
        }
    
        function validarDni(){
            const letras = "TRWAGMYFPDXBNJZSQVHLCKE";
            const dniInput = document.getElementById("dni");
            const dni = dniInput.value.toUpperCase().trim();

            // Separar número y letra
            const partes = dni.split("-");
            const numero = parseInt(partes[0]);
            const letra = partes[1];

            // Calcular la letra correcta
            const letraCorrecta = letras[numero % 23];

            // Comparar
            if (letra !== letraCorrecta) {
                alert(`Letra de DNI incorrecta.`);
                return false;
            }

            return true; // Todo correcto
        }

    </script>

<br>
<footer>
    <!--&copy; <?= date('Y') ?> <br>-->
     <a href="https://github.com/AitanaNB/Desarrollo-Web-SGSSI">Nuestro maravilloso y organizado código está disponible en Github</a>
     
</footer>
</body>
</html>