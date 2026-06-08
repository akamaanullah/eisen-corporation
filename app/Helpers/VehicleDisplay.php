<?php

namespace App\Helpers;

class VehicleDisplay
{
    public const NA = 'N/A';

    public static function modelWithGrade(string $model, ?string $carGrade): string
    {
        $grade = trim((string) $carGrade);
        if ($grade === '' || $grade === '-') {
            return $model;
        }

        return $model . ' ' . $grade;
    }

    public static function title(int $year, string $make, string $model, ?string $carGrade): string
    {
        return $year . ' ' . strtoupper($make) . ' ' . self::modelWithGrade($model, $carGrade);
    }

    public static function isMissingText(?string $value, array $extraMissing = []): bool
    {
        $normalized = trim((string) $value);
        if ($normalized === '' || $normalized === '-' || strcasecmp($normalized, 'n/a') === 0) {
            return true;
        }

        return in_array($normalized, $extraMissing, true);
    }

    public static function text(?string $value, array $extraMissing = []): string
    {
        return self::isMissingText($value, $extraMissing) ? self::NA : trim((string) $value);
    }

    public static function upperText(?string $value, array $extraMissing = []): string
    {
        $formatted = self::text($value, $extraMissing);
        return $formatted === self::NA ? self::NA : strtoupper($formatted);
    }

    public static function dimension(?string $value): string
    {
        $normalized = trim((string) $value);
        if ($normalized === '' || preg_match('/^0\.00\s*m\s*[×x]\s*0\.00\s*m\s*[×x]\s*0\.00\s*m$/iu', $normalized)) {
            return self::NA;
        }

        return $normalized;
    }

    public static function cubicMeters(?string $value): string
    {
        return self::text($value, ['10.167', '0', '0.00']);
    }

    public static function mileageKm(int $km): string
    {
        return $km > 0 ? number_format($km) . 'km' : self::NA;
    }

    public static function engineCc(int $cc): string
    {
        return $cc > 0 ? number_format($cc) . 'cc' : self::NA;
    }

    public static function drive(?string $value): string
    {
        return self::text($value);
    }

    public static function count(?int $value): string
    {
        return ($value ?? 0) > 0 ? (string) $value : self::NA;
    }

    public static function transmission(?string $value): string
    {
        $normalized = strtoupper(trim((string) $value));
        if ($normalized === 'AT') {
            return 'Automatic (AT)';
        }
        if ($normalized === 'MT') {
            return 'Manual (MT)';
        }

        return self::text($value);
    }

    public static function steering(?string $value): string
    {
        $normalized = strtoupper(trim((string) $value));
        if ($normalized === 'RHD') {
            return 'Right Hand Drive';
        }
        if ($normalized === 'LHD') {
            return 'Left Hand Drive';
        }

        return self::text($value);
    }

    public static function location(?string $value): string
    {
        return self::text($value);
    }

    public static function listingCityKey(?string $value): string
    {
        if (self::isMissingText($value)) {
            return '';
        }

        $city = strtolower(trim(explode(',', (string) $value)[0]));
        $known = ['tokyo', 'osaka', 'yokohama', 'nagoya', 'fukuoka', 'sapporo', 'kobe', 'kyoto', 'hiroshima'];
        foreach ($known as $key) {
            if (strpos($city, $key) !== false) {
                return $key;
            }
        }

        return '';
    }
}
