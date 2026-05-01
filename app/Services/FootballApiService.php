<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FootballApiService
{
    protected $baseUrl = 'https://v3.football.api-sports.io';

    // Fetching the last 10 matches Champions League
    public function getRecentMatches(){
        $response = Http::withoutVerifying()->withHeaders([
            'x-apisports-key' => env('API_SPORTS_KEY'),
        ])->get("{$this->baseUrl}/fixtures", [
            'league' => 2,
            'season' => 2024,
        ]);
           
        $allMatches = $response->json('response') ?? [];

        // Fetching Only Match had finished status
        $finishedMatches = array_filter($allMatches, function($match){
            $status = $match['fixture']['status']['short'];
            return in_array($status, ['FT', 'AET', 'PEN']);
        });

        // Sort by the most recent match date.
        usort($finishedMatches, function($a, $b){
            return strtotime($b['fixture']['date']) - strtotime($a['fixture']['date']);

        });

        return array_slice($finishedMatches, 0 , 10);
    }

    public function getMatchStatistics($fixtureId)
    {
        
        $response = Http::withoutVerifying()->withHeaders([
            'x-apisports-key' => env('API_SPORTS_KEY'),
        ])->get("{$this->baseUrl}/fixtures/statistics", [
            'fixture' => $fixtureId
        ]);

        return $response->json(); // Return ALL data (including error messages, if any).
    }

    // Fetching the team lineup
    public function getMatchLineups($fixtureId){
        $response = Http::withoutVerifying()->withHeaders([
            'x-apisports-key' => env('API_SPORTS_KEY'),
        ])->get("{$this->baseUrl}/fixtures/lineups", ['fixture' => $fixtureId]);

        return $response->json('response') ?? [];
    }

    // Fetching match event data.
    public function getMatchEvents($fixtureId){
        $response = Http::withoutVerifying()->withHeaders([
            'x-apisports-key' => env('API_SPORTS_KEY'),
        ])->get("{$this->baseUrl}/fixtures/events", ['fixture' => $fixtureId]);

        return $response->json('response') ?? [];
    }

    // Fetching the statistic every single player
    public function getPlayerStatistics($fixtureId){
        $response = Http::withoutVerifying()->withHeaders([
            'x-apisports-key' => env('API_SPORTS_KEY'),
        ])->get("{$this->baseUrl}/fixtures/players", ['fixture' => $fixtureId]);

        return $response->json('response') ?? [];
    }

    // Fetching the list of injured players.
    public function getMatchInjuries($fixtureId){
        $response = Http::withoutVerifying()->withHeaders([
            'x-apisports-key' => env('API_SPORTS_KEY'),
        ])->get("{$this->baseUrl}/injuries", ['fixture' => $fixtureId]);

        return $response->json('response') ?? [];
    }
}