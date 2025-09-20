<?php

declare(strict_types=1);

namespace App\Repository;

use App\Env;

abstract class AbstractDatabase
{
    private ?\PDO $pdo = null;
    private string|false $lastInsertId = false;
    private int $affectedRows = 0;

    private ?\PDOStatement $pDOStatement = null;

    public function __construct()
    {
        $this->initConnection();
        $this->createTables();
    }

    protected function sql(string $sql, array $params = []): static
    {
        $this->lastInsertId = false;
        $this->affectedRows = 0;

        $stm = $this->pdo->prepare($sql);
        if (!$stm || !$stm->execute($params)) {
            $errorInfo = $this->pdo->errorInfo();
            throw new \Exception("Failed to prepare SQL statement: {$errorInfo[2]}");
        }

        $this->lastInsertId = $this->pdo->lastInsertId();
        $this->affectedRows = $stm->rowCount();

        $this->pDOStatement = $stm;

        return $this;
    }

    protected function isAffected(): bool
    {
        return $this->getAffectedRows() > 0;
    }

    protected function getLastInsertId(): string|false
    {
        return $this->lastInsertId;
    }

    protected function getAffectedRows(): int
    {
        return $this->affectedRows;
    }

    protected function fetchAll(): array
    {
        return $this->pDOStatement->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function fetchOne(): mixed
    {
        return $this->pDOStatement->fetch(\PDO::FETCH_ASSOC);
    }

    private function initConnection(): void
    {
        try {
            $this->pdo = new \PDO(Env::getDsn(), Env::get('DB_USER'), Env::get('DB_PASSWORD'));
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $ex) {
            if (Env::isDev()) {
                die($ex->getMessage());
            }

            die();
        }
    }

    //TODO: init tables in public.php
    private function createTables(): void
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
