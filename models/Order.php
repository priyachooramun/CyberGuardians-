<?php


declare(strict_types=1);

final class Order extends Model
{
    public const STATUSES = ['Pending', 'Paid', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

    public static function create(int $customerId, float $total, string $shippingAddress, string $status = 'Pending'): int
    {
        self::run(
            'INSERT INTO orders (customer_id, order_date, status, total_amount, shipping_address, created_at) VALUES (?, ?, ?, ?, ?, ?)',
            [$customerId, date('Y-m-d'), $status, $total, $shippingAddress, date('Y-m-d H:i:s')]
        );
        return self::lastId();
    }

    public static function find(int $orderId): ?array
    {
        return self::one(
            'SELECT o.*, c.name AS customer_name, c.email AS customer_email, c.phone AS customer_phone
             FROM orders o JOIN customer c ON c.customer_id = o.customer_id WHERE o.order_id = ?',
            [$orderId]
        );
    }

    public static function markPaid(int $orderId): void
    {
        self::run("UPDATE orders SET status = 'Paid' WHERE order_id = ?", [$orderId]);
    }

  
    public static function findForCustomer(int $orderId, int $customerId): ?array
    {
        return self::one('SELECT * FROM orders WHERE order_id = ? AND customer_id = ?', [$orderId, $customerId]);
    }

    public static function forCustomer(int $customerId): array
    {
        return self::all(
            'SELECT o.*, (SELECT SUM(quantity) FROM orderitem WHERE order_id = o.order_id) AS item_count
             FROM orders o WHERE o.customer_id = ? ORDER BY o.order_id DESC',
            [$customerId]
        );
    }

    public static function list(string $status = '', string $search = ''): array
    {
        $sql = 'SELECT o.*, c.name AS customer_name, c.email AS customer_email
                FROM orders o JOIN customer c ON c.customer_id = o.customer_id WHERE 1 = 1';
        $params = [];
        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            $sql .= ' AND o.status = ?';
            $params[] = $status;
        }
        if ($search !== '') {
            $sql .= ' AND (c.name LIKE ? OR c.email LIKE ? OR o.order_id = ?)';
            array_push($params, '%' . $search . '%', '%' . $search . '%', (int)$search);
        }
        return self::all($sql . ' ORDER BY o.order_id DESC', $params);
    }

   
    public static function updateStatus(int $orderId, string $status): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }
        $order = self::find($orderId);
        if (!$order || $order['status'] === 'Cancelled') {
            return false;
        }
        Database::transaction(static function () use ($orderId, $status, $order): void {
            self::run('UPDATE orders SET status = ? WHERE order_id = ?', [$status, $orderId]);
            if ($status === 'Cancelled' && $order['status'] !== 'Cancelled') {
                foreach (OrderItem::forOrder($orderId) as $item) {
                    Inventory::increment((int)$item['product_id'], (int)$item['quantity']);
                }
                // Simulated refund of the simulated payment
                self::run("UPDATE payment SET status = 'Refunded' WHERE order_id = ? AND status = 'Success'", [$orderId]);
            }
        });
        return true;
    }

    public static function recent(int $limit = 5): array
    {
        $limit = max(1, $limit);
        return self::all(
            "SELECT o.order_id, o.order_date, o.status, o.total_amount, c.name AS customer_name
             FROM orders o JOIN customer c ON c.customer_id = o.customer_id
             ORDER BY o.order_id DESC LIMIT {$limit}"
        );
    }

    public static function totalSales(): float
    {
        return (float)self::value("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status <> 'Cancelled'");
    }

    public static function countByStatus(string $status): int
    {
        return (int)self::value('SELECT COUNT(*) FROM orders WHERE status = ?', [$status]);
    }

    
    public static function salesByDay(int $days = 7): array
    {
        $from = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $rows = self::all(
            "SELECT order_date, SUM(total_amount) AS total FROM orders
             WHERE status <> 'Cancelled' AND order_date >= ? GROUP BY order_date",
            [$from]
        );
        $byDate = array_column($rows, 'total', 'order_date');
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day          = date('Y-m-d', strtotime("-{$i} days"));
            $result[$day] = (float)($byDate[$day] ?? 0);
        }
        return $result;
    }
}
