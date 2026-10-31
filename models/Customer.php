<?php


declare(strict_types=1);

final class Customer extends Model
{
    public static function find(int $id): ?array
    {
        return self::one('SELECT customer_id, name, email, phone, address, created_at FROM customer WHERE customer_id = ?', [$id]);
    }

    public static function findByEmailWithHash(string $email): ?array
    {
        return self::one('SELECT * FROM customer WHERE email = ?', [$email]);
    }

    public static function emailExists(string $email, int $exceptId = 0): bool
    {
        return (bool)self::value('SELECT COUNT(*) FROM customer WHERE email = ? AND customer_id <> ?', [$email, $exceptId]);
    }

    public static function create(string $name, string $email, string $phone, string $address, string $passwordHash): int
    {
        self::run(
            'INSERT INTO customer (name, email, phone, address, password_hash, created_at) VALUES (?, ?, ?, ?, ?, ?)',
            [$name, $email, $phone, $address, $passwordHash, date('Y-m-d H:i:s')]
        );
        return self::lastId();
    }

    public static function updateProfile(int $id, string $name, string $phone, string $address): void
    {
        self::run('UPDATE customer SET name = ?, phone = ?, address = ? WHERE customer_id = ?', [$name, $phone, $address, $id]);
    }

    public static function passwordHash(int $id): ?string
    {
        $hash = self::value('SELECT password_hash FROM customer WHERE customer_id = ?', [$id]);
        return $hash === false ? null : $hash;
    }

    public static function updatePassword(int $id, string $passwordHash): void
    {
        self::run('UPDATE customer SET password_hash = ? WHERE customer_id = ?', [$passwordHash, $id]);
    }

    public static function allWithStats(string $search = ''): array
    {
        $sql = 'SELECT c.customer_id, c.name, c.email, c.phone, c.address, c.created_at,
                       COUNT(o.order_id) AS order_count, COALESCE(SUM(o.total_amount), 0) AS total_spent
                FROM customer c
                LEFT JOIN orders o ON o.customer_id = c.customer_id AND o.status <> \'Cancelled\'';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE c.name LIKE ? OR c.email LIKE ?';
            $params = ['%' . $search . '%', '%' . $search . '%'];
        }
        $sql .= ' GROUP BY c.customer_id, c.name, c.email, c.phone, c.address, c.created_at ORDER BY c.customer_id DESC';
        return self::all($sql, $params);
    }

    public static function count(): int
    {
        return (int)self::value('SELECT COUNT(*) FROM customer');
    }

    public static function recent(int $limit = 5): array
    {
        $limit = max(1, $limit);
        return self::all("SELECT customer_id, name, email, created_at FROM customer ORDER BY customer_id DESC LIMIT {$limit}");
    }
}
