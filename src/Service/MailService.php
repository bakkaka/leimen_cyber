<?php

namespace App\Service;

use App\Entity\User;

class MailService
{
    public function __construct(
        private BrevoApiMailerService $brevoMailer,
    ) {}

    /**
     * Envoi de l'email de confirmation d'inscription
     */
    public function sendRegistrationConfirmation(User $user, string $confirmationUrl): void
    {
        $subject = 'Confirmation de votre inscription - Cyber Formation Maroc';
        $htmlContent = $this->renderRegistrationEmail($user, $confirmationUrl);
        $this->brevoMailer->sendEmail($user->getEmail(), $subject, $htmlContent);
    }

    /**
     * Envoi de l'email de réinitialisation de mot de passe
     */
    public function sendResetPasswordEmail(User $user, string $resetUrl): void
    {
        $subject = 'Réinitialisation de votre mot de passe - Cyber Formation Maroc';
        $htmlContent = $this->renderResetPasswordEmail($user, $resetUrl);
        $this->brevoMailer->sendEmail($user->getEmail(), $subject, $htmlContent);
    }

    /**
     * Rendu du template email de confirmation d'inscription
     */
    private function renderRegistrationEmail(User $user, string $confirmationUrl): string
    {
        $name = $user->getFullName() ?? $user->getEmail();
        
        return '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Confirmation d\'inscription</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { padding: 30px 20px; background: #f9f9f9; border-radius: 0 0 10px 10px; }
                    .button { display: inline-block; background: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; }
                    .footer { text-align: center; padding: 15px; font-size: 12px; color: #777; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1 style="margin: 0;">Cyber Formation Maroc</h1>
                    </div>
                    <div class="content">
                        <h2 style="color: #2c3e50;">Bonjour ' . htmlspecialchars($name) . ' !</h2>
                        <p>Merci pour votre inscription sur <strong>Cyber Formation Maroc</strong>.</p>
                        <p>Veuillez confirmer votre compte en cliquant sur le lien ci-dessous :</p>
                        <p style="text-align: center; margin: 30px 0;">
                            <a href="' . htmlspecialchars($confirmationUrl) . '" class="button">✅ Confirmer mon compte</a>
                        </p>
                        <p style="font-size: 12px; color: #e74c3c;">⚠️ Ce lien expire automatiquement pour des raisons de sécurité.</p>
                        <p>Cordialement,<br><strong>L\'équipe Cyber Formation Maroc</strong></p>
                    </div>
                    <div class="footer">
                        <p>© ' . date('Y') . ' Cyber Formation Maroc - Tous droits réservés</p>
                        <p>Marrakech, Maroc</p>
                    </div>
                </div>
            </body>
            </html>
        ';
    }

    /**
     * Rendu du template email de réinitialisation de mot de passe
     */
    private function renderResetPasswordEmail(User $user, string $resetUrl): string
    {
        $name = $user->getFullName() ?? $user->getEmail();
        
        return '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Réinitialisation de mot de passe</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { padding: 30px 20px; background: #f9f9f9; border-radius: 0 0 10px 10px; }
                    .button { display: inline-block; background: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; }
                    .footer { text-align: center; padding: 15px; font-size: 12px; color: #777; }
                    .warning { color: #e74c3c; font-size: 13px; background: #fff; padding: 10px; border-left: 4px solid #e74c3c; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1 style="margin: 0;">Cyber Formation Maroc</h1>
                    </div>
                    <div class="content">
                        <h2 style="color: #2c3e50;">Bonjour ' . htmlspecialchars($name) . ',</h2>
                        <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
                        <p>Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
                        <p style="text-align: center; margin: 30px 0;">
                            <a href="' . htmlspecialchars($resetUrl) . '" class="button">🔐 Réinitialiser mon mot de passe</a>
                        </p>
                        <div class="warning">
                            ⚠️ Ce lien expire dans 1 heure pour des raisons de sécurité.
                        </div>
                        <p>Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet email.</p>
                        <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
                        <p>Cordialement,<br><strong>L\'équipe Cyber Formation Maroc</strong></p>
                    </div>
                    <div class="footer">
                        <p>© ' . date('Y') . ' Cyber Formation Maroc - Tous droits réservés</p>
                        <p>Marrakech, Maroc</p>
                    </div>
                </div>
            </body>
            </html>
        ';
    }
}