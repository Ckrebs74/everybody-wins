{{-- =====================================================
FILE: resources/views/raffles/index.blade.php
===================================================== --}}
@extends('layouts.app')

@section('title', 'Alle Verlosungen')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Aktuelle Verlosungen</h1>
    <p class="text-gray-600 mt-2">Für nur 1€ pro Los die Chance auf hochwertige Produkte!</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($raffles as $raffle)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <h3 class="font-bold text-xl mb-2">{{ $raffle->product->title }}</h3>
                
                <div class="mb-4">
                    <p class="text-gray-600 text-sm mb-2">{{ Str::limit($raffle->product->description, 100) }}</p>
                    <p class="text-sm">
                        <span class="font-semibold">Marke:</span> {{ $raffle->product->brand ?? 'N/A' }}<br>
                        <span class="font-semibold">Zustand:</span> {{ ucfirst($raffle->product->condition) }}<br>
                        <span class="font-semibold">UVP:</span> {{ number_format($raffle->product->retail_price ?? 0, 2) }}€
                    </p>
                </div>

                <!-- Progress Bar -->
                <div class="mb-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>{{ $raffle->tickets_sold }} Lose verkauft</span>
                        <span>Ziel: {{ number_format($raffle->total_target, 0) }}€</span>
                    </div>
                    <div class="bg-gray-200 rounded-full h-4">
                        @php
                            $progress = min(100, ($raffle->total_revenue / $raffle->total_target) * 100);
                        @endphp
                        <div class="bg-yellow-500 h-4 rounded-full transition-all duration-300" 
                             style="width: {{ $progress }}%">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ number_format($progress, 1) }}% vom Ziel erreicht
                    </p>
                </div>

                <!-- Info -->
                <div class="mb-4 text-sm text-gray-600">
                    <p>⏰ Endet: {{ $raffle->ends_at->format('d.m.Y H:i') }} Uhr</p>
                    <p>👥 {{ $raffle->unique_participants }} Teilnehmer</p>
                </div>

                <!-- CTA Button -->
                <a href="{{ route('raffles.show', $raffle) }}" 
                   class="block w-full bg-yellow-500 text-gray-800 font-bold py-3 rounded-lg text-center hover:bg-yellow-400 transition">
                    Jetzt mitmachen - nur 1€ pro Los!
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-3 text-center py-8">
            <p class="text-gray-500">Aktuell keine aktiven Verlosungen.</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="mt-8">
    {{ $raffles->links() }}
</div>
@endsection