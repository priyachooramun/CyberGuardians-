<?php


declare(strict_types=1);

abstract class Model
{
    protected static function db(): PDO
    {
        return Database::get();
    }

    /** Prepare + execute in one call and return the statement. */
    protected static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected static function one(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    protected static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    protected static function value(string $sql, array $params = []): mixed
    {
        return self::run($sql, $params)->fetchColumn();
    }

    protected static function lastId(): int
    {
        return (int)self::db()->lastInsertId();
    }
}
