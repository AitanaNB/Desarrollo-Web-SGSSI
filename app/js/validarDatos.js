function validarDni(){
    console.log("validarDatos.js cargado correctamente");
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
