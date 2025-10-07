@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 text-white">
    <div class="container mx-auto px-4 py-8">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-5xl font-bold mb-2 animate-pulse">🎉 LIVE VERLOSUNG 🎉</h1>
            <p class="text-2xl text-gray-300">{{ $raffle->product->title }}</p>
        </div>

        {{-- Produkt Info Card --}}
        <div class="max-w-4xl mx-auto bg-white/10 backdrop-blur-md rounded-2xl p-8 mb-8 border border-white/20">
            <div class="grid md:grid-cols-2 gap-8">
                {{-- Produktbild --}}
                <div class="flex items-center justify-center">
                    @if($raffle->product->images->isNotEmpty())
                        <img src="{{ $raffle->product->images->first()->image_path }}" 
                             alt="{{ $raffle->product->title }}"
                             class="w-full max-w-sm rounded-xl shadow-2xl">
                    @endif
                </div>

                {{-- Stats --}}
                <div class="space-y-4">
                    <div class="bg-white/10 rounded-lg p-4">
                        <div class="text-sm text-gray-300 mb-1">Teilnehmer</div>
                        <div class="text-4xl font-bold">{{ $raffle->unique_participants }}</div>
                    </div>

                    <div class="bg-white/10 rounded-lg p-4">
                        <div class="text-sm text-gray-300 mb-1">Verkaufte Lose</div>
                        <div class="text-4xl font-bold">{{ $raffle->tickets_sold }}</div>
                    </div>

                    <div class="bg-white/10 rounded-lg p-4">
                        <div class="text-sm text-gray-300 mb-1">Gesamtumsatz</div>
                        <div class="text-4xl font-bold">€{{ number_format($raffle->total_revenue, 2) }}</div>
                    </div>

                    <div class="bg-white/10 rounded-lg p-4">
                        <div class="text-sm text-gray-300 mb-1">Zielpreis</div>
                        <div class="text-4xl font-bold {{ $raffle->target_reached ? 'text-green-400' : 'text-yellow-400' }}">
                            €{{ number_format($raffle->total_target, 2) }}
                            @if($raffle->target_reached)
                                <span class="text-xl ml-2">✓ Erreicht!</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Drawing Animation Area --}}
        <div id="drawingArea" class="max-w-4xl mx-auto">
            {{-- Pre-Draw State --}}
            <div id="preDrawState" class="text-center space-y-8">
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-12 border border-white/20">
                    <div class="text-6xl mb-6">🎲</div>
                    <h2 class="text-3xl font-bold mb-4">Bereit für die Ziehung!</h2>
                    <p class="text-xl text-gray-300 mb-8">
                        {{ $raffle->tickets_sold }} Lose nehmen teil. Einer gewinnt!
                    </p>

                    <button onclick="startDraw()" 
                            class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-6 px-12 rounded-full text-2xl shadow-2xl transform hover:scale-105 transition duration-200">
                        🎯 JETZT ZIEHEN!
                    </button>
                </div>
            </div>

            {{-- Drawing Animation (hidden initially) --}}
            <div id="animationState" class="hidden text-center space-y-8">
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-12 border border-white/20">
                    <div id="spinner" class="text-9xl animate-spin">🎰</div>
                    <h2 class="text-4xl font-bold mt-8 animate-pulse">Die Lose werden gemischt...</h2>
                    <div class="mt-4 text-2xl text-gray-300" id="countdown">3</div>
                </div>
            </div>

            {{-- Winner Reveal (hidden initially) --}}
            <div id="winnerState" class="hidden">
                <div class="bg-gradient-to-r from-yellow-400 via-orange-500 to-red-500 rounded-2xl p-12 border-4 border-yellow-300 shadow-2xl animate-bounce">
                    <div class="text-center">
                        <div class="text-8xl mb-6">🏆</div>
                        <h2 class="text-5xl font-bold mb-4">WIR HABEN EINEN GEWINNER!</h2>
                        
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-8 mt-8">
                            <div class="text-2xl text-gray-200 mb-2">Gewinner-Ticket</div>
                            <div id="winnerTicket" class="text-6xl font-mono font-bold"></div>
                            
                            <div class="text-2xl text-gray-200 mb-2 mt-6">Gewinner</div>
                            <div id="winnerName" class="text-4xl font-bold"></div>
                        </div>

                        <div class="mt-8 bg-white/10 rounded-lg p-6">
                            <div class="text-xl text-gray-200 mb-2">Was erhält der Gewinner?</div>
                            <div id="winnerPrize" class="text-3xl font-bold"></div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-8">
                    <a href="{{ route('admin.raffles.show', $raffle->id) }}" 
                       class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-full text-xl">
                        Details anzeigen
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let drawData = null;

async function startDraw() {
    // Hide pre-draw, show animation
    document.getElementById('preDrawState').classList.add('hidden');
    document.getElementById('animationState').classList.remove('hidden');
    
    // Countdown animation
    let count = 3;
    const countdownEl = document.getElementById('countdown');
    
    const countdownInterval = setInterval(() => {
        count--;
        countdownEl.textContent = count;
        
        if (count === 0) {
            clearInterval(countdownInterval);
            countdownEl.textContent = 'Ziehung läuft...';
            
            // Actual draw after 3 seconds
            setTimeout(() => executeDraw(), 1000);
        }
    }, 1000);
}

async function executeDraw() {
    try {
        const response = await fetch('{{ route('admin.raffles.execute-live-draw', $raffle->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            drawData = result;
            revealWinner(result);
        } else {
            alert('Fehler bei der Ziehung: ' + result.error);
            location.reload();
        }
        
    } catch (error) {
        console.error('Draw error:', error);
        alert('Fehler bei der Ziehung. Bitte Seite neu laden.');
    }
}

function revealWinner(result) {
    // Hide animation, show winner
    document.getElementById('animationState').classList.add('hidden');
    document.getElementById('winnerState').classList.remove('hidden');
    
    // Populate winner data
    document.getElementById('winnerTicket').textContent = result.winner.ticket_number;
    document.getElementById('winnerName').textContent = result.winner.user_name || `User #${result.winner.user_id}`;
    
    // Determine prize
    let prizeText = '';
    if (result.payout.final_decision === 'product_to_winner') {
        prizeText = '🎁 ' + '{{ $raffle->product->title }}';
    } else if (result.payout.final_decision === 'money_to_winner') {
        prizeText = '💰 €' + result.payout.payout_amount.toFixed(2);
    }
    document.getElementById('winnerPrize').textContent = prizeText;
    
    // Confetti animation (optional, requires canvas-confetti library)
    if (typeof confetti !== 'undefined') {
        confetti({
            particleCount: 200,
            spread: 100,
            origin: { y: 0.6 }
        });
        
        setTimeout(() => {
            confetti({
                particleCount: 150,
                angle: 60,
                spread: 70,
                origin: { x: 0 }
            });
        }, 250);
        
        setTimeout(() => {
            confetti({
                particleCount: 150,
                angle: 120,
                spread: 70,
                origin: { x: 1 }
            });
        }, 500);
    }
}
</script>

{{-- Optional: Confetti Library (CDN) --}}
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
@endpush
@endsection