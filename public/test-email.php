<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

$dsn = 'smtp://abdoubakka@gmail.com:cjkifzirghitqubk@smtp.gmail.com:587?encryption=tls&auth_mode=login';
$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);

$email = (new Email())
    ->from('abdoubakka@gmail.com')
    ->to('abdoubakka@gmail.com')  // mets une adresse où tu reçois
    ->subject('Test Gmail depuis Symfony')
    ->html('<h1>Succès !</h1><p>L’email est bien envoyé via Gmail SMTP.</p>');

$mailer->send($email);
echo "Email envoyé (vérifie la boîte de réception ou les spams)\n";