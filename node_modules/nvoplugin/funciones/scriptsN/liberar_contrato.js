function liberarContrato() {
    const id_cliente = document.getElementById("id_cliente").value; // Asegúrate de que este campo ya existe o lo agregas para obtener el ID

    if(confirm("¿Estás seguro de liberar este contrato?")) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../funciones/liberar_contrato.php", true);
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
