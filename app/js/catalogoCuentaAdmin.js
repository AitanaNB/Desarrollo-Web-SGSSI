//esto permite que funcionen las jqeries
$(document).ready(function () {
 
 //esta variable existe para recordar el id del usuario selecionado previamente, por eso esta fuera de todas las funciones, para que todas la puedan leer y utilizar
 let idSeleccionado = '';
 
 //-----------------------------------------------------------------------------
 //funcion1: ONCLICK ELIMINAR
  $('.catalogo-admins-eliminar').on('click', function () {
	//Con el 'this seleccionamos el eelemento más cercano al que se ha clicado del tipo tr, y luego buscamos el primer td que debería de tener el id'
	idSeleccionado = $(this).closest('tr').find('td').eq(0).text()
	//cambiamos texto del mensaje 
	document.getElementById('texto-confirmacion-admin').innerText =`¿Admin, seguro que quieres ELIMINAR al usuario de nombre ${$(this).closest('tr').find('td').eq(1).text()} e id ${idSeleccionado} ?`;
	//hacemos la ventana pop-up visible
    document.getElementById('popup-confirmacion-admin').style.display = 'block';
  });
  //-----------------------------------------------------------------------------
  
 //-----------------------------------------------------------------------------
 //funcion2:ONCLICK BOTON DEL POPUP QUE CONFIRMA ELIMINAR
 $('#boton-confirmar-eliminacion').on('click', function () {
	document.getElementById('popup-confirmacion-admin').style.display = 'none'; 
	//si se clica en confirmar-eliminacion, suponemos que ya tenemos un valor en idSleccionado del paso anterior, pero aun así revisamos que el valor sea valido por se acaso 
	if (idSeleccionado == null) {
          console.error('No hay ususario seleccionado para eliminar.');
          return;
      }
	//enviamos el jqery y esperamos respuesta
	$.post('eliminar_usuario.php', { id: idSeleccionado })
		.done(function (data) {
			//Revisamos la respueta
            if (data.esta=== 'ok') {
				console.error('usuario eliminado con exito.');
				//para recargar la página y que se vea el usuario borrado
				location.reload();
		    } else {
				console.error('Algo extraño acaba de pasar, y esto es lo que devuelve el servidor: ', data);
			}
		})
		.fail(function (jqXHR, textStatus, errorThrown) {
				console.error('Error en la conexion de eliminar_ususario:', textStatus, errorThrown);
		});
 });
 //-----------------------------------------------------------------------------
 //funcion3:-----------------------------------------------------------------------------
//ONCLICK NO ELIMINAR (usuario exibe remordimientos de matar al coche)
$('#boton-remordimientos').on('click', function () {
//apaga la el popup
document.getElementById('popup-confirmacion-admin').style.display = 'none';
});
//-----------------------------------------------------------------------------
 //Ejecucion de la pagina
 //(Aqui no hay nada porque no hay nada que se ejecute nada más lanzar la página, sino que esperamos siempre a clicks)

})
