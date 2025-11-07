<?php

//esto de aquí regula aspetos de las cookies de sesión (son configuraciones internas que php le aplicaráa todas las cookies de sesión que genere)
ini_set('session.cookie_httponly', 1); // <- pone 'httponly' a true para las cookies de sesión
ini_set('session.cookie_secure', 1);  // <- pone 'secure' a true para las cookies de sesión (No se cuanto sentido tiene hacerlo para nuestro sistema cuando esto solo se aplica a paginas con certificado https)
ini_set('session.use_only_cookies', 1);  // <- impide que php guarde el id de sesion por la url y le fuerza a guardarlo en cookies (Que son más seguras)

//esto ahora se que hace, si no tienes esto, no puedes acceder a las variables de sesión y entonces te va a saltar el "if (!isset($_SESSION['user_id']))"
session_start();

// Content-Security-Policy (CSP)
$csp_policy = "default-src 'self'; ";
$csp_policy .= "script-src 'self'; "; 
$csp_policy .= "style-src 'self'; ";
$csp_policy .= "img-src 'self' data:; "; 
$csp_policy .= "frame-ancestors 'none';";
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



// Verificar si el usuario está logueado, y le lleva a loguearse si no
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Literalmente llama a la conexion de la base de datos (Aun que no se usa en esta hoja)
include 'bdcon.php';


?>

<!DOCTYPE html>
<html>
<!-- titulo de la página -->
<head>
    <title>Coches disponibles - COMPRAMOS TU COCHE</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
</head>

<!-- mensajes relacionados con la compra -->
<!-- popup exito -->
<div id="popup-exito">
    <h2>¡Felicidades por su nueva adquisición exitosa, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!</h2>
	<p>Nos honra que haya decidido usar nuestro mercado de coches abierto a los usuarios</p>
	<p>esperamos disfrute de su nuevo vehiculo y que vuelva a comprar con nosotros pronto.</p>
    <button class="boton-popup" id="ver-mi-inventario" >Ver mi inventario</button>
    <button class="boton-popup" id="seguir-comprando">Seguir comprando</button>
</div>
<!-- popup pobre -->
<div id="popup-fallo">
    <h2>No tienes suficiente dinero para comprar este coche</h2>
    <p><?php echo htmlspecialchars($_SESSION['usuario']); ?>, vuélvelo a intentar cuando seas un poco más... rico.</p>
    <img src="../media/Morshu.png" class="morshu-image" alt="Morshu"/>
    <button class="boton-popup" id="oh">Oh :,c</button>
</div>

<!--Metemos el atributo del body para luego leerlo en el script desde js-->
<body data-usuario="<?php echo htmlspecialchars($_SESSION['usuario']); ?>">
<!-- subtitulo -->
<header>
        <div class="header-container">
            <div>
                <h1>Comprar Coches</h1>
                <p>Compra coches al mejor precio <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
            </div>
        </div>
</header>
<!-- Menu para ir a otras partes de la pagina -->
<nav>
        <a href="inicio.php">Inicio</a> |
        <a href="mis_coches.php">Mis Coches</a> |
        <a href="logout.php">Cerrar sesión (<?php echo htmlspecialchars($_SESSION['usuario']); ?>)</a>
</nav>
<!-- El resto de la pestaña -->
<main>
	<br>
	<br>
	<!-- Ordenador -->
	<form id="frm_selector" data-ajax="false">
                <div class="ui-field-contain">
                    <label for="order_select">Ordenar Coches Según:</label>
                    <select name="order_select" id="order_select">
                        <option value="modelo">Modelo</option>
                        <option value="marca">Marca</option>
						<option value="kilometraje">Kilometraje</option>
						<option value="color">Color</option>
						<option value="vendedor">Vendedor</option>
						<option value="matricula">Matricula</option>
						<option value="precio">Precio</option>
                        <!-- valores posibles: marca,color,kilometraje,precio *IMPORTANTE QUE ESTEN ESCRITOS ASI, SI NO PETA TODO* -->
                    </select>
                </div>
        </form>
        <!-- Tabla -->
        <table id="tabla-coches">
			<!-- Con esto creamos los titulos, la cabeza (head en ingles) de la tabla -->
			<thead>
				<tr>
					<th>Modelo</th>
					<th>Marca</th>
					<th>Kilometraje</th>
					<th>Color</th>
					<th>Vendedor</th>
					<th>Matricula</th>
					<th>Precio</th>
				</tr>
			</thead>
			<!-- Aquí insertaremos dinámicamente las filas, no se si hace falta ponerlo vacio, yo por si acaso lo hago -->
			<tbody>
			</tbody>
        </table>
        <!-- No hay coches messaje -->
        <div id="error-no-car">
        	<p class="sowy">Lo Chentimos UnU, o no hay coches o awgo va howwibwemente maw ha pawsado<p>
        	<img src="../media/catalogo-error.png" width="640" height="320"/>
        	<p class="sowy"><?php echo htmlspecialchars($_SESSION['usuario']); ?> san, ayudanos a descubwiw cuaw de was dos ocuwre poniendo un cowche tuyo en vewnta UwU</p>
        </div>
        <!-- Boton -->
        <button id="btn-comprar">
        	COMPRA EL COCHE AHORA !!!!! :D
        </button>
</main>

<footer>
    <a href="https://github.com/AitanaNB/Desarrollo-Web-SGSSI">Nuestro maravilloso y organizado código está disponible en Github</a>
</footer>

<!-- Llamadas a los Scripts -->
<!-- El primer script es solo para cargar la libreía de Jquerys, el segundo es nuestro código de catálogo. Ponemos los scripts en una hoja de js aparte para que el codio sea más limpio y más seguro -->
<!-- Por cierto, esto son llamadas a scripts, con lo que no llvan código, le estamos diciendo que en ese path esta el codigo que tiene que leer, si se pone algo más entre los <script> no se leera -->
<script src="../js/jquery-3.5.1.min.js"></script>
<script src="../js/catalogo.js"></script>

</body>
</html>