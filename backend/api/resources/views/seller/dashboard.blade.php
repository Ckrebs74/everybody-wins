@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Verkäufer-Dashboard</h1>
        <p class="text-gray-600 mt-2">Willkommen zurück, {{ Auth::user()->first_name ?? Auth::user()->email }}!</p>
    </div>

    <!-- Statistik-Karten -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Gesamte Produkte -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Gesamte Produkte</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['total_products'] }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Aktive Verlosungen -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Aktive Verlosungen</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['active_raffles'] }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Entwürfe -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Entwürfe</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['draft_products'] }}</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Gesamteinnahmen -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Gesamteinnahmen</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($stats['total_earnings'], 2, ',', '.') }} €</p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Schnellaktionen</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Neues Produkt - Coming Soon -->
            <div class="flex items-center justify-center bg-gray-300 text-gray-600 font-semibold py-3 px-6 rounded-lg cursor-not-allowed">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Neues Produkt (Coming Soon)
            </div>
            
            <a href="{{ route('seller.products.index') }}" class="flex items-center justify-center bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
                Alle Produkte verwalten
            </a>
            
            <a href="{{ route('seller.analytics') }}" class="flex items-center justify-center bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Analytics ansehen
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Bald endende Verlosungen -->
        @if($endingSoon->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">Bald endende Verlosungen</h2>
                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                    Aufmerksamkeit erforderlich
                </span>
            </div>
            
            <div class="space-y-4">
                @foreach($endingSoon as $raffle)
                <div class="border-l-4 border-red-500 bg-red-50 p-4 rounded">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800">{{ $raffle->product->title }}</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Endet: {{ \Carbon\Carbon::parse($raffle->ends_at)->format('d.m.Y H:i') }} Uhr
                            </p>
                            <div class="mt-2">
                                <div class="flex items-center space-x-4 text-sm">
                                    <span class="text-gray-600">
                                        Verkauft: <strong>{{ $raffle->tickets_sold }}</strong> Lose
                                    </span>
                                    <span class="text-gray-600">
                                        Umsatz: <strong>{{ number_format($raffle->total_revenue, 2, ',', '.') }} €</strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('seller.products.show', $raffle->product->id) }}" class="ml-4 text-blue-600 hover:text-blue-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Letzte aktive Verlosungen -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Aktuelle Verlosungen</h2>
            
            @if($recentRaffles->count() > 0)
                <div class="space-y-4">
                    @foreach($recentRaffles as $raffle)
                    <div class="border rounded-lg p-4 hover:shadow-md transition duration-300">
                        <div class="flex items-center">
                            <!-- Produktbild -->
                            <div class="w-16 h-16 rounded overflow-hidden flex-shrink-0">
                                @if($raffle->product->images()->exists())
                                    <img src="{{ $raffle->product->images()->where('is_primary', true)->first()->image_path ?? $raffle->product->images()->first()->image_path }}" 
                                         alt="{{ $raffle->product->title }}" 
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Produktinfo -->
                            <div class="ml-4 flex-1">
                                <h3 class="font-semibold text-gray-800">{{ $raffle->product->title }}</h3>
                                <div class="mt-1 flex items-center space-x-4 text-sm text-gray-600">
                                    <span>Lose: {{ $raffle->tickets_sold }}</span>
                                    <span>Umsatz: {{ number_format($raffle->total_revenue, 2, ',', '.') }} €</span>
                                </div>
                                
                                <!-- Fortschrittsbalken -->
                                <div class="mt-2">
                                    @php
                                        $progress = ($raffle->total_target > 0) ? ($raffle->total_revenue / $raffle->total_target) * 100 : 0;
                                    @endphp
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ min(100, $progress) }}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">{{ number_format($progress, 1) }}% erreicht</p>
                                </div>
                            </div>
                            
                            <!-- Action Button -->
                            <a href="{{ route('seller.products.show', $raffle->product->id) }}" 
                               class="ml-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-semibold transition duration-300">
                                Details
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="mt-4 text-center">
                    <a href="{{ route('seller.products.index', ['status' => 'active']) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                        Alle aktiven Verlosungen anzeigen →
                    </a>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-gray-600 mb-4">Du hast noch keine aktiven Verlosungen</p>
                    <div class="inline-block bg-gray-300 text-gray-600 font-semibold py-2 px-6 rounded-lg cursor-not-allowed">
                        Produkterstellung (Coming Soon)
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Performance-Metriken -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-semibold text-gray-600 mb-2">Durchschnittspreis</h3>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['avg_sale_price'] ?? 0, 2, ',', '.') }} €</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-semibold text-gray-600 mb-2">Abgeschlossene Verlosungen</h3>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['completed_raffles'] }}</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-semibold text-gray-600 mb-2">Erfolgsquote</h3>
            @php
                $successRate = $stats['completed_raffles'] > 0 
                    ? round(($stats['completed_raffles'] / ($stats['total_products'] ?: 1)) * 100, 1) 
                    : 0;
            @endphp
            <p class="text-2xl font-bold text-gray-800">{{ $successRate }}%</p>
        </div>
    </div>
</div>
@endsection