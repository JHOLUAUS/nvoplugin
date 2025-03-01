<?php
// Solo modificar configuración si la sesión no ha iniciado
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_domain', '192.168.1.32'); // Mueve esto dentro del bloque condicional
    session_start();
}
/*
ini_set('session.cookie_domain', '.nuevavistaoptica.com');
session_start();
*/
?>