<?php

//esto de aquí regula aspetos de las cookies de sesión (son configuraciones internas que php le aplicaráa todas las cookies de sesión que genere)
ini_set('session.cookie_httponly', 1); // <- pone 'httponly' a true para las cookies de sesión
ini_set('session.cookie_secure', 1);  // <- pone 'secure' a true para las cookies de sesión (No se cuanto sentido tiene hacerlo para nuestro sistema cuando esto solo se aplica a paginas con certificado https)
ini_set('session.use_only_cookies', 1);  // <- impide que php guarde el id de sesion por la url y le fuerza a guardarlo en cookies (Que son más seguras)

//esto ahora se que hace, si no tienes esto, no puedes acceder a las variables de sesión y entonces te va a saltar el "if (!isset($_SESSION['user_id']))"
session_start();


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
    <button class="boton-popup" onclick="window.location.href='mis_coches.php'">Ver mi inventario</button>
    <button class="boton-popup" id="seguir-comprando" onclick="document.getElementById('popup-exito').style.display='none'">Seguir comprando</button>
</div>
<!-- popup pobre -->
<div id="popup-fallo">
    <h2>No tienes suficiente dinero para comprar este coche</h2>
    <p><?php echo htmlspecialchars($_SESSION['usuario']); ?>, vuélvelo a intentar cuando seas un poco más... rico.</p>
    <img src="../media/Morshu.png" style="width:150px; height:auto; float:right; margin-top:-80px;"/>
    <button class="boton-popup" onclick="document.getElementById('popup-fallo').style.display='none'">Oh :,c</button>
</div>

<body>
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
	<!-- repositorio con nuestro maravilloso código -->
	<footer>
        	<a href="https://github.com/AitanaNB/Desarrollo-Web-SGSSI">Nuestro maravilloso y organizado código está disponible en Github</a>
	</footer>
	
</body>
</html>

<!-- esto de aquí no es un script, es un link a la libreria de las Jqueris que le permite al java script de esta página utilizarla-->
<script src="../js/jquery-3.5.1.min.js"></script>

<!-- esto es un codigo de javascript que hace cositas (basicamente carga los coches y hace la compra)-->
<script>
//Nota actualizada: arriba vereís que hay un escript solo para cargar la libreía de Jquerries ESTO TIENE QUE SER ASI, si pones codigo dentro de ese script el comilador lo ignorará o peor 

