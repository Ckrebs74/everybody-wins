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
        <div class="flex gap-4 items-center">
            @if($raffle->status === 'active')
                <a href="{{ route('admin.raffles.live-drawing', $raffle) }}" 
                   class="inline-flex items-center justify-center bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-semibold transition h-12">
                    🎲 Live-Ziehung starten
                </a>
                <form action="{{ route('admin.raffles.cancel', $raffle) }}" method="POST" 
                      onsubmit="return confirm('Verlosung wirklich abbrechen? Alle Teilnehmer werden erstattet.')">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-semibold transition h-12">
                        ❌ Verlosung abbrechen
                    </button>
                </form>
            @elseif($raffle->status === 'pending_draw')
                <form action="{{ route('admin.raffles.draw', $raffle) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold transition h-12">
                        🎯 Gewinner ziehen
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection