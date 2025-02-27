// Variables globales
let map;
let cobradorMarker;
let lugarCobranzaMarker;

// Función para inicializar el mapa
function initMap(cobradorLat, cobradorLon) {
    // Crear el mapa centrado en la ubicación del cobrador
    map = L.map('map').setView([cobradorLat, cobradorLon], 13); // Cambiar el zoom a 13

    // Capa de OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    // Marcador para la ubicación del cobrador
    cobradorMarker = L.marker([cobradorLat, cobradorLon]).addTo(map)
        .bindPopup('Ubicación del Cobrador')
        .openPopup();
}

// Función para mostrar el mapa y trazar la ruta
function mostrarRuta() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
            const cobradorLat = position.coords.latitude;
            const cobradorLon = position.coords.longitude;

            // Mostrar el mapa
            document.getElementById('map').style.display = 'block';

            // Inicializa el mapa con la ubicación del cobrador
            initMap(cobradorLat, cobradorLon);

            // Obtener la dirección de cobranza
            const address = direccionCobranza; // Reemplaza con la dirección del lugar de cobranza
            console.log("Dirección de cobranza: " + address);

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        const lugarCobranzaLat = data[0].lat; // Latitud del lugar de cobranza
                        const lugarCobranzaLon = data[0].lon; // Longitud del lugar de cobranza

                        // Actualiza el marcador del lugar de cobranza
                        lugarCobranzaMarker = L.marker([lugarCobranzaLat, lugarCobranzaLon]).addTo(map)
                            .bindPopup('Lugar de Cobranza')
                            .openPopup();

                        // Trazar la ruta
                        L.Routing.control({
                            waypoints: [
                                L.latLng(cobradorLat, cobradorLon),
                                L.latLng(lugarCobranzaLat, lugarCobranzaLon)
                            ],
                            routeWhileDragging: true
                        }).addTo(map);

                        // Iniciar el seguimiento de la ubicación en tiempo real
                        navigator.geolocation.watchPosition((position) => {
                            const newCobradorLat = position.coords.latitude;
                            const newCobradorLon = position.coords.longitude;

                            // Actualiza el marcador del cobrador
                            cobradorMarker.setLatLng([newCobradorLat, newCobradorLon]);
                            map.setView([newCobradorLat, newCobradorLon]); // Centrar el mapa en la nueva posición
                        }, (error) => {
                            console.error('Error al obtener la ubicación: ', error);
                            alert(`Error al obtener la ubicación: ${error.message}`);
                        });
                    } else {
                        alert('No se pudo encontrar la dirección de cobranza.');
                    }
                })
                .catch(error => console.error('Error al obtener las coordenadas: ', error));
        }, (error) => {
            console.error('Error al obtener la ubicación: ', error);
            alert(`Error al obtener la ubicación: ${error.message}`);
        });
    } else {
        alert("La geolocalización no es soportada por este navegador.");
    }
}


document.getElementById('btnTrazarRuta').onclick = mostrarRuta;
