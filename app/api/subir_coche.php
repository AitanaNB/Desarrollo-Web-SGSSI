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

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Conexión a la base de datos
include 'bdcon.php';

// Procesar el formulario de subida de coche (Cuando se clica en el boton de subir un coche)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir datos del formulario
    $matricula = $_POST['matricula'];
    $modelo = $_POST['modelo'];
    $marca = $_POST['marca'];
    $color = $_POST['color'];
    $kilometraje = $_POST['kilometraje'];
    $precio = $_POST['precio'];
    $en_venta = isset($_POST['en_venta']) ? 1 : 0;
    $id_propietario = $_SESSION['user_id'];

    if (empty($matricula) || empty($modelo) || empty($marca) || empty($color) || empty($kilometraje) || empty($precio)) {
        $mensaje = "<div class='alert alert-error'>Todos los campos son obligatorios.</div>";
    } else {
		
		try{
			// verificar si la matrícula ya existe
			$check_sql = "SELECT id FROM coches WHERE matricula = :matricula";
			$params = [':matricula' => $matricula];
			$stmt = $conn->prepare($check_sql);
			$stmt->execute($params);
			$check_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			//si el resultado es (O mejor dicho, la variable no vale 0) eso quiere decir que ya existe, no lo volvemos a insertar
			if ($check_result) {
				$mensaje = "<div class='alert alert-error'>Error: Ya existe otro coche con esa matrícula.</div>";
			//si no existe, lo insertamos
			} else {
				$conn->beginTransaction();
				
				$sql = "INSERT INTO coches (matricula, modelo, marca, color, kilometraje, precio, en_venta, id_propietario)
						VALUES ( :matricula, :modelo, :marca, :color, :kilometraje, :precio, :en_venta, :id_propietario )";
				$params = [':matricula' => $matricula,':modelo' => $modelo,':marca' => $marca,':color' => $color,':kilometraje' => $kilometraje,':precio' => $precio,':en_venta' => $en_venta,':id_propietario' => $id_propietario];
				$stmt = $conn->prepare($sql);
				$stmt->execute($params);
				$conn->commit();
				
				//Si se llega aquí 
				echo "Coche subido con éxito";
			}	
		}catch (PDOException $e) {
            echo "<div class='alert alert-error'>Error en la consulta de subir el coche: ". htmlspecialchars($e->getMessage()) .  "</div>";
		}
	}

    
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Subir coche nuevo</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="shortcut icon" href="media/icon.svg" />
    <script src="../js/validarDatos.js"></script>
</head>
<body>
    <header>
        <div class="header-container">
            <div>
                <h1>Subir coche</h1>
                <p>Añade un coche nuevo a tu catálogo - Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
            </div>
        </div>
    </header>

    <nav>
        <a href="inicio.php">Inicio</a> |
        <a href="catalogo.php">Catálogo</a> |
        <a href="mis_coches.php"> Mis coches </a> |
        <a href="logout.php">Cerrar sesión (<?php echo htmlspecialchars($_SESSION['usuario']); ?>)</a>
    </nav>

    <main>
        <div class="form-container">
            <?php
            // Mostrar mensaje de error si existe
            if (isset($mensaje) && !empty($mensaje)) {
                echo $mensaje;
            }
            ?>
            <form id="item_add_form" method="POST" onsubmit="return validarCoche()">
                <table>
                    <tr>
                        <td><label for="matricula">Matrícula:</label></td>
                        <td><input type="text" id="matricula" name="matricula" required placeholder="1234-ABC"></td>
                    </tr>
                    <tr>
                        <td><label for="modelo">Modelo:</label></td>
                        <td><input type="text" id="modelo" name="modelo" required></td>
                    </tr>
                    <tr>
                        <td><label for="marca">Marca:</label></td>
                        <td><input type="text" id="marca" name="marca" required></td>
                    </tr>
                    <tr>
                        <td><label for="color">Color:</label></td>
                        <td><input type="text" id="color" name="color" required></td>
                    </tr>
                    <tr>
                        <td><label for="kilometraje">Kilometraje:</label></td>
                        <td><input type="number" id="kilometraje" name="kilometraje" min="0" max="9999999" value="<?php echo htmlspecialchars($coche['kilometraje']); ?>"></td>
                    </tr>
                    <tr>
                        <td><label for="precio">Precio:</label></td>
                        <td><input type="number" id="precio" max="99999999.99" step="0.01" name="precio" required></td>
                    </tr>
                    <tr>
                        <td><label for="en_venta">En venta:</label></td>
                        <td><input type="checkbox" id="en_venta" name="en_venta"></td>
                    </tr>
                </table>
                <button id="item_add_submit" type="submit" class="boton">Subir coche</button>
                <a href="mis_coches.php" class="boton">Cancelar</a>
            </form>
        </div>
</body>
</html>
