<?php

{{-- =====================================================
FILE: resources/views/raffles/show.blade.php
===================================================== --}}
@extends('layouts.app')

@section('title', $raffle->product->title)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-8">
            <!-- Product Title -->
            <h1 class="text-3xl font-bold text-gray-800 mb-4">{{ $raffle->product->title }}</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Left Column: Product Info -->
                <div>
                    <div class="mb-6">
                        <h3 class="font-semibold text-lg mb-2">Produktbeschreibung</h3>
                        <p class="text-gray-600">{{ $raffle->product->description }}</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="font-semibold text-lg mb-2">Details</h3>
                        <ul class="space-y-1 text-gray-600">
                            <li><strong>Marke:</strong> {{ $raffle->product->brand ?? 'N/A' }}</li>
                            <li><strong>Model:</strong> {{ $raffle->product->model ?? 'N/A' }}</li>
                            <li><strong>Zustand:</strong> {{ ucfirst($raffle->product->condition) }}</li>
                            <li><strong>UVP:</strong> {{ number_format($raffle->product->retail_price ?? 0, 2) }}€</li>
                            <li><strong>Verkäufer:</strong> {{ $raffle->product->seller->first_name }} {{ substr($raffle->product->seller->last_name, 0, 1) }}.</li>
                        </ul>
                    </div>

                    <div class="mb-6">
                        <h3 class="font-semibold text-lg mb-2">Verlosungsinfo</h3>
                        <ul class="space-y-1 text-gray-600">
                            <li>⏰ <strong>Endet:</strong> {{ $raffle->ends_at->format('d.m.Y H:i') }} Uhr</li>
                            <li>📅 <strong>Gestartet:</strong> {{ $raffle->starts_at->format('d.m.Y') }}</li>
                            <li>🎯 <strong>Zielpreis:</strong> {{ number_format($raffle->target_price, 2) }}€</li>
                            <li>💰 <strong>Platform-Gebühr (30%):</strong> {{ number_format($raffle->platform_fee, 2) }}€</li>
                        </ul>
                    </div>
                </div>

                <!-- Right Column: Purchase Section -->
                <div>
                    <!-- Progress -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h3 class="font-semibold text-lg mb-4">Fortschritt</h3>
                        
                        @php
                            $progress = min(100, ($raffle->total_revenue / $raffle->total_target) * 100);
                        @endphp
                        
                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-2">
                                <span>{{ number_format($raffle->total_revenue, 2) }}€ gesammelt</span>
                                <span>Ziel: {{ number_format($raffle->total_target, 2) }}€</span>
                            </div>
                            <div class="bg-gray-200 rounded-full h-6">
                                <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 h-6 rounded-full flex items-center justify-center text-xs text-gray-800 font-semibold"
                                     style="width: {{ $progress }}%">
                                    {{ number_format($progress, 1) }}%
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div class="bg-white rounded p-3">
                                <p class="text-2xl font-bold text-yellow-600">{{ $raffle->tickets_sold }}</p>
                                <p class="text-xs text-gray-600">Lose verkauft</p>
                            </div>
                            <div class="bg-white rounded p-3">
                                <p class="text-2xl font-bold text-blue-600">{{ $raffle->unique_participants }}</p>
                                <p class="text-xs text-gray-600">Teilnehmer</p>
                            </div>
                        </div>
                    </div>

                    <!-- Buy Tickets -->
                    @auth
                        <div class="bg-yellow-50 rounded-lg p-6">
                            <h3 class="font-semibold text-lg mb-4">Lose kaufen</h3>
                            
                            @if($userTickets > 0)
                                <div class="mb-4 p-3 bg-green-100 rounded">
                                    <p class="text-green-800">✅ Du hast bereits <strong>{{ $userTickets }}</strong> Los(e) für diese Verlosung!</p>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('raffles.buy-ticket', $raffle) }}">
                                @csrf
                                
                                <div class="mb-4">
                                    <label class="block text-gray-700 mb-2">Anzahl Lose (je 1€)</label>
                                    <select name="quantity" class="w-full px-4 py-2 border rounded-lg">
                                        @for($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}">{{ $i }} {{ $i == 1 ? 'Los' : 'Lose' }} - {{ $i }}€</option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="mb-4 p-3 bg-blue-50 rounded">
                                    <p class="text-sm text-blue-800">
                                        💡 <strong>Tipp:</strong> Je mehr Lose du kaufst, desto höher deine Gewinnchance!
                                    </p>
                                </div>

                                <button type="submit" 
                                        class="w-full bg-yellow-500 text-gray-800 font-bold py-3 rounded-lg hover:bg-yellow-400 transition">
                                    Lose kaufen & Chance sichern!
                                </button>
                            </form>

                            <p class="text-xs text-gray-500 mt-4 text-center">
                                ⚠️ Max. 10€/Stunde Ausgabenlimit aktiv
                            </p>
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <p class="text-gray-600 mb-4">Melde dich an um Lose zu kaufen!</p>
                            <a href="{{ route('login') }}" 
                               class="inline-block bg-yellow-500 text-gray-800 font-bold px-6 py-3 rounded-lg hover:bg-yellow-400 transition">
                                Jetzt anmelden
                            </a>
                            <p class="text-sm text-gray-500 mt-2">
                                Neue Nutzer erhalten 5€ Startguthaben!
                            </p>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Decision Info -->
            @if($raffle->product->decision_type !== 'pending')
                <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-800">
                        ℹ️ <strong>Verkäufer-Entscheidung:</strong> 
                        @if($raffle->product->decision_type === 'give')
                            Der Verkäufer gibt das Produkt ab, auch wenn das Ziel nicht erreicht wird.
                        @else
                            Der Verkäufer behält das Produkt, wenn das Ziel nicht erreicht wird. Der Gewinner erhält dann den Erlös.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection