<?php

declare(strict_types=1);

namespace App\Repository;

final class UserRepository extends AbstractDatabase
{
    public function getUserByEmail(string $email): ?array
    {
        $user = $this->sql("
            SELECT * FROM users WHERE email = (:email)
        ", [
            'email' => $email
        ]);

        return $user->fetchOne();
    }

    public function createUser(array $user): bool
    {
        $this->sql("
            INSERT INTO users (username, email, password) 
            VALUES 
            ((:username), (:email), (:password))
        ", $user);

        return $this->isAffected();
    }
}
