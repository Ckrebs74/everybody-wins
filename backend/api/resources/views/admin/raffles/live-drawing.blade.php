@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-900 via-purple-800 to-indigo-900 py-12 px-4">
    <div class="container mx-auto max-w-6xl">
        
        {{-- Header mit Produkt-Info --}}
        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 mb-8 border border-white/20">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <div class="w-20 h-20 bg-yellow-500 rounded-xl flex items-center justify-center text-4xl">
                        🎲
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">
                            LIVE ZIEHUNG
                        </h1>
                        <p class="text-white/70">{{ $raffle->product->title }}</p>
                    </div>
                </div>
                
                <div class="text-right">
                    <p class="text-white/70 text-sm">Status</p>
                    <span class="inline-block px-4 py-2 bg-yellow-500 text-white font-bold rounded-lg">
                        {{ $raffle->status }}
                    </span>
                </div>
            </div>

            {{-- Statistiken --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                    <p class="text-white/70 text-sm mb-1">Verkaufte Lose</p>
                    <p class="text-3xl font-bold text-white">{{ $raffle->tickets_sold }}</p>
                </div>
                <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                    <p class="text-white/70 text-sm mb-1">Teilnehmer</p>
                    <p class="text-3xl font-bold text-white">{{ $raffle->unique_participants }}</p>
                </div>
                <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                    <p class="text-white/70 text-sm mb-1">Einnahmen</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($raffle->total_revenue, 0) }}€</p>
                </div>
                <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                    <p class="text-white/70 text-sm mb-1">Zielpreis</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($raffle->target_price, 0) }}€</p>
                </div>
            </div>

            {{-- Zielpreis-Status --}}
            <div class="mt-6">
                @if($raffle->target_reached)
                    <div class="bg-green-500/20 border border-green-500 rounded-lg p-4">
                        <p class="text-green-300 font-bold text-center">
                            ✅ ZIELPREIS ERREICHT! Gewinner erhält das Produkt.
                        </p>
                    </div>
                @else
                    <div class="bg-orange-500/20 border border-orange-500 rounded-lg p-4">
                        <p class="text-orange-300 font-bold text-center">
                            ⚠️ ZIELPREIS NICHT ERREICHT! 
                            @if($raffle->product->decision_type === 'give')
                                Verkäufer gibt Produkt trotzdem ab.
                            @else
                                Verkäufer behält Produkt - Gewinner erhält Geldpreis.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Haupt-Ziehungs-Bereich --}}
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            
            {{-- Slot Machine Display --}}
            <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 p-12">
                <div id="slotMachine" class="flex justify-center items-center space-x-4">
                    <!-- Die Slot-Rollen werden hier per JavaScript generiert -->
                </div>
                
                {{-- Gewinner-Anzeige (initial hidden) --}}
                <div id="winnerDisplay" class="hidden mt-8 text-center">
                    <div class="bg-white rounded-2xl p-8 shadow-xl animate-bounce">
                        <p class="text-6xl mb-4">🎉</p>
                        <h2 class="text-4xl font-bold text-gray-800 mb-2">
                            GEWINNER!
                        </h2>
                        <p class="text-gray-600 mb-4">Ticket-Nummer:</p>
                        <p id="winningTicket" class="text-5xl font-bold text-yellow-600 mb-4">
                            <!-- Wird per JS gefüllt -->
                        </p>
                        <p class="text-gray-600 mb-2">Gewinner:</p>
                        <p id="winnerName" class="text-2xl font-bold text-gray-800">
                            <!-- Wird per JS gefüllt -->
                        </p>
                    </div>
                </div>
            </div>

            {{-- Control Panel --}}
            <div class="p-8 bg-gray-50">
                @if($raffle->status !== 'completed')
                    <button id="startDrawingBtn" 
                            class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-2xl font-bold py-6 px-8 rounded-xl shadow-lg transform transition-all duration-200 hover:scale-105 hover:shadow-2xl">
                        🎲 ZIEHUNG STARTEN
                    </button>
                @else
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600 mb-4">
                            ✅ Ziehung bereits durchgeführt
                        </p>
                        <a href="{{ route('admin.raffles.show', $raffle) }}" 
                           class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg">
                            Zurück zur Übersicht
                        </a>
                    </div>
                @endif

                {{-- Status Messages --}}
                <div id="statusMessage" class="mt-4 hidden">
                    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative">
                        <span id="statusText"></span>
                    </div>
                </div>

                {{-- Error Messages --}}
                <div id="errorMessage" class="mt-4 hidden">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <span id="errorText"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Teilnehmer-Liste (Sidebar) --}}
        <div class="mt-8 bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-bold mb-4 text-gray-800">
                👥 Teilnehmer (Top 10)
            </h3>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @foreach($raffle->tickets()->with('user')->latest()->take(10)->get() as $ticket)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                {{ substr($ticket->user->first_name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{ $ticket->user->first_name ?? 'Teilnehmer' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $ticket->purchased_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <span class="text-sm font-mono text-gray-600">
                            #{{ substr($ticket->ticket_number, -6) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startBtn = document.getElementById('startDrawingBtn');
    const slotMachine = document.getElementById('slotMachine');
    const winnerDisplay = document.getElementById('winnerDisplay');
    const statusMessage = document.getElementById('statusMessage');
    const errorMessage = document.getElementById('errorMessage');
    const raffleId = {{ $raffle->id }};
    
    // Erstelle Slot Machine Rollen
    function createSlotMachine() {
        slotMachine.innerHTML = '';
        for (let i = 0; i < 8; i++) {
            const reel = document.createElement('div');
            reel.className = 'slot-reel w-16 h-24 bg-white rounded-lg shadow-lg flex items-center justify-center text-4xl font-bold text-gray-800 overflow-hidden relative';
            reel.dataset.reel = i;
            reel.innerHTML = '<span class="slot-number">0</span>';
            slotMachine.appendChild(reel);
        }
    }
    
    // Animiere einzelne Rolle
    function animateReel(reel, finalNumber, duration) {
        return new Promise((resolve) => {
            const numberSpan = reel.querySelector('.slot-number');
            let currentNumber = 0;
            const interval = 50;
            const steps = duration / interval;
            let step = 0;
            
            const animation = setInterval(() => {
                currentNumber = Math.floor(Math.random() * 10);
                numberSpan.textContent = currentNumber;
                numberSpan.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    numberSpan.style.transform = 'scale(1)';
                }, 25);
                
                step++;
                if (step >= steps) {
                    clearInterval(animation);
                    numberSpan.textContent = finalNumber;
                    numberSpan.classList.add('animate-pulse');
                    setTimeout(() => {
                        numberSpan.classList.remove('animate-pulse');
                    }, 500);
                    resolve();
                }
            }, interval);
        });
    }
    
    // Haupt-Ziehungs-Funktion
    async function startDrawing() {
        startBtn.disabled = true;
        startBtn.classList.add('opacity-50', 'cursor-not-allowed');
        startBtn.textContent = '🎲 Ziehung läuft...';
        
        try {
            // API Call zur Ziehung
            showStatus('Ermittle Gewinner...');
            
            const response = await fetch(`/admin/raffles/${raffleId}/execute-draw`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Fehler bei der Ziehung');
            }
            
            // Animation starten
            showStatus('Lose werden gemischt...');
            const ticketNumber = data.winner.ticket_number;
            
            // Extrahiere die letzten 8 Ziffern
            const numbers = ticketNumber.replace(/\D/g, '').slice(-8).split('');
            
            // Animiere jede Rolle nacheinander
            for (let i = 0; i < Math.min(8, numbers.length); i++) {
                const reel = document.querySelector(`[data-reel="${i}"]`);
                await animateReel(reel, numbers[i], 2000 + (i * 300));
            }
            
            // Zeige Gewinner
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            document.getElementById('winningTicket').textContent = ticketNumber;
            document.getElementById('winnerName').textContent = data.winner.winner_name;
            
            winnerDisplay.classList.remove('hidden');
            winnerDisplay.classList.add('animate-bounce');
            
            showStatus(`🎉 Gewinner ermittelt! ${data.message}`);
            
            // Confetti Effect (optional)
            if (typeof confetti !== 'undefined') {
                confetti({
                    particleCount: 100,
                    spread: 70,
                    origin: { y: 0.6 }
                });
            }
            
            // Redirect nach 5 Sekunden
            setTimeout(() => {
                window.location.href = `/admin/raffles/${raffleId}`;
            }, 5000);
            
        } catch (error) {
            console.error('Error:', error);
            showError(error.message);
            startBtn.disabled = false;
            startBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            startBtn.textContent = '🎲 ZIEHUNG STARTEN';
        }
    }
    
    function showStatus(message) {
        statusMessage.classList.remove('hidden');
        document.getElementById('statusText').textContent = message;
        errorMessage.classList.add('hidden');
    }
    
    function showError(message) {
        errorMessage.classList.remove('hidden');
        document.getElementById('errorText').textContent = message;
        statusMessage.classList.add('hidden');
    }
    
    // Event Listeners
    if (startBtn) {
        startBtn.addEventListener('click', startDrawing);
    }
    
    // Initialize
    createSlotMachine();
});
</script>

<style>
.slot-reel {
    transition: all 0.1s ease;
}

.slot-number {
    display: inline-block;
    transition: transform 0.05s ease;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-20px);
    }
}
</style>
@endpush
@endsection