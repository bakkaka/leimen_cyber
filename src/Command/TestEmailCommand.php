<?php

namespace App\Command;

use App\Service\BrevoEmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:test-email',
    description: 'Test l\'envoi d\'email via Brevo',
)]
class TestEmailCommand extends Command
{
    private BrevoEmailService $emailService;

    public function __construct(BrevoEmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $result = $this->emailService->sendWelcomeEmail('azizmostafaoui@hotmail.com', 'Test CLI');
            if ($result) {
                $output->writeln('✅ Email envoyé');
            } else {
                $output->writeln('❌ Échec – Brevo a retourné false');
            }
        } catch (\Exception $e) {
            $output->writeln('❌ Exception : ' . $e->getMessage());
        }
        return Command::SUCCESS;
    }
}