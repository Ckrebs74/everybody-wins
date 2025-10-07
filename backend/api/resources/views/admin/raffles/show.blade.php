@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <a href="{{ route('admin.raffles.index') }}" class="text-blue-600 hover:underline mb-2 inline-block">
                ← Zurück zur Übersicht
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Verlosung #{{ $raffle->id }}</h1>
            <p class="text-gray-600">{{ $raffle->product->title }}</p>
        </div>
        <div>
            <span class="px-3 py-1 rounded-full text-sm font-semibold
                @if($raffle->status === 'active') bg-green-100 text-green-800
                @elseif($raffle->status === 'pending_draw') bg-yellow-100 text-yellow-800
                @elseif($raffle->status === 'completed') bg-blue-100 text-blue-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ ucfirst($raffle->status) }}
            </span>
        </div>
    </div>

    {{-- Product Info --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex gap-6">
            @php
                $productImages = $raffle->product->images()->orderBy('sort_order')->get();
            @endphp
            @if($productImages->isNotEmpty())
                <img src="{{ $productImages->first()->image_path }}" 
                     alt="{{ $raffle->product->title }}"
                     class="w-48 h-48 object-cover rounded-lg flex-shrink-0">
            @else
                <div class="w-48 h-48 bg-gray-200 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="text-gray-400 text-sm">Kein Bild</span>
                </div>
            @endif
            
            <div class="flex-1">
                <h2 class="text-2xl font-bold mb-2">{{ $raffle->product->title }}</h2>
                <p class="text-gray-600 mb-4">{{ $raffle->product->description }}</p>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Verkäufer</span>
                        <p class="font-semibold">{{ $raffle->product->seller->email }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Kategorie</span>
                        <p class="font-semibold">{{ $raffle->product->category->name }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Zielpreis</span>
                        <p class="font-semibold">{{ number_format($raffle->target_price, 2, ',', '.') }} €</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Enddatum</span>
                        <p class="font-semibold">{{ $raffle->ends_at->format('d.m.Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4">
            <div class="text-sm text-gray-600 mb-1">Verkaufte Lose</div>
            <div class="text-3xl font-bold text-blue-600">{{ $ticketStats['total_sold'] }}</div>
        </div>
        <div class="bg-green-50 rounded-lg p-4">
            <div class="text-sm text-gray-600 mb-1">Einnahmen</div>
            <div class="text-3xl font-bold text-green-600">{{ number_format($ticketStats['total_revenue'], 2, ',', '.') }} €</div>
        </div>
        <div class="bg-purple-50 rounded-lg p-4">
            <div class="text-sm text-gray-600 mb-1">Teilnehmer</div>
            <div class="text-3xl font-bold text-purple-600">{{ $ticketStats['unique_buyers'] }}</div>
        </div>
        <div class="bg-yellow-50 rounded-lg p-4">
            <div class="text-sm text-gray-500 mb-1">Ø Lose/Person</div>
            <div class="text-3xl font-bold text-yellow-600">{{ $ticketStats['avg_tickets_per_user'] }}</div>
        </div>
    </div>

    {{-- Top Buyers --}}
    @if($topBuyers->isNotEmpty())
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold mb-4">Top 10 Käufer</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Käufer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Lose</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ausgegeben</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($topBuyers as $buyer)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $buyer->display_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $buyer->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="text-sm font-semibold text-gray-900">{{ $buyer->ticket_count }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="text-sm font-semibold text-green-600">{{ number_format($buyer->total_spent, 2, ',', '.') }} €</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Actions --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold mb-4">Aktionen</h3>
        <div class="flex gap-4 items-center flex-wrap">
            @if(in_array($raffle->status, ['active', 'pending_draw']))
                {{-- Live-Ziehung mit Animation --}}
                <a href="{{ route('admin.raffles.live-drawing', $raffle) }}" 
                   class="inline-flex items-center justify-center bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-8 py-4 rounded-lg font-bold text-lg shadow-lg transform transition-all duration-200 hover:scale-105">
                    🎲 Live-Ziehung (mit Animation)
                </a>
                
                {{-- Schnelle Ziehung ohne Animation --}}
                <form action="{{ route('admin.raffles.draw', $raffle) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Gewinner jetzt direkt ziehen (ohne Animation)?')"
                            class="inline-flex items-center justify-center bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-8 py-4 rounded-lg font-bold text-lg shadow-lg transform transition-all duration-200 hover:scale-105">
                        ⚡ Schnell-Ziehung (ohne Animation)
                    </button>
                </form>
            @endif
            
            @if($raffle->status === 'active')
                <form action="{{ route('admin.raffles.cancel', $raffle) }}" method="POST" 
                      onsubmit="return confirm('Verlosung wirklich abbrechen? Alle Teilnehmer werden erstattet.')">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-semibold transition">
                        ❌ Verlosung abbrechen
                    </button>
                </form>
            @endif
            
            @if($raffle->status === 'completed')
                <div class="flex items-center gap-3 bg-green-50 px-6 py-4 rounded-lg border border-green-200">
                    <span class="text-2xl">✅</span>
                    <div>
                        <p class="font-bold text-green-800">Ziehung abgeschlossen</p>
                        <p class="text-sm text-green-600">Gewinner: 
                            @if($raffle->winnerTicket)
                                {{ $raffle->winnerTicket->user->first_name ?? $raffle->winnerTicket->user->email }}
                            @endif
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection