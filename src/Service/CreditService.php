<?php

namespace App\Service;

use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\CreditCalculation;
use Doctrine\ORM\EntityManagerInterface;

class CreditService
{
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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

        // Save to database
        $calculation = new CreditCalculation();
        $calculation->setAmount($amount);
        $calculation->setNumInstallments($numInstallments);
        $calculation->setInterestRate($interestRate);
        $calculation->setCalculationTime(new \DateTime());
        $calculation->setSchedule($schedule);

        $this->entityManager->persist($calculation);
        $this->entityManager->flush();

        return $result;
    }

    public function listSchedules(): array
    {
        $repository = $this->entityManager->getRepository(CreditCalculation::class);

        // Download the last 4 calculations, sorted by calculation time (most recent first)
        $schedules = $repository->createQueryBuilder('c')
            ->orderBy('c.calculationTime', 'DESC')
            ->setMaxResults(4)
            ->getQuery()
            ->getArrayResult();

        return $schedules;
    }


    public function excludeSchedule(int $id): void
    {
        $repository = $this->entityManager->getRepository(CreditCalculation::class);
        $calculation = $repository->find($id);

        if ($calculation) {
            $calculation->setExcluded(true);
            $this->entityManager->flush();
        }
    }
}
