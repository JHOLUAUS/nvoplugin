<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f0f0f0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    text-align: center;
}

.error-container {
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    padding: 40px;
    width: 300px;
    animation: fadeIn 1s ease;
}

.error-message {
    font-size: 32px;
    color: #e74c3c;
    animation: bounce 1s infinite;
}

.error-instruction {
    margin: 20px 0;
    font-size: 18px;
    color: #333;
}

.back-button {
    display: inline-block;
    padding: 10px 20px;
    background-color: #3498db;
    color: #ffffff;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s;
}

.back-button:hover {
    background-color: #2980b9;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-20px);
    }
    60% {
        transform: translateY(-10px);
    }
}

    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-message">¡Ups, algo salió mal!</h1>
        <p class="error-instruction">TOKEN INVÁLIDO - Por favor Recarga la Página</p>
        <a href="javascript:history.back()" class="back-button">Regresar</a>
    </div>
</body>
</html>
