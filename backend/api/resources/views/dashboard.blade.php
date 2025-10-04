@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Wallet Info -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-xl font-bold mb-4">💰 Mein Guthaben</h3>
        <p class="text-3xl font-bold text-green-600">{{ $user->wallet->balance }}€</p>
        <p class="text-sm text-gray-600 mt-2">Bonus: {{ $user->wallet->bonus_balance }}€</p>
        
        <!-- Einzahlung -->
        <form method="POST" action="/deposit" class="mt-4">
            @csrf
            <input type="number" name="amount" min="5" max="100" step="5" value="10" 
                   class="w-full px-3 py-2 border rounded mb-2">
            <button type="submit" class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600">
                Guthaben aufladen
            </button>
        </form>
    </div>
    
    <!-- Spending Limit Info -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-xl font-bold mb-4">⏱️ Ausgabenlimit</h3>
        <p class="text-lg">Diese Stunde ausgegeben:</p>
        <p class="text-3xl font-bold text-red-600">{{ $currentSpending }}€ / 10€</p>
        <div class="bg-gray-200 rounded-full h-4 mt-4">
            <div class="bg-red-500 h-4 rounded-full" 
                 style="width: {{ ($currentSpending / 10) * 100 }}%">
            </div>
        </div>
        <p class="text-sm text-gray-600 mt-2">
            Du kannst noch {{ $remainingLimit }}€ ausgeben
        </p>
    </div>
    
    <!-- Stats -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-xl font-bold mb-4">📊 Meine Statistiken</h3>
        <div class="space-y-2">
            <p>Aktive Lose: <span class="font-bold">{{ $activeTickets }}</span></p>
            <p>Gewonnene Verlosungen: <span class="font-bold">{{ $wonRaffles }}</span></p>
            <p>Mitglied seit: <span class="font-bold">{{ $user->created_at->format('d.m.Y') }}</span></p>
        </div>
    </div>
</div>

<!-- Meine Tickets -->
<div class="bg-white rounded-lg shadow-lg p-6 mt-6">
    <h3 class="text-xl font-bold mb-4">🎫 Meine Lose</h3>
    @if($user->tickets->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left p-2">Ticket Nr.</th>
                        <th class="text-left p-2">Produkt</th>
                        <th class="text-left p-2">Status</th>
                        <th class="text-left p-2">Gekauft am</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($user->tickets as $ticket)
                        <tr class="border-b">
                            <td class="p-2">{{ $ticket->ticket_number }}</td>
                            <td class="p-2">{{ $ticket->raffle->product->title }}</td>
                            <td class="p-2">
                                @if($ticket->status == 'winner')
                                    <span class="bg-green-500 text-white px-2 py-1 rounded">GEWONNEN!</span>
                                @elseif($ticket->raffle->status == 'completed')
                                    <span class="bg-gray-500 text-white px-2 py-1 rounded">Nicht gewonnen</span>
                                @else
                                    <span class="bg-yellow-500 text-white px-2 py-1 rounded">Aktiv</span>
                                @endif
                            </td>
                            <td class="p-2">{{ $ticket->purchased_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-600">Du hast noch keine Lose gekauft.</p>
    @endif
</div>
@endsection