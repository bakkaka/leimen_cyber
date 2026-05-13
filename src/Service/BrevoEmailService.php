<?php
// src/Service/BrevoEmailService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BrevoEmailService
{
    private string $apiKey;
    private string $fromEmail;
    private string $fromName;
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client, string $brevoApiKey, string $brevoFromEmail, string $brevoFromName)
    {
        $this->client = $client;
        $this->apiKey = $brevoApiKey;
        $this->fromEmail = $brevoFromEmail;
        $this->fromName = $brevoFromName;
    }

    public function sendEmail(string $to, string $subject, string $htmlContent, string $toName = ''): bool
    {
        try {
            $response = $this->client->request('POST', 'https://api.brevo.com/v3/smtp/email', [
                'headers' => [
                    'api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'sender' => [
                        'name' => $this->fromName,
                        'email' => $this->fromEmail,
                    ],
                    'to' => [[
                        'email' => $to,
                        'name' => $toName ?: $to
                    ]],
                    'subject' => $subject,
                    'htmlContent' => $htmlContent,
                ]
            ]);

            return $response->getStatusCode() === 201;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function sendWelcomeEmail(string $to, string $name): bool
    {
        $subject = 'Bienvenue sur Cyber Formation Maroc !';
        $htmlContent = "
            <h1>Bienvenue {$name} !</h1>
            <p>Nous sommes ravis de vous accueillir sur Cyber Formation Maroc.</p>
            <p>Vous pouvez dès maintenant découvrir nos formations en cybersécurité.</p>
            <a href='https://localhost:8000/formations'>Voir les formations</a>
        ";
        return $this->sendEmail($to, $subject, $htmlContent, $name);
    }

    public function sendPaymentConfirmation(string $to, string $name, string $courseTitle, string $price): bool
    {
        $subject = 'Confirmation de paiement - Cyber Formation Maroc';
        $htmlContent = "
            <h1>Merci {$name} !</h1>
            <p>Votre paiement de <strong>{$price} MAD</strong> pour la formation <strong>{$courseTitle}</strong> a été confirmé.</p>
            <p>Vous pouvez maintenant accéder à votre formation.</p>
            <a href='https://localhost:8000/formations'>Accéder à ma formation</a>
        ";
        return $this->sendEmail($to, $subject, $htmlContent, $name);
    }
}