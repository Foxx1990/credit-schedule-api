<?php


namespace App\Controller;

use App\Service\CreditService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class CreditController extends AbstractController
{
    private CreditService $creditService;

    public function __construct(CreditService $creditService)
    {
        $this->creditService = $creditService;
    }

    /**
     * @Route("/api/calculate", methods={"POST"})
     */
    public function calculate(Request $request): JsonResponse
    {
        $amount = $request->query->get('amount');
        $numInstallments = $request->query->get('num_installments');
        $interestRate = $request->query->get('interest_rate');

        $result = $this->creditService->calculateSchedule($amount, $numInstallments, $interestRate);

        return new JsonResponse($result);
    }

    /**
     * @Route("/api/schedules", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function listSchedules(): JsonResponse
    {
        try {
            $schedules = $this->creditService->listSchedules();
            return new JsonResponse($schedules);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

      /**
     * @Route("/api/exclude/{id}", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function excludeSchedule(int $id): JsonResponse
    {
        try {
            $this->creditService->excludeSchedule($id);
            return new JsonResponse(['status' => 'Calculation excluded']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}