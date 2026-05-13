<?php
// src/Service/PayPalService.php

namespace App\Service;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class PayPalService
{
    private PayPalHttpClient $client;
    private string $mode;

    public function __construct(string $clientId, string $clientSecret, string $mode)
    {
        $this->mode = $mode;
        
        if ($mode === 'sandbox') {
            $environment = new SandboxEnvironment($clientId, $clientSecret);
        } else {
            $environment = new ProductionEnvironment($clientId, $clientSecret);
        }
        
        $this->client = new PayPalHttpClient($environment);
    }

    public function createOrder(float $amount, string $currency, string $successUrl, string $cancelUrl): array
    {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $currency,
                    'value' => number_format($amount, 2, '.', '')
                ]
            ]],
            'application_context' => [
                'return_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'brand_name' => 'Cyber Formation Maroc',
                'user_action' => 'PAY_NOW',
            ]
        ];

        $response = $this->client->execute($request);
        return $response->result;
    }

    public function captureOrder(string $orderId): array
    {
        $request = new OrdersCaptureRequest($orderId);
        $response = $this->client->execute($request);
        return $response->result;
    }
}