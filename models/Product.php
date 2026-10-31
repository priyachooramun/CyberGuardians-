<?php


declare(strict_types=1);

final class Product extends Model
{
   
    public const SORTS = [
        'newest'     => ['label' => 'Newest',             'sql' => 'p.created_at DESC, p.product_id DESC'],
        'price_asc'  => ['label' => 'Price: low to high', 'sql' => 'final_price ASC, p.name ASC'],
        'price_desc' => ['label' => 'Price: high to low', 'sql' => 'final_price DESC, p.name ASC'],
        'name_asc'   => ['label' => 'Name: A to Z',       'sql' => 'p.name ASC'],
        'popular'    => ['label' => 'Most popular',       'sql' => 'sold DESC, p.name ASC'],
    ];

    public const FILTERS = ['new', 'sale', 'bestsellers', 'featured'];

    private const SELECT = 'SELECT p.*,
            COALESCE(i.quantity, 0) AS stock,
            COALESCE(s.sold, 0) AS sold,
            CASE WHEN p.is_on_sale = 1 AND p.sale_price IS NOT NULL THEN p.sale_price ELSE p.price END AS final_price
        FROM product p
        LEFT JOIN inventory i ON i.product_id = p.product_id
        LEFT JOIN (SELECT product_id, SUM(quantity) AS sold FROM orderitem GROUP BY product_id) s
               ON s.product_id = p.product_id';

    
    public static function search(array $o = []): array
    {
        $where  = [];
        $params = [];

        if (empty($o['include_inactive'])) {
            $where[] = 'p.is_active = 1';
        }
        if (!empty($o['section']) && isset(SECTIONS[$o['section']])) {
            $where[]  = 'p.section = ?';
            $params[] = $o['section'];
        }
        if (!empty($o['category'])) {
            $where[]  = 'p.category = ?';
            $params[] = $o['category'];
        }
        if (!empty($o['q'])) {
            $where[]  = '(p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)';
            $like     = '%' . $o['q'] . '%';
            array_push($params, $like, $like, $like);
        }
        if (!empty($o['low_stock'])) {
            $where[]  = 'COALESCE(i.quantity, 0) <= ' . (int)LOW_STOCK_LEVEL;   // constant, cast to int
        }

        $sort = $o['sort'] ?? 'newest';
        switch ($o['filter'] ?? '') {
            case 'new':
                $where[]  = 'p.created_at >= ?';
                $params[] = date('Y-m-d H:i:s', strtotime('-30 days'));
                break;
            case 'sale':
                $where[] = 'p.is_on_sale = 1';
                break;
            case 'featured':
                $where[] = 'p.is_featured = 1';
                break;
            case 'bestsellers':
                $where[] = 'COALESCE(s.sold, 0) > 0';
                $sort    = $o['sort'] ?? 'popular';
                break;
        }

        $sql = self::SELECT;
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY ' . (self::SORTS[$sort]['sql'] ?? self::SORTS['newest']['sql']);

        if (!empty($o['limit'])) {
            $sql .= ' LIMIT ' . max(1, (int)$o['limit']);   // cast to int → safe
        }
        return self::all($sql, $params);
    }

    public static function find(int $id, bool $activeOnly = true): ?array
    {
        $sql = self::SELECT . ' WHERE p.product_id = ?' . ($activeOnly ? ' AND p.is_active = 1' : '');
        return self::one($sql, [$id]);
    }

    
    public static function findMany(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $rows  = self::all(self::SELECT . " WHERE p.product_id IN ({$marks}) AND p.is_active = 1", $ids);
        return array_column($rows, null, 'product_id');
    }

    public static function recommended(array $product, int $limit = 3): array
    {
        $limit = max(1, $limit);
        return self::all(
            self::SELECT . " WHERE p.is_active = 1 AND p.product_id <> ? AND p.section = ?
              ORDER BY CASE WHEN p.category = ? THEN 0 ELSE 1 END, sold DESC, p.product_id DESC LIMIT {$limit}",
            [$product['product_id'], $product['section'], $product['category']]
        );
    }

   
    public static function create(array $data): int
    {
        self::run(
            'INSERT INTO product (name, description, price, section, category, brand, image, is_on_sale, sale_price, is_featured, is_active, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)',
            [
                $data['name'], $data['description'], $data['price'], $data['section'], $data['category'],
                $data['brand'], $data['image'], $data['is_on_sale'], $data['sale_price'], $data['is_featured'],
                date('Y-m-d H:i:s'),
            ]
        );
        return self::lastId();
    }

    public static function update(int $id, array $data): void
    {
        self::run(
            'UPDATE product SET name = ?, description = ?, price = ?, section = ?, category = ?, brand = ?,
                    image = ?, is_on_sale = ?, sale_price = ?, is_featured = ?
             WHERE product_id = ?',
            [
                $data['name'], $data['description'], $data['price'], $data['section'], $data['category'],
                $data['brand'], $data['image'], $data['is_on_sale'], $data['sale_price'], $data['is_featured'], $id,
            ]
        );
    }

    
    public static function remove(int $id): string
    {
        $ordered = (int)self::value('SELECT COUNT(*) FROM orderitem WHERE product_id = ?', [$id]);
        if ($ordered > 0) {
            self::run('UPDATE product SET is_active = 0 WHERE product_id = ?', [$id]);
            return 'hidden';
        }
        Database::transaction(static function () use ($id): void {
            self::run('DELETE FROM productvariant WHERE product_id = ?', [$id]);
            self::run('DELETE FROM inventory WHERE product_id = ?', [$id]);
            self::run('DELETE FROM product WHERE product_id = ?', [$id]);
        });
        return 'deleted';
    }

    public static function restore(int $id): void
    {
        self::run('UPDATE product SET is_active = 1 WHERE product_id = ?', [$id]);
    }

    public static function countActive(): int
    {
        return (int)self::value('SELECT COUNT(*) FROM product WHERE is_active = 1');
    }

    public static function countLowStock(): int
    {
        return (int)self::value(
            'SELECT COUNT(*) FROM product p LEFT JOIN inventory i ON i.product_id = p.product_id
             WHERE p.is_active = 1 AND COALESCE(i.quantity, 0) <= ' . (int)LOW_STOCK_LEVEL
        );
    }
}
