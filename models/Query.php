<?php


declare(strict_types=1);

final class Query extends Model
{
    public static function create(int $customerId, string $subject, string $text): int
    {
        self::run(
            'INSERT INTO `query` (customer_id, subject, query_text, query_date, status) VALUES (?, ?, ?, ?, ?)',
            [$customerId, $subject, $text, date('Y-m-d'), 'Open']
        );
        return self::lastId();
    }

    public static function forCustomer(int $customerId): array
    {
        return self::all('SELECT * FROM `query` WHERE customer_id = ? ORDER BY query_id DESC', [$customerId]);
    }

    public static function recent(int $limit = 5): array
    {
        $limit = max(1, $limit);
        return self::all(
            "SELECT q.query_id, q.subject, q.query_date, q.status, c.name AS customer_name
             FROM `query` q JOIN customer c ON c.customer_id = q.customer_id
             ORDER BY q.query_id DESC LIMIT {$limit}"
        );
    }

    public const STATUSES = ['Open', 'Answered'];

   
    public static function list(string $status = ''): array
    {
        $sql    = 'SELECT q.*, c.name AS customer_name, c.email AS customer_email
                   FROM `query` q JOIN customer c ON c.customer_id = q.customer_id';
        $params = [];
        if (in_array($status, self::STATUSES, true)) {
            $sql     .= ' WHERE q.status = ?';
            $params[] = $status;
        }
        return self::all($sql . ' ORDER BY q.query_id DESC', $params);
    }

    public static function find(int $id): ?array
    {
        return self::one('SELECT * FROM `query` WHERE query_id = ?', [$id]);
    }

    
    public static function reply(int $id, string $reply): void
    {
        self::run(
            "UPDATE `query` SET reply = ?, reply_date = ?, status = 'Answered' WHERE query_id = ?",
            [$reply, date('Y-m-d'), $id]
        );
    }

    public static function countOpen(): int
    {
        return (int)self::value("SELECT COUNT(*) FROM `query` WHERE status = 'Open'");
    }
}
