@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">💰 Mein Wallet</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        
        {{-- Aktuelles Guthaben --}}
        <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-lg shadow-lg p-8 text-white">
            <h2 class="text-xl font-semibold mb-4">Aktuelles Guthaben</h2>
            <p class="text-5xl font-bold mb-6">{{ number_format($balance, 2, ',', '.') }} €</p>
            
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="opacity-80">Gesamt eingezahlt:</p>
                    <p class="font-semibold">{{ number_format($stats['total_deposited'], 2, ',', '.') }} €</p>
                </div>
                <div>
                    <p class="opacity-80">Gesamt ausgegeben:</p>
                    <p class="font-semibold">{{ number_format($stats['total_spent'], 2, ',', '.') }} €</p>
                </div>
            </div>
        </div>

        {{-- Guthaben aufladen --}}
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-semibold mb-4">Guthaben aufladen</h3>
            
            <form action="{{ route('wallet.deposit') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-2">Betrag (5€ - 500€)</label>
                    <div class="relative">
                        <input type="number" 
                               name="amount" 
                               min="5" 
                               max="500" 
                               step="5" 
                               value="20"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-lg font-semibold">
                        <span class="absolute right-4 top-3 text-gray-500 font-semibold">€</span>
                    </div>
                </div>

                {{-- Quick Select Buttons --}}
                <div class="grid grid-cols-4 gap-2 mb-4">
                    <button type="button" onclick="document.querySelector('[name=amount]').value=10" 
                            class="bg-gray-100 hover:bg-yellow-100 border-2 border-gray-300 py-2 rounded font-semibold">
                        10€
                    </button>
                    <button type="button" onclick="document.querySelector('[name=amount]').value=20" 
                            class="bg-gray-100 hover:bg-yellow-100 border-2 border-gray-300 py-2 rounded font-semibold">
                        20€
                    </button>
                    <button type="button" onclick="document.querySelector('[name=amount]').value=50" 
                            class="bg-gray-100 hover:bg-yellow-100 border-2 border-gray-300 py-2 rounded font-semibold">
                        50€
                    </button>
                    <button type="button" onclick="document.querySelector('[name=amount]').value=100" 
                            class="bg-gray-100 hover:bg-yellow-100 border-2 border-gray-300 py-2 rounded font-semibold">
                        100€
                    </button>
                </div>

                <button type="submit" 
                        class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-3 rounded-lg font-bold">
                    @if(config('app.demo_mode', true))
                        💳 Jetzt aufladen (Demo-Mode)
                    @else
                        💳 Mit Stripe aufladen
                    @endif
                </button>

                @if(config('app.demo_mode', true))
                    <p class="text-xs text-gray-500 text-center mt-2">
                        Demo-Modus aktiv - Guthaben wird sofort gutgeschrieben
                    </p>
                @endif
            </form>
        </div>
    </div>

    {{-- Transaktionshistorie --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-2xl font-bold">Transaktionshistorie</h2>
        </div>

        @if($transactions->count() > 0)
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Datum</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Typ</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Beschreibung</th>
                        <th class="px-6 py-3 text-right text-sm font-semibold">Betrag</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm">
                                {{ $transaction->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    {{ $transaction->type === 'deposit' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $transaction->type === 'ticket_purchase' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $transaction->type === 'withdrawal' ? 'bg-purple-100 text-purple-800' : '' }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {{ $transaction->description }}
                            </td>
                            <td class="px-6 py-4 text-right font-semibold
                                {{ $transaction->amount < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 2, ',', '.') }} €
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-6">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="p-12 text-center text-gray-500">
                <p class="text-lg">Noch keine Transaktionen vorhanden.</p>
                <p class="text-sm mt-2">Laden Sie Guthaben auf oder kaufen Sie Lose!</p>
            </div>
        @endif
    </div>
</div>
@endsection