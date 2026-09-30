<?php

declare(strict_types=1);

namespace App;

use PDO;

final class Database
{
    public static function connect(): PDO
    {
        $host = getenv('DB_HOST') ?: 'database';
        $port = getenv('DB_PORT') ?: '3306';
        $database = getenv('DB_DATABASE') ?: 'vehicle_api';
        $username = getenv('DB_USERNAME') ?: 'vehicle_user';
        $password = getenv('DB_PASSWORD') ?: 'secret';

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
