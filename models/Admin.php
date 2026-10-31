<?php

declare(strict_types=1);

final class Admin extends Model
{
    public static function find(int $id): ?array
    {
        return self::one('SELECT admin_id, name, email, phone FROM admin WHERE admin_id = ?', [$id]);
    }

    public static function findByEmailWithHash(string $email): ?array
    {
        return self::one('SELECT * FROM admin WHERE email = ?', [$email]);
    }
}
