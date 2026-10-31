<?php


declare(strict_types=1);

final class Bookstore extends Model
{
    public static function info(): array
    {
        return self::one('SELECT * FROM bookstore ORDER BY store_id LIMIT 1') ?? [
            'name' => 'InkCorner', 'address' => 'Port Louis, Mauritius', 'phone' => '',
            'email' => '', 'opening_hours' => '',
        ];
    }
}
