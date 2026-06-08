<?php

namespace App\Helpers;

class ExchangeRate
{
    private const API_URL = 'https://open.er-api.com/v6/latest/USD';
    private const CACHE_TTL = 3600;
    private const FALLBACK_RATE = 150.0;
    private const MIN_RATE = 50.0;
    private const MAX_RATE = 300.0;

    public static function getUsdJpyRate(): float
    {
        $cached = self::readCache(false);
        if ($cached !== null) {
            return $cached;
        }

        $live = self::fetchLiveRate();
        if ($live !== null) {
            self::writeCache($live);
            return $live;
        }

        $stale = self::readCache(true);
        return $stale ?? self::FALLBACK_RATE;
    }

    public static function resolveRate(mixed $postedRate): float
    {
        $rate = is_numeric($postedRate) ? (float) $postedRate : 0.0;
        if ($rate >= self::MIN_RATE && $rate <= self::MAX_RATE) {
            return $rate;
        }

        return self::getUsdJpyRate();
    }

    private static function cachePath(): string
    {
        $dir = ROOT_DIR . '/storage/cache';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir . '/usd_jpy_rate.json';
    }

    private static function readCache(bool $allowStale): ?float
    {
        $path = self::cachePath();
        if (!is_file($path)) {
            return null;
        }

        $payload = json_decode((string) file_get_contents($path), true);
        if (!is_array($payload)) {
            return null;
        }

        $rate = isset($payload['rate']) ? (float) $payload['rate'] : 0.0;
        $savedAt = isset($payload['saved_at']) ? (int) $payload['saved_at'] : 0;

        if ($rate < self::MIN_RATE || $rate > self::MAX_RATE) {
            return null;
        }

        if (!$allowStale && ($savedAt <= 0 || (time() - $savedAt) > self::CACHE_TTL)) {
            return null;
        }

        return $rate;
    }

    private static function writeCache(float $rate): void
    {
        file_put_contents(self::cachePath(), json_encode([
            'rate' => $rate,
            'saved_at' => time(),
        ], JSON_UNESCAPED_SLASHES));
    }

    private static function fetchLiveRate(): ?float
    {
        if (!function_exists('curl_init')) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'header' => "User-Agent: Eisen-Admin/1.0\r\n",
                ],
            ]);
            $body = @file_get_contents(self::API_URL, false, $context);
        } else {
            $ch = curl_init(self::API_URL);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_USERAGENT => 'Eisen-Admin/1.0',
            ]);
            $body = curl_exec($ch);
            curl_close($ch);
        }

        if ($body === false || $body === '') {
            return null;
        }

        $data = json_decode($body, true);
        $rate = isset($data['rates']['JPY']) ? (float) $data['rates']['JPY'] : 0.0;

        if ($rate < self::MIN_RATE || $rate > self::MAX_RATE) {
            return null;
        }

        return $rate;
    }
}
