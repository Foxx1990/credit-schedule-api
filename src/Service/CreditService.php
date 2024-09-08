<?php

namespace App\Service;

use DateTime;

class CreditService
{
    private array $schedules = [];
    private array $excludedSchedules = [];

    public function calculateSchedule(float $amount, int $numInstallments, float $interestRate): array
    {
        if ($amount < 1000 || $amount > 12000 || $amount % 500 !== 0) {
            throw new \InvalidArgumentException('Invalid amount');
        }

        if ($numInstallments < 3 || $numInstallments > 18 || $numInstallments % 3 !== 0) {
            throw new \InvalidArgumentException('Invalid number of installments');
        }

        $k = 12; // number of installments per year
        $r = $interestRate / 100;
        $n = $numInstallments;

        $schedule = [];
        $totalInterest = 0;

        for ($i = 1; $i <= $n; $i++) {
            $R = $amount * $r / $k * pow(1 + $r / $k, $n) / (pow(1 + $r / $k, $n) - 1);
            $interest = $amount * $r / $k;
            $principal = $R - $interest;
            $amount -= $principal;
            $totalInterest += $interest;

            $schedule[] = [
                'installment_number' => $i,
                'installment_amount' => round($R, 2),
                'interest_amount' => round($interest, 2),
                'principal_amount' => round($principal, 2),
            ];
        }

        $metric = [
            'calculation_time' => (new DateTime())->format('Y-m-d H:i:s'),
            'num_installments' => $numInstallments,
            'amount' => $amount,
            'interest_rate' => $interestRate,
        ];

        $result = [
            'metric' => $metric,
            'schedule' => $schedule,
        ];

        $this->schedules[] = $result;

        return $result;
    }

    public function listSchedules(bool $excludedOnly = false): array
    {
        $schedules = $excludedOnly ? $this->excludedSchedules : $this->schedules;
        usort($schedules, fn($a, $b) => array_sum(array_column($b['schedule'], 'interest_amount')) <=> array_sum(array_column($a['schedule'], 'interest_amount')));
        return array_slice($schedules, 0, 4);
    }

    public function excludeSchedule(int $id): void
    {
        // For simplicity, we'll use a basic approach
        $this->excludedSchedules[] = $this->schedules[$id] ?? [];
        unset($this->schedules[$id]);
    }
}
