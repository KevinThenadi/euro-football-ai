<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiSummarizerService
{
    public function generateTacticalSummary($teamA, $teamB, $statsA, $statsB)
    {
        $prompt = "You are a professional football tactician. 
        Here are the match statistics between {$teamA} vs {$teamB}.
        {$teamA} Stats: " . json_encode($statsA) . "
        {$teamB} Stats: " . json_encode($statsB) . "
        
        Task: Provide a VERY SHORT, punchy tactical insight (maximum 3 sentences). Do not read all the numbers back to me. Just tell me the decisive tactical reason why the match ended the way it did (e.g., 'Inter's low block frustrated Barcelona, using lethal counter-attacks...'). Use professional English.";

        try {
            $response = Http::withoutVerifying()->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . env('GEMINI_API_KEY'), [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text');
            }

            
            if ($response->status() === 503) {
                return "We apologize, the AI server is currently very busy handling a large number of requests worldwide. Please try again in a few minutes.";
            }

            // For another Error
            return "Failed to generate summary. API Error: " . $response->json('error.message') ?? 'Unknown Error';

        } catch (\Exception $e) {
            return "Connection Ai Server Error: " . $e->getMessage();
        }
    }
}