<?php

namespace App\Controller;

use App\Service\GeminiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GeminiiController extends AbstractController
{
    #[Route('/gemini', name: 'gemini_test', methods: ['GET', 'POST'])]
    public function index(Request $request, GeminiService $geminiService): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            $data = json_decode($request->getContent(), true);
            $prompt = $data['prompt'] ?? '';

            if (!empty($prompt)) {
                $responseText = $geminiService->generateText($prompt);
                return new JsonResponse(['response' => $responseText]);
            }

            return new JsonResponse(['response' => 'Le message est vide.'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['error' => 'Invalid request.'], Response::HTTP_BAD_REQUEST);
    }
}
