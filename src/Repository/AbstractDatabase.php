<?php

declare(strict_types=1);

namespace App\Repository;

abstract class AbstractDatabase
{
    private ?\PDO $pdo = null;
    private string|false $lastInsertId = false;
    private int $affectedRows = 0;

    private ?\PDOStatement $pDOStatement = null;

    public function __construct()
    {
        $this->initConnection();
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
        return $this->pDOStatement->fetchAll();
    }

    protected function fetchOne(): ?array
    {
        $result = $this->pDOStatement->fetch();

        return is_array($result) ? $result : null;
    }

    private function initConnection(): void
    {
        try {
            $dsn = sprintf("%s:host=%s;port=%s;dbname=%s", $_ENV['DB_DRIVER'], $_ENV['DB_HOST'], $_ENV['DB_PORT'], $_ENV['DB_NAME']);
            $this->pdo = new \PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);

            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        } catch (\PDOException $ex) {
            if ($_ENV['APP_ENV'] === 'dev') {
                die($ex->getMessage());
            }

            die();
        }
    }
}
