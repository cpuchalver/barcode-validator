<?php

namespace Cpuch\BarcodeValidator;

use Illuminate\Support\Str;

class BarcodeValidator
{
    /**
     * Validate an EAN or UPC barcode.
     *
     * Supports EAN-8, UPC-A, and EAN-13 formats.
     *
     * @param  string  $value  The barcode value to validate.
     * @return bool True if the barcode is valid, false otherwise.
     */
    public static function validate(string $value): bool
    {
        // Check if the barcode has a valid length (8, 12, or 13),
        // and contains only digits, and is not composed entirely of zeros
        if (! preg_match('/^[0-9]{8}$|^[0-9]{12}$|^[0-9]{13}$/', $value) || preg_match('/^0+$/', $value)) {
            return false;
        }

        $length = Str::length($value);

        // Separate the data portion from the check digit
        $data = Str::substr($value, 0, $length - 1);
        $check_digit = (int) Str::substr($value, -1);

        // Validate the check digit
        return self::calculateCheckDigit($data) === $check_digit;
    }

    /**
     * Calculate the check digit using the standard EAN/UPC algorithm.
     *
     * @param  string  $data  The data portion of the barcode (without check digit).
     * @return int The calculated check digit.
     */
    private static function calculateCheckDigit(string $data): int
    {
        // Initialize sum for weighted calculation
        $check_sum = 0;

        // Convert string to array of digits
        $digits = str_split($data);

        // Process digits from right to left
        // This is required by the GS1 (EAN/UPC) specification, which defines
        // the weighting pattern relative to the check digit, not the start
        // of the barcode
        $digits = array_reverse($digits);

        // Calculate weighted sum
        // Using a 0-based index counted from the right (i.e. from the check digit):
        // - even index (0, 2, 4...) → weight ×3
        // - odd index  (1, 3, 5...) → weight ×1
        foreach ($digits as $index => $digit) {
            $digit = (int) $digit;
            $check_sum += $index % 2 === 0 ? $digit * 3 : $digit;
        }

        // Calculate the check digit (mod 10)
        // The check digit is the amount needed to round the sum up to the
        // next multiple of 10 (the "ten's complement" of check_sum mod 10).
        // The final % 10 handles the case where check_sum is already a
        // multiple of 10, so that 10 becomes 0 instead of an invalid digit.
        return (10 - ($check_sum % 10)) % 10;
    }
}
