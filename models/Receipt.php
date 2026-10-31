<?php

declare(strict_types=1);

final class Receipt extends Model
{
    public static function create(int $paymentId): array
    {
        self::run('INSERT INTO receipt (payment_id, issue_date) VALUES (?, ?)', [$paymentId, date('Y-m-d')]);
        $id     = self::lastId();
        $number = sprintf('RCPT-%06d', $id);
        self::run('UPDATE receipt SET receipt_number = ? WHERE receipt_id = ?', [$number, $id]);
        return ['receipt_id' => $id, 'receipt_number' => $number];
    }

    public static function forOrder(int $orderId): ?array
    {
        return self::one(
            'SELECT r.*, p.amount, p.method, p.status AS payment_status, p.transaction_ref, p.card_last4, p.payment_date
             FROM receipt r JOIN payment p ON p.payment_id = r.payment_id
             WHERE p.order_id = ? ORDER BY r.receipt_id DESC LIMIT 1',
            [$orderId]
        );
    }
}