//esto esta por encima de todo para que las Jqueris funcionen
$(document).ready(function () {
  /*
   ___________________________________________________________________________________
  /                                                                                                                                                           \
 | CargarCoches (función que pide a la base de datos los coches y los pasa a la tabla)                              |
  \___________________________________________________________________________________ /
  */
  function cargarCoches () {
        console.log('Iniciando carga de coches...');
        
        //lanzamos el script coge-coches desde la posicion relativa del documento en que se esta, como catalogo y coge-coches estan los dos en api no hace falta especificar la ruta desde index, solo se pone el nombre del documento 
		var order = document.getElementById('order_select').value ?? 'modelo';
        $.post('coge-coches.php', { orden: order })
          .done(function (data) {
              /*IMPORTANTE
			  
				Aqui, esto se solia hacer con Jquerys con el metodo .getJSON()
				Sin embargo, me di cuenta de que usaba la URL para pasar y recibir la información, a si que lo sustituí por el metodo .post(), que sintactica y funcionalmente es igual pero utiliza el metodo POST para pasar la inormación
				-POST consiste meter las variables en un mensaje http, es un metodo Jquery a si que seguimos necesitando la librería
				-Tambien nos obliga a que en los backends, las variables ya no se cogen con $_GET['variable_ejemplo'] sino con $_POST['variable_elemplo']
				-Y a demas, lo siguiente es MUY IMPORTANTE para la funcion de debajo de esta y me ha llevado un monton de tiempo,sudor y lagrimas descubrir:
				--Asume que lo que le devuelves es un objeto del tipo string, no array, que es como son los JSON por defecto
				--Esto es importante aqui porque en las dos funciones que lo hacen, no leer bien el JSON es un error crítico (en una no se identificaría bien que pop-up lanzar, en la otra no se podria leer bien los coches que se cargan en la tabla)
				-pues utilizamos 'JSON.parse(data);' para convertir data de un objeto string a un objeto como son los JSON, array 
			  */
			  console.log('Respuesta recibida:', data);
			  data = JSON.parse(data);
			  if (!data || !Array.isArray(data) || !data.length) {
                  console.log('No hay rutas o data no se ha formado bien');
				  //muestra horrores más grandes que la imaginación del usuario ("horrors beyod the users comprehension")
                  document.getElementById("error-no-car").style.display = "block"
				  return;
			  }
			  else{
				console.log('cargando', data.length, 'coches en la tabla');
				//vale aqui definimos un string vacio que llamamos filas, ahora el truco es que le damos valores en un bucle donde por cada coche en datos transferimos los valores de los campos de la tabla formateados como código de html dinámicamente y los añadimos a los que ya existen gracias al =+
				//la clase fila-seleccionable se la añadimos a cada fila para cuando luego tengamos que hacer toda la parte de seleccionar coches
				//el resultado en filas es un string que contiene todo el codigo de html ya formateado para meter en la tabla los coches de la consulta
				var filas = '';
				data.forEach(coche => {
					filas +=
					`<tr class="fila-seleccionable">
						<td>${coche.modelo}</td>
						<td>${coche.marca}</td>
						<td>${coche.kilometraje}</td>
						<td>${coche.color}</td>
						<td>${coche.vendedor}</td>
						<td>${coche.matricula}</td>
						<td>${coche.precio}</td>
					</tr>`;
				});

				// Insertamos el html que acabamos de crear guardado en la variable filas en el tbody de la tabla a trabes de una Jquery
				$('#tabla-coches tbody').html(filas);
	          }
		   })
          //despues de mucha prueba y error puse este codigo de internet *modificado un poco* que muestra los errores que se dan si falla brutalmente el JSON, así pude saber que estaba mal en el encode que mando de la otra hoja 
          .fail(function(jqXHR, textStatus, errorThrown) {
    	     console.error('Error en la conexion de coger-coches:', textStatus, errorThrown);
          });
  }
  /*
   _____________________________________________________________________________
  /                                                                                                                                               \
 |  Gestionadores de pop ups (se encargan de mostrar las ventanas relacionadas con la compra)  |
  \_____________________________________________________________________________/
  */
  //muestra el pop-up de cuando la compra va bien
  function mostrarPopupCompraExitosa() {
    document.getElementById('popup-exito').style.display = 'block';
  }
  //muestra el pop-up de cuando la compra va mal porque no hay dinero
  function mostrarPopupSinDinero() {
    document.getElementById('popup-fallo').style.display = 'block';
  }
  /*
   ______________________________________________________________
  /                                                                                                                    \
 |  Lo que se ejecutará una vez se cargue la página                                           |
  \______________________________________________________________ /
  */
  //Nada más cargar la página la primera vez, se ejecuta la funcion para darle valores a la tabla
  cargarCoches();
  
  //declaramos esto tambien aqui por se acaso
  let matriculaSeleccionada = '';
  
  //cada vez que cambie el valor del desplegable, se tiene que volver a lanzar cargarCoches
  $('#order_select').on('change', function() {
    
	//antes de voler a cargar la tabla, borramos las selecciones y escondemos y reseteamos los valores del boton de compra (por se acaso, aun que no se vaya a mostrar, esta bien que tenga un valor por defecto)
	$('.fila-seleccionable').removeClass('fila-selec');
	document.getElementById("btn-comprar").innerText ='COMPRA ESTE MAGNIFICO COCHE AHORA !!!! :D';
	document.getElementById("btn-comprar").style.display = "none"
	matriculaSeleccionada = '';
	cargarCoches();
  });
  
  //cada vez que se clique en una fila de la tabla (un elemento de la tabla de la clase fila-seleccionable) se lanzará este evento
  $('#tabla-coches').on('click', '.fila-seleccionable', function () {
    // Quitamos el higlight las filas preseleccionadas (de haber seleccionado una antes)
    $('.fila-seleccionable').removeClass('fila-selec');

    // Añadimos la clase asociada al higlight a la fila clicada
    $(this).addClass('fila-selec');

    //Guardamos la matrícula en la variable: children hace referencia a los datos detro del <td>, eq(5) al 5, text() es con lo que se sustrae el valor 
    matriculaSeleccionada = $(this).children().eq(5).text();
	
	//habilitamos el boton de comprar
	document.getElementById("btn-comprar").style.display = "inline-block"
	
	//Personalizamos el texto del boton para que el anuncio impacte más al consumidor, como en una de esas distopías cyberpunk
	//Si, el nuevo texto tiene que ir entre esas comillas raras `` para poder aplicar los ${} que permiten insertar los valores de las variables
    var nombreUser = <?php echo  json_encode($_SESSION['usuario']); ?>;
	document.getElementById("btn-comprar").innerText =`${nombreUser.toUpperCase()} COMPRA ESTE MAGNÍFICO ${$(this).children().eq(1).text().toUpperCase()} AHORA !!!!! :D`;
  });
  
  //cada vez que se le de a comprar
  $('#btn-comprar').on('click', function () {
	//en el caso rarisimo de que se rompa el boton de comprar coche (partimos de que ya tenemos un valor de matriculaSeleccionada porque se le da cuando se clica en un coche, algo indispensable para comprar un coche)
    if (!matriculaSeleccionada | matriculaSeleccionada == '') {
        console.error('No has seleccionado ningún coche.');
        return;
    }
	
	//lanzamos el script de php y esperamos el encode de respuesta 
    $.post('compra-coches.php', { matricula: matriculaSeleccionada })
        //Como con el de caegarCoches, con esto cogemos los errores
		.done(function (data) {
            if (data.resultado=== 'rico') {
				console.error('Nos honrra poder llevar acabo su transacción usuario, gracias por comprar con nosotros.');
                mostrarPopupCompraExitosa();
            } else if (data.resultado === 'pobre') {
                console.error('ALERTA POBRE, ALERTA POBRE.');
				mostrarPopupSinDinero();
            }
			else {
				console.error('Algo extraño acaba de pasar, y esto es lo que devuelve el servidor: ', data);
			}
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
           console.error('Error en la conexion de comprar-coches:', textStatus, errorThrown);
        });
   });
   //cada vez que se le de a seguir comprando (en el pop up)
  $('#seguir-comprando').on('click', function () {
	//Recargar la página y que se vea que ha cambiado el catalogo de coches
	location.reload();
  });
})
</script>
