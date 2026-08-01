<?php

namespace Tests\Unit\Rules;

use Cpuch\BarcodeValidator\BarcodeValidatorServiceProvider;
use Cpuch\BarcodeValidator\Rules\BarcodeValidatorRule;
use Orchestra\Testbench\TestCase;

// use PHPUnit\Framework\TestCase;

class ValidatorRuleTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        // Ajoute ton service provider pour charger les traductions
        return [
            BarcodeValidatorServiceProvider::class,
        ];
    }

    /**
     * Test validation of valid barcodes.
     */
    public function test_valid_barcodes_pass()
    {
        foreach (self::validBarcodeProvider() as $barcode) {
            $rule = new BarcodeValidatorRule;

            $failCalled = false;
            $failCallback = function () use (&$failCalled) {
                $failCalled = true;

                return 'Error message';
            };

            $rule->validate('barcode', $barcode, $failCallback);

            $this->assertFalse($failCalled, "Validation should pass for {$barcode}");
        }
    }

    /**
     * Test validation of invalid barcodes.
     */
    public function test_valid_barcodes_fail()
    {
        foreach (self::invalidBarcodeProvider() as $barcode) {
            $rule = new BarcodeValidatorRule;

            $failCalled = false;
            $failMessage = null;
            $failCallback = function ($message) use (&$failCalled, &$failMessage) {
                $failCalled = true;
                $failMessage = $message;

                return $message;
            };

            $rule->validate('barcode', $barcode, $failCallback);

            $this->assertTrue($failCalled, "Validation should fail for {$barcode}");
            $this->assertNotNull($failMessage, 'Fail message should be set');
        }
    }

    /**
     * Provide valid barcodes for testing.
     */
    public static function validBarcodeProvider(): array
    {
        return [
            // EAN-8
            '96385074',
            // ['73513537'],

            // UPC-A
            '829576019311',
            '822603004809',

            // EAN-13
            '5901234123457',
            '3760068237264',
            // ['4006381333931'],
            // ['8711253001202'],
        ];
    }

    /**
     * Provide invalid barcodes for testing.
     */
    public static function invalidBarcodeProvider(): array
    {
        return [
            // Format invalide
            'ABC12345',
            '12345-6789',
            '123,456',

            // Longueur invalide
            '123456789',
            '1234567890123',
            '12345',
            '',

            // Check digit invalide
            '96385073',
            '5901234123458',
            '3760068237265',

            // Valeurs limites
            // ['00000000'],
            // ['000000000000'],
            // ['0000000000000'],
        ];
    }
}
