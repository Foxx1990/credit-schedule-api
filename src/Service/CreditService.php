<?php

namespace App\Service;

use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CreditService
{
    private array $schedules = [];
    private array $excludedSchedules = [];

    public function calculateSchedule(float $amount, int $numInstallments, float $interestRate): array
    {
       // Validations
       $amount = floatval($amount);
       $numInstallments = intval($numInstallments);
       $interestRate = floatval($interestRate);

       if ($amount < 1000 || $amount > 12000 || $amount % 500 !== 0) {
           return new JsonResponse([
               'error' => 'Invalid amount'
           ], Response::HTTP_BAD_REQUEST);
       }

       if ($numInstallments < 3 || $numInstallments > 18 || $numInstallments % 3 !== 0) {
           return new JsonResponse([
               'error' => 'Invalid number of installments'
           ], Response::HTTP_BAD_REQUEST);
       }

    // Constants
    $k = 12; // number of installments per year
    $r = $interestRate / 100;
    $n = $numInstallments;

    $schedule = [];
    $principal = $amount;

    // Calculate the monthly installment amount (R)
    $r_per_k = $r / $k;
    $pow_term = pow(1 + $r_per_k, $n);
    $R = $principal * ($r_per_k * $pow_term) / ($pow_term - 1);

    for ($i = 1; $i <= $n; $i++) {
        $interest = $principal * $r_per_k;
        $capital = $R - $interest;
        $principal -= $capital;

        $schedule[] = [
            'installment_number' => $i,
            'installment_amount' => round($R, 2),
            'interest_amount' => round($interest, 2),
            'principal_amount' => round($capital, 2),
        ];
    }

    $metric = [
        'calculation_time' => (new \DateTime())->format('Y-m-d H:i:s'),
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
