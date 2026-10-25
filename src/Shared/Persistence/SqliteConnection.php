<?php

declare(strict_types=1);

namespace Gaby\Shared\Persistence;

use Gaby\Shared\Config\Env;
use PDO;

final class SqliteConnection
{
    /** @var PDO|null */
    private static $pdo = null;

    public static function create(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $path = Env::get('DB_PATH', 'storage/database.sqlite');

        if ($path === null || $path === '') {
            $path = 'storage/database.sqlite';
        }

        if (!self::isAbsolute($path)) {
            $path = Env::rootPath() . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
        }

        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $pdo = new PDO('sqlite:' . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');

        self::$pdo = $pdo;

        return $pdo;
    }

    private static function isAbsolute(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if ($path[0] === '/' || $path[0] === '\\') {
            return true;
        }

        return (bool) preg_match('#^[A-Za-z]:[\\\\/]#', $path);
    }
}
