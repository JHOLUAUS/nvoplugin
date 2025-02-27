function enviarObservacion() {
    // Obtener el valor de la observación
    const observacion = document.getElementById('observaciones').value;

    // Verificar que la observación no esté vacía
    if (observacion.trim() === '') {
        alert("Por favor, ingrese una observación.");
        return;
    }

    // Enviar la observación al servidor mediante AJAX
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "../funciones/guardar_observacion_lab.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // Mostrar mensaje de éxito
                alert("Observación enviada con éxito.");
                // Limpiar el textarea después de enviar
            } else {
                // Manejar errores de la solicitud
                alert("Error al enviar la observación. Código de error: " + xhr.status);
            }
        }
    };

    // Manejo de errores de red
    xhr.onerror = function() {
        alert("Error al enviar la observación. Inténtalo de nuevo.");
    };

    // Enviar los datos (ID del contrato y observación)
    xhr.send("id_clienteContrato=" + idClienteContrato + "&observacion=" + encodeURIComponent(observacion));
}

// Agregar el evento de clic al botón
document.getElementById('btnEnviarObservacion').onclick = enviarObservacion;
