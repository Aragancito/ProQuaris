// 1. Capturar el formulario de recuperación desde el HTML
const formularioRecuperar = document.getElementById('formulario-recuperar');

// 2. Escuchar el envío del formulario
formularioRecuperar.addEventListener('submit', function(event) {
    // Evita que la página se recargue
    event.preventDefault();

    // 3. Obtener el correo ingresado
    const correoRecuperar = document.getElementById('correo-recuperar').value.trim();

    // 4. Simular el proceso de envío de instrucciones
    if (correoRecuperar !== "") {
        alert(`Se han enviado las instrucciones de recuperación al correo: ${correoRecuperar}.\n\nPor favor, revisa tu bandeja de entrada.`);
        
        // Redirigir automáticamente al login para que el usuario intente ingresar de nuevo
        window.location.href = "login.html";
    }
});