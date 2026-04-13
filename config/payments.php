<?php

/*
| If CRYPTO_* env vars are empty, optional demo wallet strings fill in so the crypto
| checkout UI works without configuring real addresses. Default: enabled when APP_ENV=local.
| Set CRYPTO_USE_DEMO_ADDRESSES=false to require real .env addresses.
| Set CRYPTO_USE_DEMO_ADDRESSES=true to allow demos on other environments (not recommended for production).
*/
$useDemoCryptoWallets = filter_var(
    env('CRYPTO_USE_DEMO_ADDRESSES', env('APP_ENV') === 'local'),
    FILTER_VALIDATE_BOOL
);

$demoBtc = 'bc1qdemolocal00000000000000000000000000000';
$demoEth = '0xDeMoL0ca10000000000000000000000000000000';
$demoUsdt = '0xDeMoL0ca10000000000000000000000000000000';

$envBtc = trim((string) env('CRYPTO_BTC_ADDRESS'));
$envEth = trim((string) env('CRYPTO_ETH_ADDRESS'));
$envUsdt = trim((string) env('CRYPTO_USDT_ERC20_ADDRESS'));

$resolvedBtc = $envBtc !== '' ? $envBtc : ($useDemoCryptoWallets ? $demoBtc : '');
$resolvedEth = $envEth !== '' ? $envEth : ($useDemoCryptoWallets ? $demoEth : '');
$resolvedUsdt = $envUsdt !== '' ? $envUsdt : ($useDemoCryptoWallets ? $demoUsdt : '');

$showDemoCryptoNotice = $useDemoCryptoWallets && ($envBtc === '' || $envEth === '' || $envUsdt === '');

return [

    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cryptocurrency receiving addresses (shown to citizens after rate quote).
    |--------------------------------------------------------------------------
    */
    'crypto' => [
        'btc_address' => $resolvedBtc,
        'eth_address' => $resolvedEth,
        'usdt_erc20_address' => $resolvedUsdt,
        'show_demo_crypto_notice' => $showDemoCryptoNotice,
        /*
         * When true, submitting a transaction reference on the crypto flow marks
         * the payment completed immediately (good for local demos only).
         */
        'auto_complete_after_citizen_submit' => filter_var(
            env('PAYMENTS_CRYPTO_AUTO_COMPLETE', env('APP_ENV') === 'local'),
            FILTER_VALIDATE_BOOL
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Exchange rate providers (no API keys required for defaults).
    |--------------------------------------------------------------------------
    */
    'exchange' => [
        'frankfurter_base' => env('EXCHANGE_FRANKFURTER_URL', 'https://api.frankfurter.app/latest'),
        'coingecko_base' => env('EXCHANGE_COINGECKO_URL', 'https://api.coingecko.com/api/v3'),
        'cache_ttl' => (int) env('EXCHANGE_CACHE_TTL', 120),
        'http_timeout' => (int) env('EXCHANGE_HTTP_TIMEOUT', 12),
    ],

];
