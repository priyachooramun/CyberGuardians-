<?php

declare(strict_types=1);

final class Payment extends Model
{
    public static function create(int $orderId, float $amount, string $method, string $status, string $reference, ?string $last4): int
    {
        self::run(
            'INSERT INTO payment (order_id, amount, payment_date, method, status, transaction_ref, card_last4) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$orderId, $amount, date('Y-m-d'), $method, $status, $reference, $last4]
        );
        return self::lastId();
    }

    public static function forOrder(int $orderId): ?array
    {
        return self::one('SELECT * FROM payment WHERE order_id = ? ORDER BY payment_id DESC LIMIT 1', [$orderId]);
    }
}
