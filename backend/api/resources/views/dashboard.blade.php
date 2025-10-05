@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Mein Dashboard</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        {{-- Wallet-Widget --}}
        <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
            <h3 class="text-lg font-semibold mb-2">💰 Mein Guthaben</h3>
            <p class="text-4xl font-bold mb-4">{{ number_format($user->wallet_balance, 2, ',', '.') }} €</p>
            <a href="{{ route('wallet.index') }}" 
               class="block bg-white text-yellow-600 text-center py-2 rounded-lg font-semibold hover:bg-gray-100">
                Guthaben verwalten
            </a>
        </div>

        {{-- Spending Limit --}}
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-2">⏱️ Ausgabenlimit</h3>
            <p class="text-sm text-gray-600 mb-2">Diese Stunde:</p>
            <p class="text-2xl font-bold {{ $spendingStats['remaining_hour'] < 3 ? 'text-red-600' : 'text-green-600' }}">
                {{ number_format($spendingStats['current_hour'], 2, ',', '.') }} € / 10,00 €
            </p>
            <div class="w-full bg-gray-200 rounded-full h-3 mt-3">
                <div class="bg-blue-500 h-3 rounded-full transition-all" 
                     style="width: {{ $spendingStats['percentage_used'] }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                Verbleibend: {{ number_format($spendingStats['remaining_hour'], 2, ',', '.') }} €
            </p>
        </div>

        {{-- Ticket-Statistiken --}}
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-2">🎟️ Meine Lose</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Aktive:</span>
                    <span class="font-bold text-blue-600">{{ $ticketCounts['active'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Gewonnen:</span>
                    <span class="font-bold text-green-600">{{ $ticketCounts['winner'] }}</span>
                </div>
                <div class="flex justify-between border-t pt-2">
                    <span class="text-gray-600">Gesamt:</span>
                    <span class="font-bold">{{ $ticketCounts['total'] }}</span>
                </div>
            </div>
            <a href="{{ route('tickets.index') }}" 
               class="block mt-4 text-center text-yellow-600 font-semibold hover:underline">
                Alle Lose anzeigen →
            </a>
        </div>
    </div>

    {{-- Aktive Tickets --}}
    @if($activeTickets->count() > 0)
        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-4">Aktive Teilnahmen</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($activeTickets as $ticket)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        @php
                            $image = $ticket->raffle->product->images()->where('is_primary', true)->first() 
                                  ?? $ticket->raffle->product->images()->first();
                        @endphp
                        
                        @if($image)
                            <img src="{{ $image->image_path }}" 
                                 alt="{{ $ticket->raffle->product->title }}"
                                 class="w-full h-32 object-cover">
                        @endif
                        
                        <div class="p-4">
                            <h3 class="font-bold mb-2">{{ $ticket->raffle->product->title }}</h3>
                            <p class="text-sm text-gray-600 mb-2">
                                Los-Nr.: <span class="font-mono">{{ $ticket->ticket_number }}</span>
                            </p>
                            <p class="text-xs text-gray-500">
                                Gekauft: {{ $ticket->purchased_at->format('d.m.Y H:i') }}
                            </p>
                            <a href="{{ route('raffles.show', $ticket->raffle->id) }}" 
                               class="block mt-3 text-center bg-yellow-500 text-white py-2 rounded hover:bg-yellow-600">
                                Zur Verlosung
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Gewinnchancen --}}
    @if(count($winningChances) > 0)
        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-4">Meine Gewinnchancen</h2>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left">Produkt</th>
                            <th class="px-4 py-3 text-center">Meine Lose</th>
                            <th class="px-4 py-3 text-center">Gesamt Lose</th>
                            <th class="px-4 py-3 text-center">Gewinnchance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($winningChances as $chance)
                            <tr class="border-t">
                                <td class="px-4 py-3">{{ $chance['product_title'] }}</td>
                                <td class="px-4 py-3 text-center font-semibold">{{ $chance['user_tickets'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $chance['total_tickets'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-bold text-green-600">{{ $chance['chance_percentage'] }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Letzte Transaktionen --}}
    <div>
        <h2 class="text-2xl font-bold mb-4">Letzte Transaktionen</h2>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            @if($recentTransactions->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left">Datum</th>
                            <th class="px-4 py-3 text-left">Beschreibung</th>
                            <th class="px-4 py-3 text-right">Betrag</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $transaction)
                            <tr class="border-t">
                                <td class="px-4 py-3 text-sm">{{ $transaction->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $transaction->description }}</td>
                                <td class="px-4 py-3 text-right font-semibold {{ $transaction->amount < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 2, ',', '.') }} €
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $transaction->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="p-6 text-gray-500 text-center">Noch keine Transaktionen vorhanden.</p>
            @endif
        </div>
        
        @if($recentTransactions->count() > 0)
            <a href="{{ route('wallet.index') }}" 
               class="block mt-4 text-center text-yellow-600 font-semibold hover:underline">
                Alle Transaktionen anzeigen →
            </a>
        @endif
    </div>
</div>
@endsection