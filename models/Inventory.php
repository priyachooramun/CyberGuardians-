<?php


declare(strict_types=1);

final class Inventory extends Model
{
    public static function quantity(int $productId): int
    {
        return (int)(self::value('SELECT quantity FROM inventory WHERE product_id = ?', [$productId]) ?: 0);
    }

   
    public static function set(int $productId, int $quantity): void
    {
        $quantity = max(0, $quantity);
        $exists = (int)self::value('SELECT COUNT(*) FROM inventory WHERE product_id = ?', [$productId]);
        if ($exists) {
            self::run('UPDATE inventory SET quantity = ? WHERE product_id = ?', [$quantity, $productId]);
        } else {
            self::run('INSERT INTO inventory (product_id, quantity) VALUES (?, ?)', [$productId, $quantity]);
        }
    }

    
    public static function decrement(int $productId, int $quantity): bool
    {
        $stmt = self::run(
            'UPDATE inventory SET quantity = quantity - ? WHERE product_id = ? AND quantity >= ?',
            [$quantity, $productId, $quantity]
        );
        return $stmt->rowCount() === 1;
    }

    public static function increment(int $productId, int $quantity): void
    {
        self::run('UPDATE inventory SET quantity = quantity + ? WHERE product_id = ?', [$quantity, $productId]);
    }
}
