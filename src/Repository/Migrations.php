<?php

declare(strict_types=1);

namespace App\Repository;

final class Migrations extends AbstractDatabase
{
    public function createTables(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) NULL,
                email VARCHAR(100) NULL,
                password VARCHAR(255) NULL,

                UNIQUE KEY (email)
            )
        ";

        $this->sql($sql);
    }
}
