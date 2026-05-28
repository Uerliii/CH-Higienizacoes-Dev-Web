<?php

/**
 * CH Higienizações — Front Controller
 * Ponto de entrada único da aplicação PHP/MVC
 */

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/backend');
define('VIEWS_PATH', BASE_PATH . '/resources/views');
define('CONFIG_PATH', BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');

// Carrega arquivo .env customizado (Sem Composer)
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        }
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Autoloader simples (sem Composer, para máxima compatibilidade)
spl_autoload_register(function (string $class): void {
    $map = [
        'Controllers\\' => APP_PATH . '/Controllers/',
        'Services\\'    => APP_PATH . '/Services/',
        'Models\\'      => APP_PATH . '/Models/',
        'Middleware\\'  => APP_PATH . '/Middleware/',
    ];

    foreach ($map as $namespace => $dir) {
        if (str_starts_with($class, $namespace)) {
            $file = $dir . str_replace('\\', '/', substr($class, strlen($namespace))) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// Carrega config e helpers
require_once CONFIG_PATH . '/app.php';
require_once BASE_PATH . '/backend/helpers.php';

// Inicia sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Roteamento
require_once BASE_PATH . '/routes/web.php';
