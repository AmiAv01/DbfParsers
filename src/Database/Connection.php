<?php

namespace App\Database;

use App\src\Exceptions\DatabaseException;

class Connection
{
    private \mysqli $connection;

    public function __construct(array $config)
    {
        $this->connection = new \mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database']
        );

        if ($this->connection->connect_error) {
            throw new DatabaseException(
                "Database connection failed: " . $this->connection->connect_error
            );
        }

        $this->connection->set_charset($config['charset'] ?? 'utf8');
    }

    public function beginTransaction(): void
    {
        $this->connection->begin_transaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollback(): void
    {
        $this->connection->rollback();
    }

    public function query(string $sql): \mysqli_result|bool
    {
        $result = $this->connection->query($sql);

        if (!$result) {
            throw new DatabaseException(
                "SQL error: " . $this->connection->error . "\nQuery: " . $sql
            );
        }
        return $result;
    }

    public function escape(string $value): string
    {
        return $this->connection->real_escape_string($value);
    }

    public function close(): void
    {
        $this->connection->close();
    }
}
