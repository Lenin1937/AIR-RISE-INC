<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
    
    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Generate streaming response from Gemini API
     * This method yields chunks as they arrive from Gemini
     */
    public function streamChat(string $message, array $context = [])
    {
        $systemPrompt = $this->buildSystemPrompt($context);
        
        $url = "{$this->baseUrl}/models/gemini-1.5-flash:streamGenerateContent?alt=sse&key={$this->apiKey}";
        
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $systemPrompt . "\n\nUser Question: " . $message]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
            ]
        ];

        try {
            // Use Guzzle to stream the response
            $client = new \GuzzleHttp\Client();
            $response = $client->post($url, [
                'json' => $payload,
                'stream' => true,
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $body = $response->getBody();
            
            while (!$body->eof()) {
                $chunk = $body->read(1024);
                
                if (empty($chunk)) {
                    continue;
                }

                // Parse SSE format from Gemini
                $lines = explode("\n", $chunk);
                
                foreach ($lines as $line) {
                    if (strpos($line, 'data: ') === 0) {
                        $jsonData = substr($line, 6); // Remove "data: " prefix
                        
                        if (trim($jsonData) === '[DONE]') {
                            break;
                        }

                        try {
                            $decoded = json_decode($jsonData, true);
                            
                            if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
                                $text = $decoded['candidates'][0]['content']['parts'][0]['text'];
                                yield $text;
                            }
                        } catch (\Exception $e) {
                            // Skip malformed JSON chunks
                            continue;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
            yield "I apologize, but I'm having trouble connecting to my AI service. Please try again in a moment.";
        }
    }

    /**
     * Build context-aware system prompt
     */
    private function buildSystemPrompt(array $context): string
    {
        $prompt = "You are a helpful AI assistant for iCorp Pro, a company that provides business incorporation services.\n\n";
        
        // Add page context
        if (isset($context['page'])) {
            $prompt .= "Current Page: {$context['page']}\n";
        }
        
        if (isset($context['page_type'])) {
            switch ($context['page_type']) {
                case 'service_c_corp':
                    $prompt .= "Context: User is viewing C-Corporation service page. Focus on C-Corp benefits, requirements, and pricing.\n";
                    break;
                case 'service_s_corp':
                    $prompt .= "Context: User is viewing S-Corporation service page. Focus on S-Corp benefits, tax advantages, and requirements.\n";
                    break;
                case 'service_llc':
                    $prompt .= "Context: User is viewing LLC service page. Focus on LLC benefits, flexibility, and formation process.\n";
                    break;
                case 'service_nonprofit':
                    $prompt .= "Context: User is viewing Nonprofit organization service page. Focus on 501(c)(3) status, benefits, and requirements.\n";
                    break;
                case 'service_greencard':
                    $prompt .= "Context: User is viewing Green Card Lottery service page. Focus on immigration services and requirements.\n";
                    break;
                case 'pricing':
                    $prompt .= "Context: User is viewing pricing page. Focus on package options, pricing details, and value.\n";
                    break;
                case 'dashboard':
                    $prompt .= "Context: User is in their client dashboard. You can help with order status, documents, and account questions.\n";
                    break;
            }
        }

        // Add user context
        if (isset($context['user'])) {
            $user = $context['user'];
            $prompt .= "\nUser Information:\n";
            $prompt .= "- Name: {$user['name']}\n";
            $prompt .= "- Email: {$user['email']}\n";
            
            if (isset($user['is_admin']) && $user['is_admin']) {
                $prompt .= "- Role: Administrator\n";
            }
        }

        $prompt .= "\nCompany Services:\n";
        $prompt .= "- C-Corporation Formation\n";
        $prompt .= "- S-Corporation Formation\n";
        $prompt .= "- LLC Formation\n";
        $prompt .= "- Nonprofit Organization (501c3)\n";
        $prompt .= "- Green Card Lottery Assistance\n";
        $prompt .= "- Registered Agent Services\n";
        $prompt .= "- EIN Filing\n";
        $prompt .= "- Operating Agreements\n";
        
        $prompt .= "\nInstructions:\n";
        $prompt .= "- Be helpful, professional, and concise\n";
        $prompt .= "- Provide accurate information about our services\n";
        $prompt .= "- If you don't know something, recommend contacting support\n";
        $prompt .= "- Use the page context to give relevant answers\n";
        $prompt .= "- For pricing questions, mention viewing our pricing page\n";
        $prompt .= "- For complex questions, suggest scheduling a consultation\n";

        return $prompt;
    }

    /**
     * Get non-streaming response (for simple queries)
     */
    public function chat(string $message, array $context = []): string
    {
        $systemPrompt = $this->buildSystemPrompt($context);
        
        $url = "{$this->baseUrl}/models/gemini-1.5-flash:generateContent?key={$this->apiKey}";
        
        try {
            $response = Http::timeout(30)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $systemPrompt . "\n\nUser Question: " . $message]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 2048,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response generated.';
            }

            return "I apologize, but I'm having trouble processing your request. Please try again.";
        } catch (\Exception $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
            return "I apologize, but I'm having trouble connecting. Please try again in a moment.";
        }
    }
}
