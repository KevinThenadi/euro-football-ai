<div class="max-w-6xl mx-auto p-6 mt-8">
    
    <div class="text-center mb-10">
        <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight">UEFA Champions League AI</h2>
        <p class="text-gray-500 mt-2">Select a recent match to generate tactical insights using AI.</p>
    </div>

    @if(!$selectedMatch)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($matchList as $match)
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200 hover:shadow-xl transition duration-300">
                    <div class="flex justify-between items-center mb-4 text-xs text-gray-500 font-bold uppercase tracking-wider">
                        <span>{{ \Carbon\Carbon::parse($match['fixture']['date'])->format('d M Y') }}</span>
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded">Finished</span>
                    </div>
                    
                    <div class="flex justify-between items-center mb-6">
                        <div class="text-center flex-1">
                            <img src="{{ $match['teams']['home']['logo'] }}" alt="Home Logo" class="w-14 h-14 mx-auto mb-3 object-contain">
                            <p class="font-bold text-gray-800 leading-tight">{{ $match['teams']['home']['name'] }}</p>
                        </div>
                        
                        <div class="px-4 text-3xl font-black text-blue-900">
                            {{ $match['goals']['home'] }} - {{ $match['goals']['away'] }}
                        </div>
                        
                        <div class="text-center flex-1">
                            <img src="{{ $match['teams']['away']['logo'] }}" alt="Away Logo" class="w-14 h-14 mx-auto mb-3 object-contain">
                            <p class="font-bold text-gray-800 leading-tight">{{ $match['teams']['away']['name'] }}</p>
                        </div>
                    </div>

                    <button 
                        wire:click="selectMatch({{ $match['fixture']['id'] }}, '{{ $match['teams']['home']['name'] }}', '{{ $match['teams']['away']['name'] }}')" 
                        class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Analyze Tactics
                    </button>
                </div>
            @empty
                <div class="col-span-3 text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
                    <p class="text-gray-500 text-lg">Loading matches or daily API limit has been reached...</p>
                </div>
            @endforelse
        </div>
    @endif

    @if($selectedMatch)
        <div wire:init="generateAnalysis" class="bg-white rounded-2xl shadow-2xl p-8 border border-gray-200 relative">
            
            <button wire:click="backToList" class="absolute top-6 left-6 text-gray-500 hover:text-blue-600 font-semibold flex items-center gap-2 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Matches
            </button>

            <div class="text-center mt-6 mb-10 border-b border-gray-100 pb-6">
                <h3 class="text-4xl font-black text-gray-800">{{ $selectedMatch['title'] }}</h3>
                <p class="text-blue-600 font-medium mt-2">AI Tactical Post-Match Review</p>
            </div>

            <div wire:loading wire:target="generateAnalysis" class="w-full text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-4 border-blue-600 mb-4"></div>
                <p class="text-blue-800 font-bold text-lg animate-pulse">AI is reading the statistics and writing the summary...</p>
            </div>

            <div wire:loading.remove wire:target="generateAnalysis">
                
                @if($errorMessage)
                    <div class="bg-red-50 text-red-600 p-5 rounded-xl mb-6 border border-red-200 flex items-start gap-3">
                        <svg class="w-6 h-6 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="font-medium">{{ $errorMessage }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 bg-gray-50 p-6 rounded-2xl border border-gray-100">
                        <h4 class="text-xl font-bold text-gray-800 mb-6 text-center tracking-wide uppercase">Head-to-Head Stats</h4>
                        <div class="space-y-6">
                            @foreach($parsedStats as $stat)
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-lg font-bold text-blue-700">{{ $stat['valA'] }}</span>
                                        <span class="text-sm font-semibold text-gray-500 uppercase">{{ $stat['label'] }}</span>
                                        <span class="text-lg font-bold text-red-600">{{ $stat['valB'] }}</span>
                                    </div>
                                    <div class="flex w-full h-3 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="bg-blue-600 h-full" style="width: {{ $stat['pctA'] }}%;"></div>
                                        <div class="bg-red-500 h-full" style="width: {{ $stat['pctB'] }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-gradient-to-b from-blue-900 to-indigo-900 p-6 rounded-2xl text-white shadow-xl h-full">
                            <div class="flex items-center gap-3 mb-4 border-b border-blue-700 pb-4">
                                <svg class="w-8 h-8 text-yellow-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <h4 class="font-bold text-xl">AI Verdict</h4>
                            </div>
                            @if($aiSummary)
                                <p class="text-blue-100 text-lg leading-relaxed font-light italic">"{{ $aiSummary }}"</p>
                            @else
                                <p class="text-gray-400 italic">No summary generated.</p>
                            @endif
                        </div>
                    </div>
                    
                </div>

               
                <div class="mt-12 bg-white rounded-2xl border border-gray-200 shadow-xl flex flex-col h-[550px] w-full overflow-hidden">
                    
                    <div class="bg-gradient-to-r from-blue-700 to-indigo-900 text-white p-5 flex items-center gap-4">
                        <span class="text-3xl drop-shadow-md">🤖</span>
                        <div>
                            <h4 class="font-black text-xl leading-tight tracking-wide">Tactical AI Assistant</h4>
                            <p class="text-sm text-blue-200 font-medium">Ask me anything about team weaknesses, tactics, or match stats</p>
                        </div>
                    </div>

                   
                    <div class="flex-1 p-6 overflow-y-auto space-y-6 bg-slate-50">
                        @if(empty($chatHistory))
                            <div class="flex flex-col items-center justify-center h-full text-center space-y-4 opacity-50">
                                <span class="text-6xl">💬</span>
                                <p class="text-gray-500 font-bold text-lg">Hello! I'm your AI Tactical Assistant.<br>I have read all the data for this match. What do you want to know?</p>
                            </div>
                        @else
                            @foreach($chatHistory as $chat)
                                <div class="flex {{ $chat['sender'] === 'user' ? 'justify-end' : 'justify-start' }}">
                                    <div class="max-w-[75%] rounded-2xl p-4 text-[15px] leading-relaxed shadow-sm {{ $chat['sender'] === 'user' ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white text-gray-800 border border-gray-200 rounded-bl-none' }}">
                                        {!! nl2br(e($chat['text'])) !!}
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        
                        
                        <div wire:loading wire:target="askAssistant" class="flex justify-start">
                            <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-none p-4 shadow-sm flex items-center gap-2">
                                <div class="w-2.5 h-2.5 bg-blue-500 rounded-full animate-bounce"></div>
                                <div class="w-2.5 h-2.5 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                <div class="w-2.5 h-2.5 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="p-4 bg-white border-t border-gray-100">
                        <form wire:submit.prevent="askAssistant" class="flex items-center gap-3">
                            <input 
                                type="text" 
                                wire:model="userQuestion" 
                                placeholder="Type your tactical question here..." 
                                class="flex-1 bg-gray-100 border border-transparent hover:border-gray-200 rounded-xl px-5 py-4 text-[15px] focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition"
                                required
                            >
                            <button type="submit" wire:loading.attr="disabled" class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-xl transition disabled:opacity-50 flex items-center justify-center min-w-[60px] shadow-md hover:shadow-lg">
                                
                                <span wire:loading.remove wire:target="askAssistant">
                                    <svg class="w-6 h-6 transform rotate-45 -mt-1 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                </span>
                                
                                <span wire:loading wire:target="askAssistant">
                                    <svg class="animate-spin w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
                

                @if(!empty($topPlayers))
                    <div class="mt-12 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                        <h4 class="text-xl font-bold text-gray-800 mb-6 text-center tracking-wide uppercase">🌟 Top Performers</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @foreach($topPlayers as $index => $player)
                                <div class="flex items-center gap-4 p-4 rounded-xl {{ $index === 0 ? 'bg-yellow-50 border border-yellow-300 shadow-md transform scale-105 transition' : 'bg-gray-50 border border-gray-200' }}">
                                    <img src="{{ $player['photo'] }}" alt="{{ $player['name'] }}" class="w-16 h-16 rounded-full object-cover shadow-sm bg-white border-2 {{ $index === 0 ? 'border-yellow-400' : 'border-gray-200' }}">
                                    <div class="flex-1">
                                        <p class="font-bold text-gray-800 leading-tight truncate">{{ $player['name'] }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $player['team'] }} • {{ $player['pos'] }}</p>
                                        <div class="mt-1 flex items-center gap-1">
                                            <span class="text-yellow-500 text-sm">⭐</span>
                                            <span class="font-black text-lg {{ $index === 0 ? 'text-yellow-700' : 'text-gray-700' }}">{{ number_format($player['rating'], 1) }}</span>
                                        </div>
                                    </div>
                                    @if($index === 0)
                                        <div class="text-4xl animate-bounce" title="Man of the Match">🏆</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8 border-t border-gray-100 pt-8">
                    @foreach($lineups as $lineup)
                        @php 
                            // Filter pemain cedera khusus untuk tim yang sedang di-looping ini
                            $teamInjuries = collect($injuries)->where('team.id', $lineup['team']['id'])->all();
                        @endphp

                        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col h-full">
                            <div class="flex flex-col mb-6 gap-2">
                                <div class="flex justify-between items-center">
                                    <h5 class="font-black text-xl text-gray-800">{{ $lineup['team']['name'] }}</h5>
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-bold shadow-sm">
                                        {{ $lineup['formation'] }}
                                    </span>
                                </div>
                                
                                <div class="w-full">
                                    <span class="bg-indigo-900 text-indigo-100 px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider shadow-inner inline-block">
                                        {{ $lineup['team']['name'] === $selectedMatch['teamA'] ? $selectedMatch['teamA_playstyle'] : $selectedMatch['teamB_playstyle'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-3 mb-6">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b pb-2">Starting Eleven</p>
                                @foreach($lineup['startXI'] as $player)
                                    @php $playerId = $player['player']['id']; @endphp
                                    <div class="flex items-center gap-2 hover:bg-gray-50 p-1.5 rounded transition">
                                        <span class="text-blue-600 font-mono font-bold w-6 text-right">{{ $player['player']['number'] }}</span>
                                        <span class="text-gray-800 font-medium whitespace-nowrap">{{ $player['player']['name'] }}</span>
                                        
                                        <div class="flex flex-wrap items-center gap-2 ml-1">
                                            @foreach($events as $event)
                                                @if(isset($event['player']['id']) && $event['player']['id'] === $playerId)
                                                    @if($event['type'] === 'Goal')
                                                        <span class="flex items-center text-[11px] font-bold text-gray-500"><span class="text-sm mr-0.5">⚽</span>{{ $event['time']['elapsed'] }}'</span>
                                                    @elseif($event['type'] === 'Card')
                                                        @if(str_contains($event['detail'], 'Yellow'))
                                                            <span class="flex items-center text-[11px] font-bold text-gray-500"><div class="w-2.5 h-3.5 bg-yellow-400 rounded-sm border border-yellow-500 shadow-sm mr-1"></div>{{ $event['time']['elapsed'] }}'</span>
                                                        @else
                                                            <span class="flex items-center text-[11px] font-bold text-gray-500"><div class="w-2.5 h-3.5 bg-red-600 rounded-sm border border-red-700 shadow-sm mr-1"></div>{{ $event['time']['elapsed'] }}'</span>
                                                        @endif
                                                    @elseif($event['type'] === 'subst')
                                                        <span class="flex items-center text-[11px] font-bold text-red-500"><span class="text-sm mr-0.5">⬇️</span>{{ $event['time']['elapsed'] }}'</span>
                                                    @endif
                                                @endif
                                                
                                                @if($event['type'] === 'subst' && isset($event['assist']['id']) && $event['assist']['id'] === $playerId)
                                                    <span class="flex items-center text-[11px] font-bold text-green-600"><span class="text-sm mr-0.5">⬆️</span>{{ $event['time']['elapsed'] }}'</span>
                                                @endif
                                            @endforeach
                                        </div>
                                        <span class="ml-auto text-xs font-bold text-gray-400 uppercase w-8 text-center">{{ $player['player']['pos'] }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="space-y-2 mb-6 flex-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b pb-2">Substitutes</p>
                                <div class="grid grid-cols-1 gap-1">
                                    @foreach($lineup['substitutes'] as $sub)
                                        @php $subId = $sub['player']['id']; @endphp
                                        <div class="flex items-center gap-2 hover:bg-gray-50 p-1.5 rounded transition">
                                            <span class="text-gray-400 font-mono w-6 text-right text-sm">{{ $sub['player']['number'] }}</span>
                                            <span class="text-gray-500 italic text-sm whitespace-nowrap">{{ $sub['player']['name'] }}</span>
                                            
                                            <div class="flex flex-wrap items-center gap-2 ml-1">
                                                @foreach($events as $event)
                                                    @if(isset($event['player']['id']) && $event['player']['id'] === $subId)
                                                        @if($event['type'] === 'Goal')
                                                            <span class="flex items-center text-[11px] font-bold text-gray-400"><span class="text-sm mr-0.5">⚽</span>{{ $event['time']['elapsed'] }}'</span>
                                                        @elseif($event['type'] === 'Card')
                                                            @if(str_contains($event['detail'], 'Yellow'))
                                                                <span class="flex items-center text-[11px] font-bold text-gray-400"><div class="w-2.5 h-3.5 bg-yellow-400 rounded-sm border border-yellow-500 shadow-sm mr-1"></div>{{ $event['time']['elapsed'] }}'</span>
                                                            @else
                                                                <span class="flex items-center text-[11px] font-bold text-gray-400"><div class="w-2.5 h-3.5 bg-red-600 rounded-sm border border-red-700 shadow-sm mr-1"></div>{{ $event['time']['elapsed'] }}'</span>
                                                            @endif
                                                        @elseif($event['type'] === 'subst')
                                                            <span class="flex items-center text-[11px] font-bold text-red-400"><span class="text-sm mr-0.5">⬇️</span>{{ $event['time']['elapsed'] }}'</span>
                                                        @endif
                                                    @endif
                                                    
                                                    @if($event['type'] === 'subst' && isset($event['assist']['id']) && $event['assist']['id'] === $subId)
                                                        <span class="flex items-center text-[11px] font-bold text-green-500"><span class="text-sm mr-0.5">⬆️</span>{{ $event['time']['elapsed'] }}'</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                            <span class="ml-auto text-xs text-gray-300 uppercase w-8 text-center">{{ $sub['player']['pos'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if(count($teamInjuries) > 0)
                                <div class="mt-2 mb-6 p-4 bg-red-50 rounded-lg border border-red-100">
                                    <p class="text-[10px] font-bold text-red-500 uppercase tracking-widest mb-3 flex items-center gap-1">
                                        <span class="text-sm">🚑</span> Missing Players
                                    </p>
                                    <div class="space-y-2">
                                        @foreach($teamInjuries as $injury)
                                            <div class="flex items-start gap-2">
                                                <span class="text-red-400 text-xs mt-0.5">⚕️</span>
                                                <div>
                                                    <p class="font-bold text-gray-800 text-sm leading-tight">{{ $injury['player']['name'] }}</p>
                                                    <p class="text-xs text-red-600 font-medium">{{ $injury['player']['reason'] ?? 'Injured / Suspended' }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mt-auto pt-4 border-t border-gray-100 bg-gray-50 -mx-6 -mb-6 p-6 rounded-b-xl flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Manager</p>
                                    <p class="text-base font-bold text-gray-800">{{ $lineup['coach']['name'] ?? 'Unknown Coach' }}</p>
                                </div>
                                <div class="text-2xl text-gray-300">👔</div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div> </div> @endif </div> 

