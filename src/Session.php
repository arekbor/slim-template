<?php

declare(strict_types=1);

namespace App;

final class Session
{
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroySession(): void
    {
        session_unset();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    public static function getCurrentUser(): ?array
    {
        $userId = self::get('user_id');
        if (!$userId) {
            return null;
        }

        return [
            'user_id' => $userId,
            'username' => self::get('username'),
            'email'    => self::get('email'),
        ];
    }

    public static function startSession(): void
    {
        //TODO: get settings from env
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 1800,
                'domain' => 'localhost',
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict',
            ]);

            if (!@session_start()) {
                throw new \RuntimeException('Failed to start session.');
            }
        }

        $regenerationInterval = 60 * 30;
        $lastRegeneration = self::get('last_regeneration', 0);
        if (time() - $lastRegeneration >= $regenerationInterval) {
            session_regenerate_id(true);
            self::set('last_regeneration', time());
        }
    }
}
