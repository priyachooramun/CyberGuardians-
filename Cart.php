<?php
/**
 * Shopping cart stored in the session (FR9, FR10)
 * Owner: Member 4 (Cart, checkout & orders)
 *
 * Only product ids, variant ids and quantities are kept in the session.
 * Prices are ALWAYS read again from the database, so a user cannot change
 * the price they pay by tampering with a request.
 */

declare(strict_types=1);

final class Cart
{
    public const MAX_QTY_PER_LINE = 50;

    private static function &items(): array
    {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        return $_SESSION['cart'];
    }

    public static function key(int $productId, ?int $variantId): string
    {
        return $productId . '-' . (int)$variantId;
    }

    /** @return array{0: bool, 1: string} [success, message] */
    public function add(int $productId, ?int $variantId, int $quantity): array
    {
        if ($quantity < 1 || $quantity > self::MAX_QTY_PER_LINE) {
            return [false, 'Please choose a quantity between 1 and ' . self::MAX_QTY_PER_LINE . '.'];
        }
        $product = Product::find($productId);
        if (!$product) {
            return [false, 'This product is no longer available.'];
        }

        $variants = ProductVariant::forProduct($productId);
        if ($variants) {
            if (!$variantId) {
                return [false, 'Please choose an option (colour/edition) first.'];
            }
            if (!ProductVariant::findForProduct($variantId, $productId)) {
                return [false, 'That option is not available for this product.'];
            }
        } else {
            $variantId = null;
        }

        $stock = (int)$product['stock'];
        if ($stock < 1) {
            return [false, $product['name'] . ' is out of stock.'];
        }

        $items   = &self::items();
        $key     = self::key($productId, $variantId);
        $inCart  = $this->quantityOfProduct($productId);
        $allowed = min($quantity, $stock - $inCart, self::MAX_QTY_PER_LINE - (int)($items[$key]['qty'] ?? 0));
        if ($allowed < 1) {
            return [false, 'You already have all available stock of ' . $product['name'] . ' in your cart.'];
        }

        $items[$key] = [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'qty'        => (int)($items[$key]['qty'] ?? 0) + $allowed,
        ];

        $message = $allowed < $quantity
            ? "Only {$allowed} more could be added – that is all the stock we have."
            : $product['name'] . ' added to your cart.';
        return [true, $message];
    }

    /** Set a new quantity for one line (0 removes it). */
    public function update(string $key, int $quantity): array
    {
        $items = &self::items();
        if (!isset($items[$key])) {
            return [false, 'That item is not in your cart.'];
        }
        if ($quantity <= 0) {
            unset($items[$key]);
            return [true, 'Item removed.'];
        }
        $productId = (int)$items[$key]['product_id'];
        $stock     = Inventory::quantity($productId);
        $others    = $this->quantityOfProduct($productId) - (int)$items[$key]['qty'];
        $allowed   = max(0, min($quantity, $stock - $others, self::MAX_QTY_PER_LINE));
        if ($allowed === 0) {
            unset($items[$key]);
            return [false, 'This item is now out of stock and was removed.'];
        }
        $items[$key]['qty'] = $allowed;
        return [true, $allowed < $quantity ? "Only {$allowed} available – quantity adjusted." : 'Cart updated.'];
    }

    public function remove(string $key): void
    {
        $items = &self::items();
        unset($items[$key]);
    }

    public function clear(): void
    {
        $_SESSION['cart'] = [];
    }

    public function count(): int
    {
        return array_sum(array_map(static fn($i) => (int)$i['qty'], self::items()));
    }

    public function isEmpty(): bool
    {
        return self::items() === [];
    }

    private function quantityOfProduct(int $productId): int
    {
        $total = 0;
        foreach (self::items() as $item) {
            if ((int)$item['product_id'] === $productId) {
                $total += (int)$item['qty'];
            }
        }
        return $total;
    }

    /**
     * Cart lines with fresh product data from the database. Products that were
     * removed are dropped, and quantities are trimmed to the current stock.
     */
    public function lines(): array
    {
        $items    = &self::items();
        $products = Product::findMany(array_column($items, 'product_id'));
        $variants = ProductVariant::names(array_column($items, 'variant_id'));
        $lines    = [];

        foreach ($items as $key => $item) {
            $product = $products[$item['product_id']] ?? null;
            if (!$product || (int)$product['stock'] < 1) {
                unset($items[$key]);
                continue;
            }
            $qty = min((int)$item['qty'], (int)$product['stock']);
            $items[$key]['qty'] = $qty;
            $unit = effective_price($product);

            $lines[$key] = [
                'key'          => $key,
                'product_id'   => (int)$product['product_id'],
                'variant_id'   => $item['variant_id'] ? (int)$item['variant_id'] : null,
                'variant_name' => $item['variant_id'] ? ($variants[$item['variant_id']] ?? null) : null,
                'name'         => $product['name'],
                'image'        => $product['image'],
                'stock'        => (int)$product['stock'],
                'unit_price'   => $unit,
                'qty'          => $qty,
                'subtotal'     => round($unit * $qty, 2),
            ];
        }
        return $lines;
    }

    public function total(?array $lines = null): float
    {
        return round(array_sum(array_column($lines ?? $this->lines(), 'subtotal')), 2);
    }
}
