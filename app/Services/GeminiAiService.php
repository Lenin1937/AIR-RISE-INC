<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAiService
{
    private string $apiKey;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
    private string $model = 'gemini-1.5-pro-latest'; // Updated to valid model
    
    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Stream response from Gemini API
     * Yields JSON chunks for frontend consumption
     */
    public function streamResponse(array $messages, string $pageContext): \Generator
    {
        $systemPrompt = $this->buildSystemPrompt($pageContext);
        
        // Prepare conversation history for Gemini
        $contents = [];
        
        // Add system prompt as first user message
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $systemPrompt]]
        ];
        
        // Add conversation history
        foreach ($messages as $message) {
            $role = $message['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $message['content']]]
            ];
        }

        $url = "{$this->baseUrl}/models/{$this->model}:streamGenerateContent?alt=sse&key={$this->apiKey}";
        
        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
            ]
        ];

        try {
            yield json_encode(['type' => 'start', 'data' => 'Connecting to AI...']) . "\n";

            // Use Guzzle for streaming
            $client = new \GuzzleHttp\Client(['timeout' => 30]);
            $response = $client->post($url, [
                'json' => $payload,
                'stream' => true,
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $body = $response->getBody();
            $buffer = '';
            
            while (!$body->eof()) {
                $chunk = $body->read(1024);
                $buffer .= $chunk;

                // Process complete SSE messages
                while (($pos = strpos($buffer, "\n\n")) !== false) {
                    $message = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 2);

                    // Parse SSE format
                    if (strpos($message, 'data: ') === 0) {
                        $jsonData = trim(substr($message, 6));
                        
                        if ($jsonData === '[DONE]' || empty($jsonData)) {
                            continue;
                        }

                        try {
                            $decoded = json_decode($jsonData, true);
                            
                            if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
                                $text = $decoded['candidates'][0]['content']['parts'][0]['text'];
                                
                                // Yield as JSON for frontend
                                yield json_encode([
                                    'type' => 'content',
                                    'data' => $text
                                ]) . "\n";
                            }
                            
                            // Check if generation is complete
                            if (isset($decoded['candidates'][0]['finishReason'])) {
                                yield json_encode([
                                    'type' => 'done',
                                    'data' => 'Stream complete'
                                ]) . "\n";
                                break;
                            }
                        } catch (\Exception $e) {
                            Log::warning('Failed to parse Gemini SSE chunk', [
                                'error' => $e->getMessage(),
                                'data' => $jsonData
                            ]);
                        }
                    }
                }
            }

            yield json_encode(['type' => 'end', 'data' => '']) . "\n";

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Gemini API Request Error', [
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            
            yield json_encode([
                'type' => 'error',
                'data' => 'I apologize, but I\'m having trouble connecting to my AI service. Please try again in a moment.'
            ]) . "\n";
            
        } catch (\Exception $e) {
            Log::error('Gemini API Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            yield json_encode([
                'type' => 'error',
                'data' => 'An unexpected error occurred. Please try again.'
            ]) . "\n";
        }
    }

    /**
     * Build system prompt based on page context
     */
    private function buildSystemPrompt(string $pageContext): string
    {
        $basePrompt = "You are a helpful AI assistant for CORPIUS, a professional business incorporation and services company.\n\n";
        
        $basePrompt .= "=== COMPANY SERVICES ===\n";
        $basePrompt .= "1. **C-Corporation Formation** - $299+\n";
        $basePrompt .= "   - Unlimited shareholders, separate legal entity\n";
        $basePrompt .= "   - Best for: Large businesses planning to go public\n\n";
        
        $basePrompt .= "2. **S-Corporation Formation** - $299+\n";
        $basePrompt .= "   - Pass-through taxation, avoid double taxation\n";
        $basePrompt .= "   - Best for: Small to medium businesses\n\n";
        
        $basePrompt .= "3. **LLC Formation** - $149+\n";
        $basePrompt .= "   - Simple structure, flexible management\n";
        $basePrompt .= "   - Best for: Small businesses, freelancers, startups\n\n";
        
        $basePrompt .= "4. **Nonprofit 501(c)(3)** - $399+\n";
        $basePrompt .= "   - Tax-exempt status, eligible for grants\n";
        $basePrompt .= "   - Best for: Charitable organizations\n\n";
        
        $basePrompt .= "5. **Green Card Lottery Assistance** - $199+\n";
        $basePrompt .= "   - Expert application review and tracking\n\n";
        
        $basePrompt .= "6. **Additional Services**\n";
        $basePrompt .= "   - Registered Agent Services\n";
        $basePrompt .= "   - EIN Filing\n";
        $basePrompt .= "   - Operating Agreements\n";
        $basePrompt .= "   - Business Licenses\n\n";

        // Add context-specific information
        $basePrompt .= "=== CURRENT PAGE CONTEXT ===\n";
        $basePrompt .= $pageContext . "\n\n";

        $basePrompt .= "=== INSTRUCTIONS ===\n";
        $basePrompt .= "- Be professional, friendly, and concise\n";
        $basePrompt .= "- Provide accurate information about our services\n";
        $basePrompt .= "- Use the page context to give relevant, targeted answers\n";
        $basePrompt .= "- If you don't know something specific, recommend contacting support\n";
        $basePrompt .= "- For pricing details, mention viewing our pricing page\n";
        $basePrompt .= "- For complex legal questions, suggest scheduling a free consultation\n";
        $basePrompt .= "- Keep responses under 150 words unless more detail is specifically requested\n";
        $basePrompt .= "- Use markdown formatting for better readability\n";
        $basePrompt .= "- If a user seems ready to purchase, encourage them to get started\n\n";

        return $basePrompt;
    }

    /**
     * Generate a full SEO-optimised blog post using Gemini.
     * Returns ['success' => bool, 'data' => array] or ['success' => false, 'error' => string]
     */
    public function generateBlogPost(string $topic, string $category = 'General', string $tone = 'professional'): array
    {
        $prompt = <<<PROMPT
You are a professional content writer for CORPIUS, a US business formation and legal services company (corpius.net).

Write a complete, high-quality SEO-optimised blog post on the following topic.

Topic: {$topic}
Category: {$category}
Tone: {$tone}

Return ONLY a valid JSON object — no markdown, no code fences, no extra text — with exactly these fields:
{
  "title":            "SEO-optimised blog post title",
  "excerpt":          "2-3 sentence summary for listing pages (max 300 chars)",
  "content":          "Full HTML content using <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>. Minimum 800 words. Conclude with a CTA mentioning CORPIUS.",
  "tags":             ["tag1","tag2","tag3","tag4","tag5"],
  "category":         "Category name matching: General | Business Formation | Tax & Compliance | Getting Started | Immigration | News & Updates",
  "meta_title":       "SEO meta title, max 60 chars",
  "meta_description": "SEO meta description, max 155 chars",
  "read_time":        5
}

Content guidelines:
- 800-1200 words of practical, valuable information
- Proper HTML heading hierarchy (h2 for sections, h3 for sub-sections)
- Include intro, 3-5 main sections, conclusion with CTA to corpius.net
- read_time = ceil(word_count / 200)
- Focus on US business, legal, tax, or immigration topics relevant to CORPIUS
PROMPT;

        $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

        try {
            $response = Http::timeout(60)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature'     => 0.7,
                    'topK'            => 40,
                    'topP'            => 0.95,
                    'maxOutputTokens' => 4096,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

                // Strip markdown code fences if Gemini adds them
                $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
                $text = preg_replace('/```\s*$/m', '', $text);
                $text = trim($text);

                $parsed = json_decode($text, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($parsed['title'], $parsed['content'])) {
                    return ['success' => true, 'data' => $parsed];
                }

                Log::warning('Blog AI: Could not parse JSON from Gemini', ['raw' => substr($text, 0, 500)]);
                return ['success' => false, 'error' => 'AI returned an invalid response. Please try again.'];
            }

            Log::error('Blog AI: Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
            return ['success' => false, 'error' => 'AI service error (' . $response->status() . '). Please try again.'];

        } catch (\Exception $e) {
            Log::error('Blog AI: Exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Failed to reach AI service. Please try again.'];
        }
    }

    /**
     * Get non-streaming response (for simple queries or testing)
     */
    public function chat(string $message, string $pageContext): string
    {
        $systemPrompt = $this->buildSystemPrompt($pageContext);
        
        $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";
        
        try {
            $response = Http::timeout(30)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $systemPrompt . "\n\nUser: " . $message]
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

            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return "I apologize, but I'm having trouble processing your request. Please try again.";
            
        } catch (\Exception $e) {
            Log::error('Gemini API Exception', [
                'error' => $e->getMessage()
            ]);
            
            return "I apologize, but I'm having trouble connecting. Please try again in a moment.";
        }
    }
}
