<?php
// views/404.php

http_response_code(404);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada</title>
    <link rel="stylesheet" href="/storage-app/public/css/auth/home.css"> <!-- Reutiliza tu estilo base -->
    <style>
        .error-container {
            text-align: center;
            padding: 50px 20px;
            font-family: Arial, sans-serif;
        }
        .error-code { font-size: 7rem; color: #dc3545; margin: 0; }
        .error-message { font-size: 1.5rem; color: #6c757d; }
        .btn-home { 
            display: inline-block; 
            margin-top: 20px; 
            padding: 10px 20px; 
            background: #007bff; 
            color: #fff; 
            text-decoration: none; 
            border-radius: 5px; 
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">404</h1>
        <p class="error-message">¡Ups! La página que buscas no existe o ha sido movida.</p>
        <a href="/storage-app/public/" class="btn-home">Volver al inicio</a>
    </div>
</body>
</html>