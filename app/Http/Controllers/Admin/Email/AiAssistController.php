<?php

namespace App\Http\Controllers\Admin\Email;

use App\Http\Controllers\Controller;
use App\Services\GeminiAiService;
use App\Services\OpenAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiAssistController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'mode'      => 'required|in:draft,improve,shorten,expand,translate,subjects,sequence,compliance',
            'input'     => 'required|string|max:5000',
            'language'  => 'nullable|string|max:5',
            'tone'      => 'nullable|in:formal,friendly,urgent',
            'target'    => 'nullable|string|max:100', // for sequence: client type / funnel goal
        ]);

        $mode     = $request->mode;
        $input    = $request->input;
        $language = $request->language ?? 'en';
        $tone     = $request->tone ?? 'formal';

        $prompt = match ($mode) {
            'draft' => "You are an email copywriter for CORPIUS, a US business formation company (LLC, C-Corp, S-Corp, Nonprofit, Tax Filing, Green Card). Write a professional email with subject line and body. Purpose: \"{$input}\". Language: {$language}. Tone: {$tone}. Use placeholders {{FirstName}}, {{EntityType}}, {{OrderNumber}} where appropriate. Format response as JSON: {\"subject\": \"...\", \"preheader\": \"...\", \"body\": \"<html email body>\"}",

            'improve' => "Rewrite the following email text to be more professional and effective for a business formation service company called CORPIUS. Tone: {$tone}. Return only the improved text:\n\n{$input}",

            'shorten' => "Shorten the following email to be more concise and impactful, targeting business owners. Return only the shortened version:\n\n{$input}",

            'expand' => "Expand the following email into a more detailed, informative email for CORPIUS business formation clients. Return only the expanded version:\n\n{$input}",

            'translate' => "Translate the following email text to {$language}, preserving all {{variable}} placeholders exactly as-is. Return only the translated text:\n\n{$input}",

            'subjects' => "Generate 8 email subject line variants for the following email body. Label each with a tone tag: [high-urgency], [educational], [friendly], [formal], [question], [benefit-focused]. Return as JSON array: [{\"subject\": \"...\", \"tone\": \"...\"}]\n\nEmail body:\n{$input}",

            'sequence' => "Create a 5-email onboarding sequence for CORPIUS business formation clients. Target: \"{$input}\". For each email provide: day (0,2,5,10,20), subject, preheader, and brief body outline. Return as JSON array: [{\"day\": 0, \"subject\": \"...\", \"preheader\": \"...\", \"body_outline\": \"...\"}]",

            'compliance' => "Review the following marketing email for: 1) overly aggressive tone, 2) legally risky promises, 3) excessive guarantees, 4) CAN-SPAM compliance issues. Return as JSON: {\"issues\": [{\"type\": \"...\", \"text\": \"...\", \"suggestion\": \"...\"}], \"score\": 1-10, \"summary\": \"...\"}:\n\n{$input}",

            default => $input,
        };

        try {
            $aiService = app(OpenAiService::class);
            $result    = $aiService->chat($prompt, 'email-marketing');

            if (!$result) {
                $aiService = app(GeminiAiService::class);
                $result    = $aiService->chat($prompt, 'email-marketing');
            }

            return response()->json(['success' => true, 'result' => $result, 'mode' => $mode]);
        } catch (\Exception $e) {
            Log::error('AI Assist error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'AI service unavailable. Please try again.'], 500);
        }
    }
}
