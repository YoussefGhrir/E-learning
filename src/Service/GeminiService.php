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

        $response = $this->client->request('POST', $url, [
            'json' => [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
            ],
        ]);

        $data = $response->toArray();
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response from Gemini.';
    }
}
