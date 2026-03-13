<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;
    
    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->baseUrl = rtrim(config('services.openai.base_url', 'https://api.openai.com/v1'), '/');
        $this->model = config('services.openai.model', 'gpt-4o-mini');
    }

    /**
     * Stream response from OpenAI API
     * Yields JSON chunks for frontend consumption
     */
    public function streamResponse(array $messages, string $pageContext): \Generator
    {
        $systemPrompt = $this->buildSystemPrompt($pageContext);
        
        // Prepare conversation history for OpenAI
        $apiMessages = [];
        
        // Add system prompt
        $apiMessages[] = [
            'role' => 'system',
            'content' => $systemPrompt
        ];
        
        // Add conversation history (filter out system messages from DB)
        foreach ($messages as $message) {
            if ($message['role'] !== 'system') {
                $apiMessages[] = [
                    'role' => $message['role'],
                    'content' => $message['content']
                ];
            }
        }

        $url = "{$this->baseUrl}/chat/completions";
        
        $payload = [
            'model' => $this->model,
            'messages' => $apiMessages,
            'temperature' => 0.7,
            'max_tokens' => 2048,
            'stream' => true,
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
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ]
            ]);

            $body = $response->getBody();
            $buffer = '';
            
            while (!$body->eof()) {
                $chunk = $body->read(1024);
                $buffer .= $chunk;

                // Process complete SSE messages
                $lines = explode("\n", $buffer);
                $buffer = array_pop($lines); // Keep incomplete line in buffer

                foreach ($lines as $line) {
                    $line = trim($line);
                    
                    if (empty($line)) {
                        continue;
                    }

                    // Parse SSE format
                    if (strpos($line, 'data: ') === 0) {
                        $jsonData = trim(substr($line, 6));
                        
                        if ($jsonData === '[DONE]') {
                            yield json_encode([
                                'type' => 'done',
                                'data' => 'Stream complete'
                            ]) . "\n";
                            break 2;
                        }

                        try {
                            $decoded = json_decode($jsonData, true);
                            
                            if (isset($decoded['choices'][0]['delta']['content'])) {
                                $text = $decoded['choices'][0]['delta']['content'];
                                
                                // Yield as JSON for frontend
                                yield json_encode([
                                    'type' => 'content',
                                    'data' => $text
                                ]) . "\n";
                            }
                            
                            // Check if generation is complete
                            if (isset($decoded['choices'][0]['finish_reason']) && 
                                $decoded['choices'][0]['finish_reason'] !== null) {
                                yield json_encode([
                                    'type' => 'done',
                                    'data' => 'Stream complete'
                                ]) . "\n";
                                break 2;
                            }
                        } catch (\Exception $e) {
                            Log::warning('Failed to parse OpenAI SSE chunk', [
                                'error' => $e->getMessage(),
                                'data' => $jsonData
                            ]);
                        }
                    }
                }
            }

            yield json_encode(['type' => 'end', 'data' => '']) . "\n";

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('OpenAI API Request Error', [
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            
            yield json_encode([
                'type' => 'error',
                'data' => 'I apologize, but I\'m having trouble connecting to my AI service. Please try again in a moment.'
            ]) . "\n";
            
        } catch (\Exception $e) {
            Log::error('OpenAI API Error', [
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
        $basePrompt .= "1. C-Corporation Formation - $299+\n";
        $basePrompt .= "   - Unlimited shareholders, separate legal entity\n";
        $basePrompt .= "   - Best for: Large businesses planning to go public\n\n";
        
        $basePrompt .= "2. S-Corporation Formation - $299+\n";
        $basePrompt .= "   - Pass-through taxation, avoid double taxation\n";
        $basePrompt .= "   - Best for: Small to medium businesses\n\n";
        
        $basePrompt .= "3. LLC Formation - $149+\n";
        $basePrompt .= "   - Simple structure, flexible management\n";
        $basePrompt .= "   - Best for: Small businesses, freelancers, startups\n\n";
        
        $basePrompt .= "4. Nonprofit 501(c)(3) - $399+\n";
        $basePrompt .= "   - Tax-exempt status, eligible for grants\n";
        $basePrompt .= "   - Best for: Charitable organizations\n\n";
        
        $basePrompt .= "5. Green Card Lottery Assistance - $199+\n";
        $basePrompt .= "   - Expert application review and tracking\n\n";
        
        $basePrompt .= "6. Additional Services\n";
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
        $basePrompt .= "- Do NOT use markdown formatting, asterisks, bold, or any special symbols. Write plain text only.\n";
        $basePrompt .= "- If a user seems ready to purchase, encourage them to get started\n\n";

        return $basePrompt;
    }

    /**
     * Generate a full SEO-optimised blog post.
     * Returns ['success' => bool, 'data' => array] or ['success' => false, 'error' => string]
     */
    public function generateBlogPost(string $topic, string $category = 'General', string $tone = 'professional'): array
    {
        $prompt = <<<PROMPT
You are a professional content writer for CORPIUS (corpius.net), a US business formation and legal services company.

Write a complete SEO-optimised blog post on this topic: {$topic}
Category: {$category}
Tone: {$tone}

Output ONLY a valid JSON object with no extra text or code fences:
{"title":"...","excerpt":"2-3 sentence summary max 300 chars","content":"Full HTML using h2 h3 p ul ol li strong em tags, minimum 600 words, ending with a CTA mentioning CORPIUS","tags":["tag1","tag2","tag3"],"category":"{$category}","meta_title":"max 60 chars","meta_description":"max 155 chars","read_time":5}
PROMPT;

        $url = "{$this->baseUrl}/chat/completions";

        try {
            $response = Http::timeout(120)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post($url, [
                    'model'       => $this->model,
                    'messages'    => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens'  => 8192,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $msg  = $data['choices'][0]['message'] ?? [];

                // Reasoning models (e.g. kimi-k2.5) put the final answer in
                // 'content'; reasoning steps go into 'reasoning_content'.
                $text = $msg['content'] ?? $msg['reasoning_content'] ?? '';

                // Strip markdown code fences if present
                $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
                $text = preg_replace('/```\s*$/m', '', $text);
                $text = trim($text);

                // Some reasoning models wrap the JSON in <answer>…</answer>
                if (preg_match('/<answer>(.*?)<\/answer>/s', $text, $m)) {
                    $text = trim($m[1]);
                }

                $parsed = json_decode($text, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($parsed['title'], $parsed['content'])) {
                    return ['success' => true, 'data' => $parsed];
                }

                Log::warning('Blog AI: Could not parse JSON from OpenAI', [
                    'content_len'   => strlen($msg['content'] ?? ''),
                    'reasoning_len' => strlen($msg['reasoning_content'] ?? ''),
                    'raw'           => substr($text, 0, 500),
                ]);
                return ['success' => false, 'error' => 'AI returned an invalid response. Please try again.'];
            }

            Log::error('Blog AI: OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
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
        
        $url = "{$this->baseUrl}/chat/completions";
        
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt
                        ],
                        [
                            'role' => 'user',
                            'content' => $message
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 2048,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? 'No response generated.';
            }

            Log::error('OpenAI API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return "I apologize, but I'm having trouble processing your request. Please try again.";
            
        } catch (\Exception $e) {
            Log::error('OpenAI API Exception', [
                'error' => $e->getMessage()
            ]);
            
            return "I apologize, but I'm having trouble connecting. Please try again in a moment.";
        }
    }
}
