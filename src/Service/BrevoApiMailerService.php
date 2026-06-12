<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BrevoApiMailerService
{
    private string $apiKey;
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client, string $brevoApiKey)
    {
        $this->client = $client;
        $this->apiKey = $brevoApiKey;
    }

    public function sendEmail(string $to, string $subject, string $htmlContent): void
    {
        $response = $this->client->request('POST', 'https://api.brevo.com/v3/smtp/email', [
            'headers' => [
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'sender' => [
                    'name' => 'Cyber Formation Maroc',
                    'email' => 'admin@cyberleimen.com',
                ],
                'to' => [['email' => $to]],
                'subject' => $subject,
                'htmlContent' => $htmlContent,
            ]
        ]);

        if ($response->getStatusCode() >= 400) {
            throw new \RuntimeException('Erreur lors de l’envoi : ' . $response->getContent(false));
        }
    }

    public function sendResetPasswordEmail(string $to, string $resetToken): void
{
    $resetLink = 'https://cyberleimen.com/reset-password/' . $resetToken;
    $htmlContent = '<p>Cliquez sur ce lien pour réinitialiser votre mot de passe :</p><a href="' . $resetLink . '">' . $resetLink . '</a>';
    
    $this->sendEmail($to, 'Réinitialisation de votre mot de passe', $htmlContent);
}
}