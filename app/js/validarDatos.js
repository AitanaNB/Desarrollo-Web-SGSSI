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

function validarCoche(){
    const matricula = document.getElementById("matricula").value.trim();
    const modelo = document.getElementById("modelo").value.trim();
    const marca = document.getElementById("marca").value.trim();
    const kilometraje = document.getElementById("kilometraje").value.trim();
    const precio = document.getElementById("precio").value.trim();

    if (matricula === "" || modelo === "" || marca === "" || precio === "" || kilometraje === "") {
        alert("Por favor, complete todos los campos del coche.");
        return false;
    }

    // Validar matrícula 
    const matriculaPattern = /^\d{4}-[A-Z]{3}$/;
    if (!matriculaPattern.test(matricula)) {
        alert("La matrícula debe tener el formato '1234-ABC'.");
        return false;
    }

    // Validar kilometraje
    if (isNaN(kilometraje) || parseFloat(kilometraje) < 0) {
        alert("El kilometraje debe ser un número igual o mayor que 0.");
        return false;
    }


    // Validar precio
    if (isNaN(precio) || Number(precio) <= 0) {
        alert("El precio debe ser un número positivo.");
        return false;
    }

    return true; // Todo correcto
}