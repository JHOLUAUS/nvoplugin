function entregarLentes(id_cliente) {
    if (confirm("¿Estás seguro de que han sido entregados?")) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../funciones/entregar_lentes.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                alert(xhr.responseText); // Muestra el mensaje de éxito o error
                // Aquí puedes hacer algo adicional, como recargar la página
                location.reload();
            }
        };
        xhr.send("id_cliente=" + id_cliente);
    }
}
