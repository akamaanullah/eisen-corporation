<?php

namespace App\Helpers;

class VehicleSpecOptions
{
    /** @var array<string, array<string, string>|list<string>>|null */
    private static ?array $catalog = null;

    /** @return array<string, array<string, string>|list<string>> */
    public static function catalog(): array
    {
        if (self::$catalog === null) {
            self::$catalog = require dirname(__DIR__, 2) . '/config/data/vehicle_spec_options.php';
        }

        return self::$catalog;
    }

    /** @return array<string, string> */
    public static function transmissionOptions(): array
    {
        return self::catalog()['transmission'];
    }

    /** @return array<string, string> */
    public static function fuelOptions(): array
    {
        return self::catalog()['fuel'];
    }

    /** @return list<string> */
    public static function bodyTypeOptions(): array
    {
        return self::catalog()['body_type'];
    }

    public static function isValidTransmission(string $value): bool
    {
        return array_key_exists(strtoupper(trim($value)), self::transmissionOptions());
    }

    public static function isValidFuel(string $value): bool
    {
        return array_key_exists(strtoupper(trim($value)), self::fuelOptions());
    }

    public static function isValidBodyType(string $value): bool
    {
        return in_array(trim($value), self::bodyTypeOptions(), true);
    }

    public static function transmissionLabel(?string $value): string
    {
        $normalized = strtoupper(trim((string) $value));
        return self::transmissionOptions()[$normalized] ?? VehicleDisplay::text($value);
    }

    public static function fuelLabel(?string $value): string
    {
        $normalized = strtoupper(trim((string) $value));
        return self::fuelOptions()[$normalized] ?? VehicleDisplay::text($value);
    }

    /** @return list<string> */
    public static function manualTransmissionCodes(): array
    {
        return ['MT', 'AMT', '4MT', '5MT', '6MT'];
    }

    /** @return list<string> */
    public static function primaryFuelCodes(): array
    {
        return ['PETROL', 'DIESEL', 'HYBRID', 'MILD_HYBRID', 'PLUG_IN_HYBRID', 'ELECTRIC'];
    }
}
