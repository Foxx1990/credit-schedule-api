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
        $data = json_decode($request->getContent(), true);

        $amount = $data['amount'];
        $numInstallments = $data['num_installments'];
        $interestRate = $data['interest_rate'];

        $result = $this->creditService->calculateSchedule($amount, $numInstallments, $interestRate);

        return new JsonResponse($result);
    }

    /**
     * @Route("/api/schedules", methods={"GET"})
     */
    public function listSchedules(Request $request): JsonResponse
    {
        $includeExcluded = $request->query->get('include_excluded', 'all');

        $schedules = $this->creditService->listSchedules($includeExcluded === 'excluded');

        return new JsonResponse($schedules);
    }

    /**
     * @Route("/api/exclude/{id}", methods={"DELETE"})
     */
    public function excludeSchedule(int $id): Response
    {
        $this->creditService->excludeSchedule($id);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}