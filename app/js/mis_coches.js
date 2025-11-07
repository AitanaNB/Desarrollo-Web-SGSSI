//esto permite que funcionen las jqeries
$(document).ready(function () {
 
	//esta variable existe para recordar el id del coche selecionado previamente, por eso esta fuera de todas las funciones, para que todas la puedan leer y utilizar
	let idSeleccionado = '';
 
	//-----------------------------------------------------------------------------
	//funcion1: ONCLICK ELIMINAR
	$('.btn-eliminar').on('click', function () {
		//'This' selecciona lo que se ha clicado (La fila de la tabla), closest() coge el elemento más cercano y le especificamos que sea del tipo tr, en el tr es donde guardamos la informacion de los campos y el boton (enronces el tr mas cercano al boton es en el que se guarda el propio boton), pero tambien hemos insertado un data-id antes, hablando del cual, data selecciona un valor del tipo data e id con la etiqueta id (data-id)
		idSeleccionado = $(this).closest('tr').data('id');
		//Cambiamos el texto del mensaje pop-up "insertando" el valor de la matrícula pero no desde una variable, sino desde un método que calcula el valor al leer la frase (Asi intentamos evitar inserciones SQL)
		document.getElementById('texto-confirmacion').innerText =`¿Estás seguro de que quieres eliminar el coche de matricula ${$(this).closest('tr').find('td').eq(0).text()} ?`;
		//hacemos la ventana pop up visible
		document.getElementById('popup-confirmacion').style.display = 'block';
	});
	//-----------------------------------------------------------------------------
  
  
  
	//-----------------------------------------------------------------------------
	//funcion2:ONCLICK BOTON DEL POPUP QUE CONFIRMA ELIMINAR
	$('#boton-confirmar-eliminacion').on('click', function () {
		//si se clica en confirmar-eliminacion, suponemos que ya tenemos un valor en idSleccionado del paso anterior, pero aun así revisamos que el valor sea valido por se acaso 
		if (idSeleccionado == null) {
          console.error('No hay coche seleccionado para eliminar.');
          return;
		}
		//enviamos el jqery y esperamos respuesta
		$.post('eliminar_coche.php', { id: idSeleccionado })
			.done(function (data) {
				//Revisamos la respueta
				if (data.esta=== 'ok') {
					console.error('Coche eliminado con exito.');
					//para recargar la página y que se vea el coche borrado
					location.reload();
				} else {
					console.error('Algo extraño acaba de pasar, y esto es lo que devuelve el servidor: ', data);
				}
			})
		.fail(function (jqXHR, textStatus, errorThrown) {
				console.error('Error en la conexion de eliminar_coches:', textStatus, errorThrown);
		});
	});
	//-----------------------------------------------------------------------------
	
	
	//funcion3:-----------------------------------------------------------------------------	
	//ONCLICK MODIFICAR
	$('.btn-modificar').on('click', function () {
		idSeleccionado = $(this).closest('tr').data('id');
		/*
		Aqui queria haber usado un .post y luego un window.location.href para primero pasar la información y luego redirigir a la página
		¿El problema ?, 2 en realidad. 
			1) Eso son dos sesiones diferentes, el href cuenta compo otra consulta que se lanza al acabar la primera, y las variables no se mantienen de consulta en consulta 
			2) Como esta escrito al menos el php, para salir de modificar_php necesitamos input del usuario, si el orden de ejecución entra solo sin usuario se queda atascado porque se queda esperando input de usuario (Pulsar el boton guardar cambios por ejemplo)
		¿La solucion ?, cookies 
		*/
		//mandamos al sistema a la hoja de php que crea la cookie (Esto es porque las cookies solo se pueden crear y normalmente modificar desde php)
		$.post('hornear_cookie_coche.php', { id: idSeleccionado })
			//una vez se ha regresado del php que crea la cookie 
			.done(function (data) {
				//si todo ha ido bien con la cookie
				if (data.cookie=== 'si ^w^') {
					console.error('se horneo la cookie :D');
					//Movemos al usuario a la página de modificar coche, donde podremos acceder a la cookie, porque las cookies se pueden acceder desde cualquier sitio que queramos de la página (y son bastante seguras)
					window.location.href = 'modificar_coche.php';
				} else {
					console.error('oh... no se ha podido hornear la cookie porque: ', data.cookie);
				}
			})
			//fallo horroroso
			.fail(function (jqXHR, textStatus, errorThrown) {
				console.error('No se ha podido hornear la cookie :C, y no sabemos porque quizá esto ayude a saber porque: ', textStatus, errorThrown);
			})
	});
	//-----------------------------------------------------------------------------
 
	//Ejecucion de la pagina
	//(Aqui no hay nada porque no hay nada que se ejecute nada más lanzar la página, sino que esperamos siempre a clicks)

})
