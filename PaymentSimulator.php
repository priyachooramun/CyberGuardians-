<?php
/**
 * Simulated card payment – NO real money, NO real gateway, NO card data stored.
 * Owner: Member 4 (Cart, checkout & orders)
 *
 * What happens:
 *  1. The card number is checked with the Luhn algorithm (the checksum every
 *     real card number passes), plus expiry date and CVV format.
 *  2. Test cards decide the result, like Stripe's test mode:
 *       4242 4242 4242 4242  → approved (Visa)
 *       5555 5555 5555 4444  → approved (Mastercard)
 *       4000 0000 0000 0002  → declined (card declined)
 *       4000 0000 0000 9995  → declined (insufficient funds)
 *     Any other Luhn-valid number is approved.
 *  3. Only the last 4 digits and a fake transaction reference are saved.
 */

declare(strict_types=1);

final class PaymentSimulator
{
    private const DECLINED = [
        '4000000000000002' => 'Your card was declined by the bank (simulated).',
        '4000000000009995' => 'Insufficient funds on this card (simulated).',
    ];

    /** Remove spaces and dashes. */
    public static function normalise(string $number): string
    {
        return preg_replace('/\D/', '', $number) ?? '';
    }

    public static function luhnValid(string $number): bool
    {
        if (!preg_match('/^\d{13,19}$/', $number)) {
            return false;
        }
        $sum    = 0;
        $double = false;
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = (int)$number[$i];
            if ($double) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum   += $digit;
            $double = !$double;
        }
        return $sum % 10 === 0;
    }

    public static function brand(string $number): string
    {
        return match (true) {
            str_starts_with($number, '4')                     => 'Visa',
            (bool)preg_match('/^(5[1-5]|2[2-7])/', $number)   => 'Mastercard',
            (bool)preg_match('/^3[47]/', $number)             => 'American Express',
            default                                           => 'Card',
        };
    }

    /** Expiry as MM/YY; must be this month or later (and not more than 15 years ahead). */
    public static function expiryValid(string $expiry): bool
    {
        if (!preg_match('/^(0[1-9]|1[0-2])\s*\/\s*(\d{2})$/', trim($expiry), $m)) {
            return false;
        }
        $month   = (int)$m[1];
        $year    = 2000 + (int)$m[2];
        $current = (int)date('Y') * 12 + (int)date('n');
        $card    = $year * 12 + $month;
        return $card >= $current && $card <= $current + 15 * 12;
    }

    public static function cvvValid(string $cvv, string $number): bool
    {
        $length = self::brand($number) === 'American Express' ? 4 : 3;
        return (bool)preg_match('/^\d{' . $length . '}$/', $cvv);
    }

    /**
     * Validate the card fields. Returns field => message errors (empty = valid).
     */
    public static function validate(string $number, string $expiry, string $cvv): array
    {
        $errors = [];
        $number = self::normalise($number);
        if (!self::luhnValid($number)) {
            $errors['card_number'] = 'Please enter a valid card number.';
        }
        if (!self::expiryValid($expiry)) {
            $errors['expiry'] = 'Enter a valid expiry date (MM/YY) that is not in the past.';
        }
        if (!self::cvvValid(trim($cvv), $number)) {
            $errors['cvv'] = 'Enter the 3-digit security code (4 digits for American Express).';
        }
        return $errors;
    }

    /**
     * "Charge" the card. Returns
     *   ['approved' => bool, 'message' => string, 'reference' => string, 'last4' => string, 'method' => string]
     */
    public static function charge(string $number, float $amount): array
    {
        $number = self::normalise($number);
        usleep(400000);   // pretend to talk to the bank (0.4 s)

        $reference = 'SIM-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $result    = [
            'approved'  => true,
            'message'   => 'Payment approved.',
            'reference' => $reference,
            'last4'     => substr($number, -4),
            'method'    => 'Card (' . self::brand($number) . ')',
        ];

        if (isset(self::DECLINED[$number])) {
            $result['approved'] = false;
            $result['message']  = self::DECLINED[$number];
        } elseif ($amount <= 0) {
            $result['approved'] = false;
            $result['message']  = 'Nothing to pay.';
        }
        return $result;
    }
}
