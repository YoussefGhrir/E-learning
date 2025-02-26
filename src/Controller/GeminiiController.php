<?php

namespace App\Controller;

use App\Service\GeminiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GeminiiController extends AbstractController
{
    #[Route('/gemini', name: 'gemini_test', methods: ['GET', 'POST'])]
    public function index(Request $request, GeminiService $geminiService): Response
    {
        $responseText = null;

        if ($request->isMethod('POST')) {
            $prompt = $request->request->get('prompt');
            $responseText = $geminiService->generateText($prompt);
        }

        return $this->render('geminii/index.html.twig', [
            'response' => $responseText,
        ]);
    }
}
