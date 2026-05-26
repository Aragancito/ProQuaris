const USUARIO_PRUEBA = "juan.tamayo@proquaris.com";
const CONTRASENA_PRUEBA = "sena2026";

// 1. Si el usuario está en la pantalla de LOGIN
const formularioLogin = document.getElementById('formulario-login');
if (formularioLogin) {
    formularioLogin.addEventListener('submit', function(event) {
        event.preventDefault();
        const correoIngresado = document.getElementById('correo').value.trim();
        const contrasenaIngresada = document.getElementById('contrasena').value;

        if (correoIngresado === USUARIO_PRUEBA && contrasenaIngresada === CONTRASENA_PRUEBA) {
            alert("¡Inicio de sesión exitoso! Bienvenido a ProQuaris.");
            window.location.href = "dashboard.html";
        } else {
            alert("Error: Correo o contraseña incorrectos.\n\nPrueba con:\nUser: juan.tamayo@proquaris.com\nPass: sena2026");
        }
    });
}

// 2. Si el usuario está en la pantalla de RECUPERAR
const formularioRecuperar = document.getElementById('formulario-recuperar');
if (formularioRecuperar) {
    formularioRecuperar.addEventListener('submit', function(event) {
        event.preventDefault();
        const correoRecuperar = document.getElementById('correo-recuperar').value.trim();

        if (correoRecuperar !== "") {
            alert(`Instrucciones enviadas al correo: ${correoRecuperar}`);
            window.location.href = "login.html";
        }
    });
}