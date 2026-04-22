<?php
// =============================================================================
// Configuración — Generador de Escritos
// -----------------------------------------------------------------------------
// 1. Copia este archivo como `config.php` en el mismo directorio.
// 2. Rellena las credenciales de la BD y el TOKEN de acceso.
// 3. NUNCA subas `config.php` a un repositorio público.
// =============================================================================

return [

    // -------- Base de datos MySQL/MariaDB --------
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'gjabogad_escritos',   // nombre completo de la BD en cPanel
        'user'     => 'gjabogad_escritos',   // usuario MySQL
        'password' => 'R@bula04',
        'charset'  => 'utf8mb4',
    ],

    // -------- Token de acceso a la API --------
    // Genera uno largo y aleatorio, por ejemplo con:
    //   php -r "echo bin2hex(random_bytes(32));"
    // El cliente debe enviarlo en el header:
    //   Authorization: Bearer <TOKEN>
    'api_token' => 'vtA5Us5wzt4mGi3zrYWduAYpy97M5E4B',

    // -------- CORS --------
    // Orígenes permitidos. Si el HTML se sirve desde el mismo dominio,
    // no se necesita ninguno. Añade http://localhost:8000 u otros para desarrollo.
    'cors_allow_origins' => [
        // 'http://localhost:8000',
        // 'https://gorronojara.cl',
    ],

    // -------- Modo debug --------
    // true: muestra detalles de errores SQL en las respuestas (solo desarrollo).
    // false: respuestas genéricas "Error interno".
    'debug' => false,
];
