<?php

declare(strict_types=1);

namespace App;

final class Env
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }

    public static function getDsn(): string
    {
        $dsn = self::get('DB_DRIVER');
        $dsn .= ':host=' . self::get('DB_HOST');
        $dsn .= ';port=' . self::get('DB_PORT');
        $dsn .= ';dbname=' . self::get('DB_NAME');

        return $dsn;
    }

    public static function isDev(): bool
    {
        return self::get('APP_ENV') === 'dev';
    }
}
