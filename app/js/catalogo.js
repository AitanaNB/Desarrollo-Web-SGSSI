// Variables globales
let matriculaSeleccionada = '';

  /*
   ___________________________________________________________________________________
  /                                                                                                                                                           \
 | CargarCoches (función que pide a la base de datos los coches y los pasa a la tabla)                              |
  \___________________________________________________________________________________ /
  */
// Función para cargar coches desde el servidor
function cargarCoches() {
    console.log('Iniciando carga de coches...');
    
    //lanzamos el script coge-coches desde la posicion relativa del documento en que se esta, como catalogo y coge-coches estan los dos en api no hace falta especificar la ruta desde index, solo se pone el nombre del documento 
    var order = document.getElementById('order_select').value || 'modelo';
    
    $.post('coge-coches.php', { orden: order })
        .done(function(data) {
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
            
            try {
                // Verificar si ya es un objeto o necesita parseo
                if (typeof data === 'string') {
                    data = JSON.parse(data);
                }
                
                if (!data || !Array.isArray(data) || data.length === 0) {
                    console.log('No hay coches disponibles');
                    //muestra horrores más grandes que la imaginación del usuario ("horrors beyod the users comprehension")
                    document.getElementById("error-no-car").style.display = "block";
                    //document.getElementById("tabla-coches").style.display = "none";
                    return;
                }
                
                console.log('Cargando', data.length, 'coches en la tabla');
                //vale aqui definimos un string vacio que llamamos filas, ahora el truco es que le damos valores en un bucle donde por cada coche en datos transferimos los valores de los campos de la tabla formateados como código de html dinámicamente y los añadimos a los que ya existen gracias al =+
				//la clase fila-seleccionable se la añadimos a cada fila para cuando luego tengamos que hacer toda la parte de seleccionar coches
				//el resultado en filas es un string que contiene todo el codigo de html ya formateado para meter en la tabla los coches de la consulta
                //document.getElementById("error-no-car").style.display = "none";
                //document.getElementById("tabla-coches").style.display = "table";
                
                var filas = '';
                data.forEach(coche => {
                    filas +=
                    `<tr class="fila-seleccionable">
                        <td>${coche.modelo || ''}</td>
                        <td>${coche.marca || ''}</td>
                        <td>${coche.kilometraje || ''}</td>
                        <td>${coche.color || ''}</td>
                        <td>${coche.vendedor || ''}</td>
                        <td>${coche.matricula || ''}</td>
                        <td>${coche.precio || ''}</td>
                    </tr>`;
                });
                // Insertamos el html que acabamos de crear guardado en la variable filas en el tbody de la tabla a trabes de una Jquery
				$('#tabla-coches tbody').html(filas);
                
            } catch (error) {
                console.error('Error al parsear JSON:', error);
                document.getElementById("error-no-car").style.display = "block";
                document.getElementById("tabla-coches").style.display = "none";
            }
        })
        //despues de mucha prueba y error puse este codigo de internet *modificado un poco* que muestra los errores que se dan si falla brutalmente el JSON, así pude saber que estaba mal en el encode que mando de la otra hoja 
        .fail(function(jqXHR, textStatus, errorThrown) {
            console.error('Error en la conexión:', textStatus, errorThrown);
            //document.getElementById("error-no-car").style.display = "block";
            //document.getElementById("tabla-coches").style.display = "none";
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

// Función para cerrar popup de fallo
function cerrarPopupFallo() {
    document.getElementById('popup-fallo').style.display = 'none';
}

// Función para manejar la compra de coches
function comprarCoche() {
    if (!matriculaSeleccionada || matriculaSeleccionada === '') {
        console.error('No has seleccionado ningún coche.');
        return;
    }
    
    $.post('compra-coches.php', { matricula: matriculaSeleccionada })
        .done(function(data) {
            console.log('Respuesta compra:', data);
            
            try {
                if (typeof data === 'string') {
                    data = JSON.parse(data);
                }
                
                if (data.resultado === 'rico') {
                    console.log('Nos honrra poder llevar acabo su transacción usuario, gracias por comprar con nosotros.');
                    mostrarPopupCompraExitosa();
                } else if (data.resultado === 'pobre') {
                    console.log('ALERTA POBRE, ALERTA POBRE.');
                    mostrarPopupSinDinero();
                } else {
                    console.error('Respuesta inesperada:', data);
                }
            } catch (error) {
                console.error('Algo extraño acaba de pasar, y esto es lo que devuelve el servidor: ', error);
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            console.error('Error en la conexion de comprar-coches:', textStatus, errorThrown);
        });
}


// Función para ir al inventario
function irAInventario() {
    window.location.href = 'mis_coches.php';
}

//Recargar la página y que se vea que ha cambiado el catalogo de coches
function recargarPagina() {
    location.reload();
}

// Inicialización cuando el documento está listo
$(document).ready(function() {
        /*
    ______________________________________________________________
    /                                                                                                                    \
    |  Lo que se ejecutará una vez se cargue la página                                           |
    \______________________________________________________________ /
    */
    //Nada más cargar la página la primera vez, se ejecuta la funcion para darle valores a la tabla
    cargarCoches();
    
    // Evento para cambiar el orden
    $('#order_select').on('change', function() {
        $('.fila-seleccionable').removeClass('fila-selec');
        document.getElementById("btn-comprar").innerText = 'COMPRA ESTE MAGNIFICO COCHE AHORA !!!! :D';
        document.getElementById("btn-comprar").style.display = "none";
        matriculaSeleccionada = '';
        cargarCoches();
    });
    
    // Evento para seleccionar fila de coche
    $('#tabla-coches').on('click', '.fila-seleccionable', function() {
        // Quitar highlight de filas anteriores
        $('.fila-seleccionable').removeClass('fila-selec');
        
        // Añadir highlight a la fila clickeada
        $(this).addClass('fila-selec');
        
        // Guardar matrícula seleccionada
        matriculaSeleccionada = $(this).children().eq(5).text();
        
        // Habilitar botón de comprar
        document.getElementById("btn-comprar").style.display = "inline-block";
        
        // Personalizar texto del botón
        var nombreUser = $(this).children().eq(4).text(); // Obtener nombre del vendedor
        var marcaCoche = $(this).children().eq(1).text();
        document.getElementById("btn-comprar").innerText = `COMPRA ESTE MAGNÍFICO ${marcaCoche.toUpperCase()} AHORA !!!!! :D`;
    });
    
    // Evento para botón de comprar
    $('#btn-comprar').on('click', comprarCoche);
    
    // Evento para botón "Seguir comprando"
    $('#seguir-comprando').on('click', recargarPagina);

    // Evento para botón "Ver mi inventario"
    $('#ver-inventario').on('click', irAInventario);

    // Evento para botón "Oh :,c" del popup de fallo
    $('#cerrar-fallo').on('click', cerrarPopupFallo);
});