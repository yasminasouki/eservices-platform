<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlockchainVerificationService
{
    // USDT ERC-20 contract address (mainnet)
    private const USDT_CONTRACT = '0xdac17f958d2ee523a2206206994597c13d831ec7';

    public function verify(string $asset, string $txHash): array
    {
        try {
            return match (strtolower($asset)) {
                'btc'  => $this->verifyBtc($txHash),
                'eth'  => $this->verifyEth($txHash),
                'usdt' => $this->verifyUsdt($txHash),
                default => [
                    'found' => false,
                    'error' => 'Unsupported asset.',
                    'explorer_url' => '',
                ],
            };
        } catch (\Throwable $e) {
            Log::warning('Blockchain verification error.', [
                'asset' => $asset,
                'tx'    => $txHash,
                'error' => $e->getMessage(),
            ]);

            return [
                'found'        => false,
                'confirmed'    => false,
                'confirmations' => null,
                'to_address'   => null,
                'amount'       => null,
                'explorer_url' => $this->explorerUrl($asset, $txHash),
                'error'        => 'Verification service temporarily unavailable. Check explorer link manually.',
            ];
        }
    }

    // ── Bitcoin via Blockstream.info (no API key required) ─────────────────────

    private function verifyBtc(string $txHash): array
    {
        $base        = rtrim((string) config('payments.blockchain.blockstream_base', 'https://blockstream.info/api'), '/');
        $explorerUrl = "https://blockstream.info/tx/{$txHash}";

        $res = Http::timeout(15)->get("{$base}/tx/{$txHash}");

        if ($res->status() === 404) {
            return $this->notFound('btc', $txHash, 'Transaction not found on the Bitcoin network.');
        }
        if (! $res->successful()) {
            throw new \RuntimeException("Blockstream API error: {$res->status()}");
        }

        $tx        = $res->json();
        $confirmed = (bool) ($tx['status']['confirmed'] ?? false);

        // Collect all output addresses and total BTC sent
        $toAddress    = null;
        $totalSatoshis = 0;
        foreach ($tx['vout'] ?? [] as $vout) {
            if (isset($vout['scriptpubkey_address'])) {
                $toAddress     = $toAddress ?? $vout['scriptpubkey_address'];
                $totalSatoshis += (int) ($vout['value'] ?? 0);
            }
        }

        $confirmations = 0;
        if ($confirmed && isset($tx['status']['block_height'])) {
            $heightRes = Http::timeout(10)->get("{$base}/blocks/tip/height");
            if ($heightRes->successful()) {
                $currentHeight = (int) trim($heightRes->body());
                $confirmations = max(0, $currentHeight - (int) $tx['status']['block_height'] + 1);
            }
        }

        $required = (int) config('payments.blockchain.required_confirmations.btc', 2);

        return [
            'found'                  => true,
            'confirmed'              => $confirmed && $confirmations >= $required,
            'confirmations'          => $confirmations,
            'required_confirmations' => $required,
            'to_address'             => $toAddress,
            'amount'                 => number_format($totalSatoshis / 1e8, 8, '.', ''),
            'asset_label'            => 'BTC',
            'explorer_url'           => $explorerUrl,
            'error'                  => null,
        ];
    }

    // ── Ethereum via Etherscan (free key from etherscan.io/register) ───────────

    private function verifyEth(string $txHash): array
    {
        $data        = $this->fetchEtherscanTx($txHash);
        $explorerUrl = "https://etherscan.io/tx/{$txHash}";

        if ($data === null) {
            return $this->notFound('eth', $txHash, 'Transaction not found on the Ethereum network.');
        }

        [$confirmations, $confirmed] = $this->ethConfirmations($data, 'eth');

        $toAddress = $data['to'] ?? null;
        $amount    = number_format($this->hexToFloat($data['value'] ?? '0x0') / 1e18, 8, '.', '');

        return [
            'found'                  => true,
            'confirmed'              => $confirmed,
            'confirmations'          => $confirmations,
            'required_confirmations' => (int) config('payments.blockchain.required_confirmations.eth', 12),
            'to_address'             => $toAddress,
            'amount'                 => $amount,
            'asset_label'            => 'ETH',
            'explorer_url'           => $explorerUrl,
            'error'                  => null,
        ];
    }

    // ── USDT ERC-20 via Etherscan ───────────────────────────────────────────────

    private function verifyUsdt(string $txHash): array
    {
        $data        = $this->fetchEtherscanTx($txHash);
        $explorerUrl = "https://etherscan.io/tx/{$txHash}";

        if ($data === null) {
            return $this->notFound('usdt', $txHash, 'Transaction not found on the Ethereum network.');
        }

        [$confirmations, $confirmed] = $this->ethConfirmations($data, 'usdt');

        // Parse ERC-20 transfer(address,uint256): selector 0xa9059cbb
        $toAddress = null;
        $amount    = null;
        $input     = $data['input'] ?? '';

        if (str_starts_with(strtolower($input), '0xa9059cbb') && strlen($input) >= 138) {
            $toAddress  = '0x' . substr($input, 34, 40);
            $amountHex  = substr($input, 74, 64);
            $amountRaw  = $this->hexToFloat($amountHex);
            $amount     = number_format($amountRaw / 1e6, 6, '.', ''); // USDT = 6 decimals
        } else {
            // Fallback: the `to` field is the contract; recipient is in input
            $toAddress = $data['to'] ?? null;
        }

        $toContract = strtolower($data['to'] ?? '');
        $isUsdtTx   = $toContract === self::USDT_CONTRACT;

        return [
            'found'                  => true,
            'confirmed'              => $confirmed,
            'confirmations'          => $confirmations,
            'required_confirmations' => (int) config('payments.blockchain.required_confirmations.usdt', 12),
            'to_address'             => $toAddress,
            'amount'                 => $amount,
            'asset_label'            => 'USDT (ERC-20)',
            'is_usdt_contract'       => $isUsdtTx,
            'explorer_url'           => $explorerUrl,
            'error'                  => $isUsdtTx ? null : 'Warning: transaction does not call the USDT contract.',
        ];
    }

    // ── Shared Etherscan helpers ────────────────────────────────────────────────

    private function fetchEtherscanTx(string $txHash): ?array
    {
        $base   = rtrim((string) config('payments.blockchain.etherscan_base', 'https://api.etherscan.io/api'), '/');
        $apiKey = (string) config('payments.blockchain.etherscan_api_key', '');

        $params = [
            'module'  => 'proxy',
            'action'  => 'eth_getTransactionByHash',
            'txhash'  => $txHash,
        ];
        if ($apiKey !== '') {
            $params['apikey'] = $apiKey;
        }

        $res = Http::timeout(15)->get($base, $params);
        if (! $res->successful()) {
            throw new \RuntimeException("Etherscan API error: {$res->status()}");
        }

        $result = $res->json('result');

        return (is_array($result) && ! empty($result)) ? $result : null;
    }

    /** @return array{int, bool} [$confirmations, $confirmed] */
    private function ethConfirmations(array $txData, string $asset): array
    {
        $txBlockHex = $txData['blockNumber'] ?? null;
        if ($txBlockHex === null) {
            return [0, false];
        }

        $base   = rtrim((string) config('payments.blockchain.etherscan_base', 'https://api.etherscan.io/api'), '/');
        $apiKey = (string) config('payments.blockchain.etherscan_api_key', '');

        $params = ['module' => 'proxy', 'action' => 'eth_blockNumber'];
        if ($apiKey !== '') {
            $params['apikey'] = $apiKey;
        }

        $blockRes = Http::timeout(10)->get($base, $params);
        if (! $blockRes->successful()) {
            return [0, false];
        }

        $currentBlock  = $this->hexToFloat($blockRes->json('result') ?? '0x0');
        $txBlock       = $this->hexToFloat($txBlockHex);
        $confirmations = (int) max(0, $currentBlock - $txBlock + 1);
        $required      = (int) config("payments.blockchain.required_confirmations.{$asset}", 12);

        return [$confirmations, $confirmations >= $required];
    }

    // ── Utilities ───────────────────────────────────────────────────────────────

    private function hexToFloat(string $hex): float
    {
        $hex = strtolower(ltrim($hex, '0x'));
        if ($hex === '' || $hex === '0') {
            return 0.0;
        }
        if (function_exists('gmp_strval')) {
            return (float) gmp_strval(gmp_init($hex, 16));
        }

        return (float) hexdec($hex);
    }

    private function notFound(string $asset, string $txHash, string $message): array
    {
        return [
            'found'        => false,
            'confirmed'    => false,
            'confirmations' => 0,
            'to_address'   => null,
            'amount'       => null,
            'explorer_url' => $this->explorerUrl($asset, $txHash),
            'error'        => $message,
        ];
    }

    private function explorerUrl(string $asset, string $txHash): string
    {
        return match (strtolower($asset)) {
            'btc'  => "https://blockstream.info/tx/{$txHash}",
            default => "https://etherscan.io/tx/{$txHash}",
        };
    }
}
