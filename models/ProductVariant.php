<?php

declare(strict_types=1);

final class ProductVariant extends Model
{
    public static function forProduct(int $productId): array
    {
        return self::all('SELECT * FROM productvariant WHERE product_id = ? ORDER BY variant_id', [$productId]);
    }

    public static function findForProduct(int $variantId, int $productId): ?array
    {
        return self::one('SELECT * FROM productvariant WHERE variant_id = ? AND product_id = ?', [$variantId, $productId]);
    }

    public static function productIds(): array
    {
        return array_map('intval', array_column(self::all('SELECT DISTINCT product_id FROM productvariant'), 'product_id'));
    }

    
    public static function names(array $ids): array
    {
        $ids = array_values(array_filter(array_unique(array_map('intval', $ids))));
        if (!$ids) {
            return [];
        }
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $rows  = self::all("SELECT variant_id, variant_name FROM productvariant WHERE variant_id IN ({$marks})", $ids);
        return array_column($rows, 'variant_name', 'variant_id');
    }

    
    public static function sync(int $productId, array $names): void
    {
        $names    = array_values(array_unique(array_filter(array_map('trim', $names), 'strlen')));
        $existing = self::forProduct($productId);

        foreach ($existing as $variant) {
            if (!in_array($variant['variant_name'], $names, true)) {
                self::run('DELETE FROM productvariant WHERE variant_id = ?', [$variant['variant_id']]);
            }
        }
        $kept = array_column($existing, 'variant_name');
        foreach ($names as $name) {
            if (!in_array($name, $kept, true)) {
                self::run('INSERT INTO productvariant (product_id, variant_name, stock) VALUES (?, ?, 0)', [$productId, $name]);
            }
        }
    }
}
