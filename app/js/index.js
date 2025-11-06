function mostrar(tipo) {
    const div = document.getElementById('contenido');
    if(tipo === 'login'){
        div.innerHTML = `
            <h3>Iniciar sesión</h3>
            <form action="/api/login.php" method="POST">
            <label>Usuario:</label><br><input type="text" name="usuario" id="usuario" required><br>
            <label>Contraseña:</label><br><input type="password" name="password" id="password" required><br><br>
            <input type="submit" value="Entrar">
            </form>
        `;
    } else if(tipo === 'registro'){ //VALIDANDO FORMATO
        div.innerHTML = `
            <h3>Registrarse</h3>
            <form action="/api/register.php" method="POST" onsubmit="return validarDni()">
            Nombre: <br><input type="text" name="nombre" required><br>
            Apellidos: <br><input type="text" name="apellidos" required><br>
            DNI (formato 11111111-X): <br><input type="text" name="dni" id="dni" maxlength="10" pattern="^\\d{8}-[A-Z]$" required
            title="Debe tener 8 números, un guion y una letra mayúscula, como 12345678-Z" placeholder="11111111-X"><br>
            Tlf: <br><input type="text" name="telefono" pattern="^\\d{9}$"  maxlength="9" required placeholder="123456789"><br>
            Fecha nacimiento: <br><input type="date" name="fecha_nacimiento" required><br>
            Email: <br><input type="email" name="email" required><br>
            Username: <br><input type="text" name="username" required><br>
            Contraseña: <br><input type="password" name="password" required><br><br>
            <input type="submit" value="Registrarse">
            </form>
        `;
        // Adjuntar validación de DNI al formulario dinámico
        document.getElementById('registro-form').addEventListener('submit', function(event) {
            if (!validarDni()) {
                event.preventDefault();
            }
        });    
    }
                    
}

// Escuchadores para reemplazar onclick="..."
document.addEventListener('DOMContentLoaded', function() {
    const loginButton = document.getElementById('login-button');
    const registerButton = document.getElementById('register-button');

    if (loginButton) {
        loginButton.addEventListener('click', () => mostrar('login'));
    }
    if (registerButton) {
        registerButton.addEventListener('click', () => mostrar('registro'));
    }
});
