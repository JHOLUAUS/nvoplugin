// Función para actualizar el precio total de los materiales
function actualizarMaterial() {
    let totalMaterial = 0;
    // Sumar el precio de los tratamientos seleccionados
    const materiales = document.querySelectorAll('input[name="material[]"]:checked');
    materiales.forEach(function(material) {
        const precioMaterial = material.getAttribute('data-precio');
        if (precioMaterial) {
            totalMaterial += parseFloat(precioMaterial);
        }
    });

    // Actualizar el campo de precio de los tratamientos
    document.getElementById('matPrecio').value = totalMaterial.toFixed(2);
    return totalMaterial;
}
// Función para actualizar el precio total de los tratamientos
function actualizarTratamientos() {
    let totalTratamientos = 0;
    // Sumar el precio de los tratamientos seleccionados
    const tratamientos = document.querySelectorAll('input[name="tratamiento[]"]:checked');
    tratamientos.forEach(function(tratamiento) {
        const precioTratamiento = tratamiento.getAttribute('data-precio');
        if (precioTratamiento) {
            totalTratamientos += parseFloat(precioTratamiento);
        }
    });
    // Actualizar el campo de precio de los tratamientos
    document.getElementById('tratPrecio').value = totalTratamientos.toFixed(2);
    // Retornar el total de tratamientos para poder usarlo en la función actualizarTotal
    return totalTratamientos;
}
// Función para actualizar el precio total de los tratamientos
function actualizarBifocal() {
    let totalBifocal = 0;
    // Sumar el precio de los tratamientos seleccionados
    const bifocal = document.querySelectorAll('input[name="bifocal[]"]:checked');
    bifocal.forEach(function(bifocal) {
        const precioBifocal = bifocal.getAttribute('data-precio');
        if (precioBifocal) {
            totalBifocal += parseFloat(precioBifocal);
        }
    });
    // Actualizar el campo de precio de los tratamientos
    document.getElementById('biPrecio').value = totalBifocal.toFixed(2);
    // Retornar el total de tratamientos para poder usarlo en la función actualizarTotal
    return totalBifocal;
}

// Función para actualizar el precio total de los tratamientos
function actualizarPromo() {
    let totalPromo = 0;
    // Sumar el precio de los tratamientos seleccionados
    const promo = document.querySelectorAll('input[name="promo[]"]:checked');
    promo.forEach(function(promo) {
        const precioPromo = promo.getAttribute('data-precio');
        if (precioPromo) {
            totalPromo += parseFloat(precioPromo);
        }
    });

    // Retornar el total de tratamientos para poder usarlo en la función actualizarTotal
    return totalPromo;
}





// Función para actualizar el total general (paquete + tratamientos)
function actualizarTotal() {
    let total = 0;
    // Obtener el precio del paquete Sel
    const paquete = document.getElementById('paquetesA');
    const precioPaquete = paquete.options[paquete.selectedIndex].getAttribute('data-precio');
    if (precioPaquete) {
        total += parseFloat(precioPaquete);
    }
     // Sumartotal de los tratamientos
    total += actualizarMaterial();
    total += actualizarTratamientos();
    total += actualizarBifocal();
    total -= actualizarPromo();
    // Actualizar el campo de total general
    document.getElementById('total').value = total.toFixed(2);
}
// Acá escucho cambios en el combobox de paquetes
document.getElementById('paquetesA').addEventListener('change', actualizarTotal);
// Acá Escucho cambios en los checkbox de tratamiento
const tratamientosCheckboxes = document.querySelectorAll('input[name="tratamiento[]"]');
tratamientosCheckboxes.forEach(function(checkbox) {
    checkbox.addEventListener('change', actualizarTotal);
});
// Acá Escucho cambios en los checkboxes de material
const materialesCheckboxes = document.querySelectorAll('input[name="material[]"]');
materialesCheckboxes.forEach(function(checkbox) {
    checkbox.addEventListener('change', actualizarTotal);
});
// Acá Escucho cambios en los checkboxes de material
const bifocalCheckboxes = document.querySelectorAll('input[name="bifocal[]"]');
bifocalCheckboxes.forEach(function(checkbox) {
    checkbox.addEventListener('change', actualizarTotal);
});
// Acá Escucho cambios en los checkboxes de promo
const promoCheckboxes = document.querySelectorAll('input[name="promo[]"]');
promoCheckboxes.forEach(function(checkbox) {
    checkbox.addEventListener('change', actualizarTotal);
});
actualizarTotal();
