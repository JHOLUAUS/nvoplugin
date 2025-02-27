// Función para cargar datos del CP y actualizar los campos
function cargarDatosDireccion(cpInputId, asentamientoSelectId, tipoAsentInputId, municipioInputId, estadoInputId) {
    var cp = document.getElementById(cpInputId).value;
    
    // Petición AJAX para obtener los datos del código postal
    fetch('../funciones/obtener_datos_delocacion.php?cp=' + cp)
    .then(response => response.json())
    .then(data => {
        // Limpiar el select de asentamientos
        var asentamientoSelect = document.getElementById(asentamientoSelectId);
        asentamientoSelect.innerHTML = '<option value="">Seleccione</option>';
        
        // Llenar el select con los asentamientos
        data.asentamientos.forEach(function(asentamiento) {
            var option = document.createElement('option');
            option.value = asentamiento;
            option.textContent = asentamiento;
            asentamientoSelect.appendChild(option);
        });
        
        // Actualizar los campos de tipo, municipio y estado
        document.getElementById(tipoAsentInputId).value = data.tipo;
        document.getElementById(municipioInputId).value = data.municipio;
        document.getElementById(estadoInputId).value = data.estado;
    })
    .catch(error => console.error('Error:', error));
}

// Función para la zona de cobranza
function cargarDatosCobranza(cpInputId, asentamientoSelectId, tipoAsentInputId, municipioInputId, estadoInputId) {
    var cp = document.getElementById(cpInputId).value;
    
    // Petición AJAX para obtener los datos del código postal
    fetch('../funciones/obtener_datos_delocacion.php?cp=' + cp)
    .then(response => response.json())
    .then(data => {
        // Limpiar el select de asentamientos
        var asentamientoSelect = document.getElementById(asentamientoSelectId);
        asentamientoSelect.innerHTML = '<option value="">Seleccione</option>';
        
        // Llenar el select con los asentamientos
        data.asentamientos.forEach(function(asentamiento) {
            var option = document.createElement('option');
            option.value = asentamiento;
            option.textContent = asentamiento;
            asentamientoSelect.appendChild(option);
        });
        
        // Actualizar los campos de tipo, municipio, estado y zona
        document.getElementById(tipoAsentInputId).value = data.tipo;
        document.getElementById(municipioInputId).value = data.municipio;
        document.getElementById(estadoInputId).value = data.estado;
    })
    .catch(error => console.error('Error:', error));
}
