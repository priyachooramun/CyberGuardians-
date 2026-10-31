<?php


declare(strict_types=1);

final class OrderItem extends Model
{
    
    public static function add(int $orderId, int $productId, ?int $variantId, int $quantity, float $price): void
    {
        self::run(
            'INSERT INTO orderitem (order_id, product_id, variant_id, quantity, price) VALUES (?, ?, ?, ?, ?)',
            [$orderId, $productId, $variantId, $quantity, $price]
        );
    }

    public static function forOrder(int $orderId): array
    {
        return self::all(
            'SELECT oi.*, p.name AS product_name, p.image, v.variant_name
             FROM orderitem oi
             JOIN product p ON p.product_id = oi.product_id
             LEFT JOIN productvariant v ON v.variant_id = oi.variant_id
             WHERE oi.order_id = ? ORDER BY oi.order_item_id',
            [$orderId]
        );
    }
}
