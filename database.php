<?php


declare(strict_types=1);

final class Database
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            $dsn = DB_DSN !== ''
                ? DB_DSN
                : sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);

            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // Never show connection details (user, host) to visitors.
                error_log('DB connection failed: ' . $e->getMessage());
                http_response_code(500);
                exit(APP_DEBUG
                    ? 'Database connection failed: ' . htmlspecialchars($e->getMessage())
                    : 'Sorry, the shop is temporarily unavailable.');
            }
        }
        return self::$pdo;
    }

   
    public static function transaction(callable $work)
    {
        $pdo = self::get();
        $pdo->beginTransaction();
        try {
            $result = $work($pdo);
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
