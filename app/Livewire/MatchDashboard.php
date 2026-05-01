<?php

namespace App\Livewire;

use App\Services\AiSummarizerService;
use App\Services\FootballApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class MatchDashboard extends Component
{
    public $matchList = [];
    public $selectedMatch = null;
    public $matchStats = [];
    public $parsedStats = []; 
    public $aiSummary;
    public $errorMessage;

    public $topPlayers = [];
    public $injuries = [];

    //generative AI
    public $chatHistory = [];
    public $userQuestion = '';

    // this function automatically run when the first page of web is loaded. 
    public function mount(){
        $footballApi = app(FootballApiService::class);
        $this->matchList = $footballApi->getRecentMatches();
    }

    // It runs instantly within milliseconds when the button is clicked. 
    public function selectMatch($fixtureId, $teamA, $teamB){
        //  Automatically reset the old data and set UI into detail mode.
        $this->reset(['matchStats', 'aiSummary', 'errorMessage','lineups','events', 'topPlayers', 'injuries', 'chatHistory', 'userQuestion']);
        $this->selectedMatch = [
            'id' => $fixtureId,
            'title' => "$teamA vs $teamB",
            'teamA' => $teamA,
            'teamB' => $teamB
        ];
    }

    public $lineups = [];
    public $events = [];

    // A function that runs automatically in the background after UI changes.
    public function generateAnalysis()
    {
        if (!$this->selectedMatch) return;

        $footballApi = app(FootballApiService::class);
        $aiService = app(AiSummarizerService::class);
        $fixtureId = $this->selectedMatch['id'];

        $teamA = $this->selectedMatch['teamA'];
        $teamB = $this->selectedMatch['teamB'];

        // Tarik data lama (sudah ada)
        $this->matchStats = Cache::remember("stats_{$fixtureId}", 86400, fn() => $footballApi->getMatchStatistics($fixtureId)['response'] ?? []);
        $this->lineups = Cache::remember("lineups_{$fixtureId}", 86400, fn() => $footballApi->getMatchLineups($fixtureId));
        $this->events = Cache::remember("events_{$fixtureId}", 86400, fn() => $footballApi->getMatchEvents($fixtureId));
        
        
        
        // Fetch new data with Cache
        $playerStats = Cache::remember("players_{$fixtureId}", 86400, fn() => $footballApi->getPlayerStatistics($fixtureId));
        $this->injuries = Cache::remember("injuries_{$fixtureId}", 86400, fn() => $footballApi->getMatchInjuries($fixtureId));

        // logic for finding the top 3 best players (Man of the Match)
        $allPlayers = [];
        foreach ($playerStats as $teamData) {
            $teamName = $teamData['team']['name'];
            foreach ($teamData['players'] as $p) {
                $stats = $p['statistics'][0] ?? null;
                //  Ensure the player has a rating before being included
                if ($stats && isset($stats['games']['rating'])) {
                    $allPlayers[] = [
                        'name' => $p['player']['name'],
                        'photo' => $p['player']['photo'],
                        'team' => $teamName,
                        'rating' => (float) $stats['games']['rating'],
                        'pos' => $stats['games']['position'] ?? 'N/A'
                    ];
                }
            }
        }

        //  Sort by the highest to lowest rating 
        usort($allPlayers, function($a, $b) {
            return $b['rating'] <=> $a['rating'];
        });

        //  Fetching Only 3 Top players 
        $this->topPlayers = array_slice($allPlayers, 0, 3);

       

        if (!empty($this->matchStats) && count($this->matchStats) >= 2) {
            $statsA = $this->matchStats[0]['statistics'];
            $statsB = $this->matchStats[1]['statistics'];

            $this->parsedStats = $this->formatStatsForUI($statsA, $statsB);
            
            $this->selectedMatch['teamA_playstyle'] = $this->getPlaystyleFromAI($statsA);
            $this->selectedMatch['teamB_playstyle'] = $this->getPlaystyleFromAI($statsB);
            
            $this->aiSummary = Cache::remember("ai_summary_{$fixtureId}", 86400, function () use ($aiService, $teamA, $teamB, $statsA, $statsB) {
                return $aiService->generateTacticalSummary($teamA, $teamB, $statsA, $statsB);
            });
        } else {
            $this->errorMessage = "Sorry, detailed tactical statistics are not available for this specific match.";
        }
    }

    private function formatStatsForUI($statsA, $statsB)
    {
        $formatted = [];
        
        foreach($statsA as $statA) {
            // only take the top 5 data entries returned by the API so the UI design remains neat.
            if(count($formatted) >= 5) break;

            $type = $statA['type'];
            $valA = $statA['value'];
            
            // Find the corresponding data in Team B
            $valB = null;
            foreach($statsB as $statB) {
                if($statB['type'] === $type) {
                    $valB = $statB['value'];
                    break;
                }
            }

            // if API had responds with null, just skip it
            if($valA === null && $valB === null) continue;

            //  Clean the number by removing '%' symbol so its percentage can be calculated.
            $cleanA = (int) preg_replace('/[^0-9]/', '', (string)$valA);
            $cleanB = (int) preg_replace('/[^0-9]/', '', (string)$valB);

            // Count the percentage for the blue and red bar widths in the UI
            $total = $cleanA + $cleanB;
            $pctA = $total > 0 ? round(($cleanA / $total) * 100) : 50;
            $pctB = $total > 0 ? round(($cleanB / $total) * 100) : 50;

            $formatted[] = [
                'label' => $type,
                'valA' => $valA ?? '0',
                'valB' => $valB ?? '0',
                'pctA' => $pctA,
                'pctB' => $pctB,
            ];
        }

        return $formatted;
    }

    public function backToList(){
        $this->reset(['selectedMatch', 'matchStats', 'parsedStats', 'aiSummary', 'errorMessage', 'lineups', 'events']);
    }

    public function render()
    {
        return view('livewire.match-dashboard');
    }

    // function to send data to FASTAPI
    private function getPlaystyleFromAI($stats){
        // Default value if data from API-Sports is empty
        $possession = 50.0; $passes = 300.0; $shots = 10.0; $fouls = 10.0;

        // extract the data from API-sports array
        if (is_array($stats)) {
            foreach ($stats as $stat) {
                $type = strtolower($stat['type'] ?? '');
                $value = $stat['value'] ?? null;

                if ($type === 'ball possession' && $value !== null) {
                    $possession = (float) preg_replace('/[^0-9.]/', '', (string)$value);
                }
                if ($type === 'total passes' && $value !== null) {
                    $passes = (float) preg_replace('/[^0-9.]/', '', (string)$value);
                }
                if ($type === 'total shots' && $value !== null) {
                    $shots = (float) preg_replace('/[^0-9.]/', '', (string)$value);
                }
                if ($type === 'fouls' && $value !== null) {
                    $fouls = (float) preg_replace('/[^0-9.]/', '', (string)$value);
                }
            }
        }

        try{
            // send to python's server  
            $response = Http::timeout(5)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])
            
            ->post('http://127.0.0.1:8000/predict-playstyle', [
                'possession' => (float) $possession,
                'passes' => (float) $passes,
                'shots' => (float) $shots,
                'fouls' => (float) $fouls
            ]);

            if ($response->successful()){
                return $response->json('playstyle') ?? "Format is Wrong AI";
            }

            return "Error 422: " . $response->body();
        }catch (\Exception $e){
            return "Connection Fail: " . substr($e->getMessage(),0, 40) . "...";
        }
        
        
    }

    // gen AI fitur
    public function askAssistant(){

        // validation empty input
        if (trim($this->userQuestion) === '' || empty($this->parsedStats)){
            return;
        }

        // save user's questions to history UI
        $currentQuestion = $this->userQuestion;
        $this->chatHistory[] = ['sender' => 'user', 'text' => $currentQuestion];
        $this->userQuestion = ''; 

        try{
            // Endpoint LangChain FastAPI
            $response = Http::timeout(15)
                ->asJson()
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->post('http://127.0.0.1:8000/chat-tactical', [
                    'question' => $currentQuestion,
                    'match_title' => $this->selectedMatch['title'] ?? 'Match',
                    'stats_context' => $this->parsedStats // statistic data RAG

                ]);

                if ($response->successful()){
                    $answer = $response->json('answer');
                    $this->chatHistory[] = ['sender' => 'ai', 'text' => $answer];
                }else {
                    $this->chatHistory[] = ['sender' => 'ai', 'text' => "Error 422 Detail: " . $response->body()];
                }
        }catch (\Exception $e){
            $this->chatHistory[] = ['sender' => 'ai', 'text' => "Connection to the tactical assistant was lost."];
        }

    }
}