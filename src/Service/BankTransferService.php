<?php
// src/Service/BankTransferService.php

namespace App\Service;

class BankTransferService
{
    private array $bankAccounts;

    public function __construct(string $attijariRib, string $attijariSwift, string $attijariName, string $cihRib, string $bpRib)
    {
        $this->bankAccounts = [
            'attijari' => [
                'name' => 'Attijariwafabank',
                'rib' => $attijariRib,
                'swift' => $attijariSwift,
                'account_name' => $attijariName,
            ],
            'cih' => [
                'name' => 'CIH Bank',
                'rib' => $cihRib,
                'swift' => 'CIHMMAMCXXX',
                'account_name' => 'Aziz Mostafaoui',
            ],
            'bp' => [
                'name' => 'Bank Populaire',
                'rib' => $bpRib,
                'swift' => 'BPMAMAMCXXX',
                'account_name' => 'Aziz Mostafaoui',
            ],
        ];
    }

    public function getAllBanks(): array
    {
        return $this->bankAccounts;
    }

    public function getBank(string $bankKey): ?array
    {
        return $this->bankAccounts[$bankKey] ?? null;
    }

    public function generatePaymentReference(int $userId, int $courseId): string
    {
        return 'CPY-' . date('Ymd') . '-' . $userId . '-' . $courseId;
    }
}