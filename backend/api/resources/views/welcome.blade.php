@extends('layouts.app')

@section('content')
<div class="text-center py-12">
    <h1 class="text-6xl font-bold text-gray-800 mb-4">Jeder Gewinnt! 🎉</h1>
    <p class="text-xl text-gray-600 mb-8">
        Der gamifizierte Marktplatz, bei dem alle gewinnen. Garantiert!
    </p>
    <p class="text-lg text-gray-500 mb-8">
        Für nur 1€ pro Los die Chance auf hochwertige Produkte!
    </p>
    
    @guest
        <a href="/register" class="bg-yellow-500 text-gray-800 font-bold px-8 py-4 rounded-lg text-xl hover:bg-yellow-400">
            Jetzt starten - 5€ Startguthaben sichern!
        </a>
    @endguest
</div>

<div class="mt-12">
    <h2 class="text-3xl font-bold text-center mb-8">Aktuelle Verlosungen</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($activeRaffles as $raffle)
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="font-bold text-xl mb-2">{{ $raffle->product->title }}</h3>
                <p class="text-gray-600 mb-4">{{ Str::limit($raffle->product->description, 100) }}</p>
                <div class="mb-4">
                    <div class="bg-gray-200 rounded-full h-4">
                        <div class="bg-yellow-500 h-4 rounded-full" 
                             style="width: {{ min(100, ($raffle->total_revenue / $raffle->total_target) * 100) }}%">
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">
                        {{ $raffle->tickets_sold }} Lose verkauft | 
                        Ziel: {{ $raffle->total_target }}€
                    </p>
                </div>
                <a href="/raffles/{{ $raffle->id }}" class="bg-yellow-500 text-gray-800 px-4 py-2 rounded block text-center hover:bg-yellow-400">
                    Jetzt mitmachen - nur 1€!
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection