<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiService
{
    private string $apiKey;
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function generateText(string $prompt): string
    {
        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent?key={$this->apiKey}";

        // Ajout d'un contexte pour guider le modèle
        $context = "You are an AI assistant specialized in education and culture. Answer questions in these fields. " .
            "Politely decline questions outside of these topics.";

        // Fusion du contexte avec le prompt utilisateur
        $fullPrompt = $context . " User's question: " . $prompt;

        $response = $this->client->request('POST', $url, [
            'json' => [
                'contents' => [
                    ['parts' => [['text' => $fullPrompt]]]
                ],
            ],
        ]);

        $data = $response->toArray();
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response from Gemini.';
    }
}