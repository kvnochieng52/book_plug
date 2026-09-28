<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MpesaService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('mpesa');
    }

    public function accessToken(): string
    {
        return Cache::remember('mpesa.access_token', 55 * 60, function () {
            $url = $this->url('oauth');
            $response = Http::withBasicAuth($this->config['consumer_key'], $this->config['consumer_secret'])
                ->acceptJson()
                ->get($url);

            if (! $response->successful() || ! $response->json('access_token')) {
                Log::error('M-Pesa OAuth failed', ['status' => $response->status(), 'body' => $response->body()]);
                throw new RuntimeException('Unable to authenticate with M-Pesa (Daraja).');
            }
            return $response->json('access_token');
        });
    }

    public function stkPush(string $phone, float $amount, string $reference, string $description = 'BookPlug order'): array
    {
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($this->config['shortcode'].$this->config['passkey'].$timestamp);

        $payload = [
            'BusinessShortCode' => $this->config['shortcode'],
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => $this->config['transaction_type'],
            'Amount' => (int) ceil($amount),
            'PartyA' => $this->normalizePhone($phone),
            'PartyB' => $this->config['shortcode'],
            'PhoneNumber' => $this->normalizePhone($phone),
            'CallBackURL' => $this->config['callback_url'] ?: 'https://example.com/api/mpesa/callback',
            'AccountReference' => substr($reference, 0, 12),
            'TransactionDesc' => substr($description, 0, 13),
        ];

        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->asJson()
            ->post($this->url('stk_push'), $payload);

        $body = $response->json() ?? [];

        if (! $response->successful()) {
            Log::error('M-Pesa STK Push failed', ['status' => $response->status(), 'body' => $body]);
            throw new RuntimeException($body['errorMessage'] ?? 'M-Pesa STK Push failed.');
        }

        return $body;
    }

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '0')) $digits = '254'.substr($digits, 1);
        if (str_starts_with($digits, '7') || str_starts_with($digits, '1')) $digits = '254'.$digits;
        if (str_starts_with($digits, '+')) $digits = substr($digits, 1);
        return $digits;
    }

    protected function url(string $key): string
    {
        $env = $this->config['env'] === 'production' ? 'production' : 'sandbox';
        return $this->config['urls'][$env][$key];
    }
}
