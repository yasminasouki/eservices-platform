<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Fiat reference rates (Frankfurter / ECB) and crypto vs USD (CoinGecko public API).
 */
class CurrencyExchangeService
{
    private const CRYPTO_IDS = [
        'btc' => 'bitcoin',
        'eth' => 'ethereum',
        'usdt' => 'tether',
    ];

    public function usdToEurRate(): float
    {
        $ttl = max(60, (int) config('payments.exchange.cache_ttl', 120));

        return (float) Cache::remember('exchange.usd_eur', $ttl, function () {
            $url = rtrim((string) config('payments.exchange.frankfurter_base'), '/').'?from=USD&to=EUR';
            $response = Http::timeout((int) config('payments.exchange.http_timeout', 12))
                ->acceptJson()
                ->get($url);

            if (! $response->successful()) {
                throw new RuntimeException('Unable to fetch USD/EUR rate.');
            }

            $rate = $response->json('rates.EUR');
            if (! is_numeric($rate) || (float) $rate <= 0) {
                throw new RuntimeException('Invalid USD/EUR rate response.');
            }

            return (float) $rate;
        });
    }

    /**
     * @return array{asset: string, coingecko_id: string, usd_per_unit: float, usd_amount: float, crypto_amount: string, rate_source: string}
     */
    public function quoteCryptoFromUsd(string $asset, float $usdAmount): array
    {
        $asset = strtolower($asset);
        if (! isset(self::CRYPTO_IDS[$asset])) {
            throw new RuntimeException('Unsupported crypto asset.');
        }

        if ($usdAmount <= 0) {
            throw new RuntimeException('Amount must be positive.');
        }

        $coingeckoId = self::CRYPTO_IDS[$asset];
        $ttl = max(60, (int) config('payments.exchange.cache_ttl', 120));
        $cacheKey = 'exchange.coingecko.'.$coingeckoId;

        $usdPerUnit = (float) Cache::remember($cacheKey, $ttl, function () use ($coingeckoId) {
            $base = rtrim((string) config('payments.exchange.coingecko_base'), '/');
            $url = $base.'/simple/price?ids='.$coingeckoId.'&vs_currencies=usd';
            $response = Http::timeout((int) config('payments.exchange.http_timeout', 12))
                ->acceptJson()
                ->get($url);

            if (! $response->successful()) {
                throw new RuntimeException('Unable to fetch crypto price.');
            }

            $price = $response->json($coingeckoId.'.usd');
            if (! is_numeric($price) || (float) $price <= 0) {
                throw new RuntimeException('Invalid crypto price response.');
            }

            return (float) $price;
        });

        $cryptoAmount = $usdAmount / $usdPerUnit;

        return [
            'asset' => $asset,
            'coingecko_id' => $coingeckoId,
            'usd_per_unit' => $usdPerUnit,
            'usd_amount' => $usdAmount,
            'crypto_amount' => $this->formatCryptoAmount($asset, $cryptoAmount),
            'rate_source' => 'coingecko_simple_price',
        ];
    }

    private function formatCryptoAmount(string $asset, float $amount): string
    {
        $decimals = match ($asset) {
            'btc' => 8,
            'eth' => 6,
            default => 6,
        };

        return number_format($amount, $decimals, '.', '');
    }
}
