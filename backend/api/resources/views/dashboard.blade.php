@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-gray-600">Willkommen zurück, {{ $user->name }}!</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Wallet Balance -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-gray-600 text-sm font-medium">Guthaben</h3>
                <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['wallet_balance'], 2) }}€</p>
            <a href="{{ route('wallet.index') }}" class="text-yellow-500 text-sm hover:underline mt-2 inline-block">Aufladen →</a>
        </div>

        <!-- Total Tickets -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-gray-600 text-sm font-medium">Meine Lose</h3>
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['total_tickets'] }}</p>
            <p class="text-gray-500 text-sm mt-1">In {{ $stats['active_raffles'] }} Verlosungen</p>
        </div>

        <!-- Total Spent -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-gray-600 text-sm font-medium">Ausgegeben</h3>
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['total_spent'], 2) }}€</p>
            <p class="text-gray-500 text-sm mt-1">Gesamt</p>
        </div>

        <!-- Spending Limit -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-gray-600 text-sm font-medium">Stündenlimit</h3>
                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['remaining_budget'], 2) }}€</p>
            <p class="text-gray-500 text-sm mt-1">noch verfügbar</p>
            @if($stats['remaining_budget'] < 3)
                <p class="text-red-500 text-xs mt-1">⚠️ Limit fast erreicht</p>
            @endif
        </div>
    </div>

    <!-- Active Tickets -->
    @if($activeTickets->count() > 0)
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Aktive Teilnahmen</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($activeTickets as $item)
            <div class="border rounded-lg p-4 hover:shadow-md transition">
                @php
                    $productImages = $item['product']->images()->get();
                @endphp
                @if($productImages->count() > 0)
                    <img src="{{ $productImages->first()->image_path }}" 
                         alt="{{ $item['product']->title }}"
                         class="w-full h-48 object-cover rounded-lg mb-3">
                @else
                    <div class="w-full h-48 bg-gray-200 rounded-lg mb-3 flex items-center justify-center">
                        <span class="text-gray-400">Kein Bild</span>
                    </div>
                @endif
                
                <h3 class="font-bold text-gray-800 mb-2">{{ $item['product']->title }}</h3>
                
                <div class="space-y-1 text-sm">
                    <p class="text-gray-600">
                        <span class="font-semibold">Lose:</span> {{ $item['ticket_count'] }}
                    </p>
                    <p class="text-gray-600">
                        <span class="font-semibold">Ausgegeben:</span> {{ number_format($item['total_spent'], 2) }}€
                    </p>
                    <p class="text-gray-600">
                        <span class="font-semibold">Gewinnchance:</span> {{ $item['win_chance'] }}%
                    </p>
                </div>
                
                <a href="{{ route('raffles.show', $item['product']->slug) }}" 
                   class="mt-3 block text-center bg-yellow-500 hover:bg-yellow-600 text-gray-800 font-bold py-2 rounded-lg transition">
                    Details ansehen
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recent Transactions -->
    @if($recentTransactions->count() > 0)
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Letzte Transaktionen</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2 px-4 text-sm font-semibold text-gray-600">Datum</th>
                        <th class="text-left py-2 px-4 text-sm font-semibold text-gray-600">Typ</th>
                        <th class="text-left py-2 px-4 text-sm font-semibold text-gray-600">Beschreibung</th>
                        <th class="text-right py-2 px-4 text-sm font-semibold text-gray-600">Betrag</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $transaction)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4 text-sm text-gray-600">
                            {{ $transaction->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="py-3 px-4 text-sm">
                            @if($transaction->type === 'deposit')
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Einzahlung</span>
                            @elseif($transaction->type === 'ticket_purchase')
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Loskauf</span>
                            @elseif($transaction->type === 'withdrawal')
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Auszahlung</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">{{ $transaction->type }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-800">
                            {{ $transaction->description }}
                        </td>
                        <td class="py-3 px-4 text-sm font-semibold text-right">
                            @if(in_array($transaction->type, ['deposit', 'win']))
                                <span class="text-green-600">+{{ number_format($transaction->amount, 2) }}€</span>
                            @else
                                <span class="text-red-600">-{{ number_format($transaction->amount, 2) }}€</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <a href="{{ route('wallet.index') }}" class="mt-4 inline-block text-yellow-500 hover:underline">
            Alle Transaktionen anzeigen →
        </a>
    </div>
    @endif

    @if($activeTickets->count() === 0 && $recentTransactions->count() === 0)
    <div class="bg-white rounded-lg shadow-lg p-12 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
        </svg>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Keine Aktivität</h3>
        <p class="text-gray-600 mb-6">Sie haben noch keine Lose gekauft. Starten Sie jetzt!</p>
        <a href="{{ route('raffles.index') }}" class="bg-yellow-500 hover:bg-yellow-600 text-gray-800 font-bold py-3 px-6 rounded-lg inline-block transition">
            Verlosungen durchsuchen
        </a>
    </div>
    @endif
</div>
@endsection