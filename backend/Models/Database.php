<?php

namespace Models;

use PDO;
use PDOException;

/**
 * Database — Singleton PDO Connection para o MySQL da Hostinger.
 */
class Database
{
    private static ?PDO $instance = null;

    private static function init(): void
    {
        if (self::$instance === null) {
            try {
                // Carrega .env se as variáveis não estiverem no ambiente (vital para painel admin standalone)
                $envFile = dirname(__DIR__, 2) . '/.env';
                if (!getenv('DB_HOST') && file_exists($envFile)) {
                    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($lines as $line) {
                        if (str_starts_with(trim($line), '#')) continue;
                        $parts = explode('=', $line, 2);
                        if (count($parts) === 2) {
                            $name = trim($parts[0]);
                            $value = trim($parts[1]);
                            if (preg_match('/^"(.*)"$/', $value, $matches)) $value = $matches[1];
                            putenv(sprintf('%s=%s', $name, $value));
                            $_ENV[$name] = $value;
                        }
                    }
                }

                // Conexão MySQL
                $host = getenv('DB_HOST') ?: '127.0.0.1';
                $dbname = getenv('DB_DATABASE') ?: 'test';
                $user = getenv('DB_USERNAME') ?: 'root';
                $pass = getenv('DB_PASSWORD') ?: '';

                self::$instance = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );

            } catch (PDOException $e) {
                die("Erro fatal de conexão com o banco de dados: " . $e->getMessage());
            }
        }
    }

    public static function connection(): PDO
    {
        self::init();
        return self::$instance;
    }
}
