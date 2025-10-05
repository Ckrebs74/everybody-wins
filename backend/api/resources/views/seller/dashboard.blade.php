{{-- resources/views/seller/dashboard.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- Header mit Haupt-CTA --}}
    <div class="mb-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-8 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="mb-4 md:mb-0">
                <h1 class="text-3xl font-bold mb-2">Willkommen zurück, {{ Auth::user()->first_name ?? 'Verkäufer' }}!</h1>
                <p class="text-blue-100">Verwalten Sie Ihre Verlosungen und erstellen Sie neue Produkte</p>
            </div>
            
            {{-- HAUPT-CTA: Produkt erstellen --}}
            <a href="{{ route('seller.products.create') }}" 
               class="inline-flex items-center px-8 py-4 bg-white text-blue-600 rounded-lg font-bold text-lg hover:bg-blue-50 transition shadow-lg hover:shadow-xl transform hover:scale-105">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Produkt erstellen
            </a>
        </div>
    </div>
    
    {{-- Statistik-Karten --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Aktive Verlosungen --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-500">Aktiv</span>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['active_raffles'] ?? 0 }}</p>
            <p class="text-sm text-gray-500 mt-1">Laufende Verlosungen</p>
        </div>
        
        {{-- Gesamterlös --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-500">Erlös</span>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_revenue'] ?? 0, 2, ',', '.') }} €</p>
            <p class="text-sm text-gray-500 mt-1">Gesamteinnahmen</p>
        </div>
        
        {{-- Verkaufte Lose --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-500">Lose</span>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_tickets'] ?? 0, 0, ',', '.') }}</p>
            <p class="text-sm text-gray-500 mt-1">Verkaufte Lose</p>
        </div>
        
        {{-- Erfolgsquote --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-500">Erfolg</span>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['success_rate'] ?? 0, 1) }}%</p>
            <p class="text-sm text-gray-500 mt-1">Zielpreis erreicht</p>
        </div>
    </div>
    
    {{-- Tabs Navigation --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="switchTab('active')" 
                        id="tab-active"
                        class="tab-button border-b-2 border-blue-500 text-blue-600 py-4 px-1 text-sm font-medium">
                    Aktive Verlosungen
                    @if(isset($activeProducts) && $activeProducts->count() > 0)
                        <span class="ml-2 bg-blue-100 text-blue-600 py-0.5 px-2 rounded-full text-xs">
                            {{ $activeProducts->count() }}
                        </span>
                    @endif
                </button>
                
                <button onclick="switchTab('drafts')" 
                        id="tab-drafts"
                        class="tab-button border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-4 px-1 text-sm font-medium">
                    Entwürfe
                    @if(isset($draftProducts) && $draftProducts->count() > 0)
                        <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">
                            {{ $draftProducts->count() }}
                        </span>
                    @endif
                </button>
                
                <button onclick="switchTab('completed')" 
                        id="tab-completed"
                        class="tab-button border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-4 px-1 text-sm font-medium">
                    Abgeschlossen
                </button>
            </nav>
        </div>
    </div>
    
    {{-- Aktive Verlosungen --}}
    <div id="content-active" class="tab-content">
        @if(isset($activeProducts) && $activeProducts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($activeProducts as $product)
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition">
                        {{-- Produktbild --}}
                        @php
                            $primaryImage = $product->images()->where('is_primary', true)->first() 
                                         ?? $product->images()->first();
                        @endphp
                        
                        @if($primaryImage)
                            <a href="{{ route('seller.products.show', $product->id) }}">
                                <img src="{{ $primaryImage->image_path }}" 
                                     alt="{{ $product->title }}"
                                     class="w-full h-48 object-cover rounded-t-lg">
                            </a>
                        @else
                            <div class="w-full h-48 bg-gray-100 rounded-t-lg flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                        
                        <div class="p-4">
                            {{-- Titel --}}
                            <a href="{{ route('seller.products.show', $product->id) }}" 
                               class="text-lg font-semibold text-gray-900 hover:text-blue-600 line-clamp-2">
                                {{ $product->title }}
                            </a>
                            
                            {{-- Statistiken --}}
                            @if($product->raffle)
                                <div class="mt-3 space-y-2">
                                    {{-- Fortschritt --}}
                                    <div>
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-600">Fortschritt</span>
                                            <span class="font-medium">
                                                {{ number_format(($product->raffle->total_revenue / $product->raffle->total_target) * 100, 1) }}%
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" 
                                                 style="width: {{ min(100, ($product->raffle->total_revenue / $product->raffle->total_target) * 100) }}%">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Lose verkauft --}}
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Lose verkauft:</span>
                                        <span class="font-medium">{{ $product->raffle->tickets_sold }}</span>
                                    </div>
                                    
                                    {{-- Endet am --}}
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Endet:</span>
                                        <span class="font-medium">
                                            {{ \Carbon\Carbon::parse($product->raffle->ends_at)->format('d.m.Y H:i') }}
                                        </span>
                                    </div>
                                </div>
                                
                                {{-- Actions --}}
                                <div class="mt-4 flex space-x-2">
                                    <a href="{{ route('raffles.show', $product->slug) }}" 
                                       target="_blank"
                                       class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 text-center rounded hover:bg-gray-200 text-sm">
                                        Ansehen
                                    </a>
                                    <a href="{{ route('seller.products.show', $product->id) }}" 
                                       class="flex-1 px-3 py-2 bg-blue-600 text-white text-center rounded hover:bg-blue-700 text-sm">
                                        Details
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Keine aktiven Verlosungen --}}
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Keine aktiven Verlosungen</h3>
                <p class="text-gray-500 mb-6">Erstellen Sie Ihr erstes Produkt und starten Sie eine Verlosung</p>
                <a href="{{ route('seller.products.create') }}" 
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Produkt erstellen
                </a>
            </div>
        @endif
    </div>
    
    {{-- Entwürfe --}}
    <div id="content-drafts" class="tab-content hidden">
        @if(isset($draftProducts) && $draftProducts->count() > 0)
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Unvollständige Entwürfe</h3>
                    
                    <div class="space-y-4">
                        @foreach($draftProducts as $draft)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">
                                            {{ $draft->title ?: 'Unbenannter Entwurf' }}
                                        </h4>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Erstellt: {{ $draft->created_at->format('d.m.Y H:i') }}
                                        </p>
                                        
                                        {{-- Fortschritt --}}
                                        <div class="mt-2 flex items-center space-x-2">
                                            @php
                                                $progress = 0;
                                                if ($draft->category_id) $progress += 20;
                                                if ($draft->title && strlen($draft->title) >= 10) $progress += 20;
                                                if ($draft->description && strlen($draft->description) >= 50) $progress += 20;
                                                if ($draft->images()->count() > 0) $progress += 20;
                                                if ($draft->target_price > 0) $progress += 20;
                                            @endphp
                                            
                                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                                <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-600">{{ $progress }}%</span>
                                        </div>
                                    </div>
                                    
                                    <div class="ml-4 flex space-x-2">
                                        <a href="{{ route('seller.products.create.step', 2) }}" 
                                           class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                            Fortsetzen
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Keine Entwürfe</h3>
                <p class="text-gray-500">Alle Ihre Produkte sind bereits veröffentlicht</p>
            </div>
        @endif
    </div>
    
    {{-- Abgeschlossen --}}
    <div id="content-completed" class="tab-content hidden">
        @if(isset($completedProducts) && $completedProducts->count() > 0)
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produkt</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zielpreis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Erlös</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Abgeschlossen</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($completedProducts as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @php
                                            $img = $product->images()->where('is_primary', true)->first() 
                                                ?? $product->images()->first();
                                        @endphp
                                        @if($img)
                                            <img src="{{ $img->thumbnail_path ?: $img->image_path }}" 
                                                 class="w-10 h-10 rounded object-cover mr-3"
                                                 alt="{{ $product->title }}">
                                        @endif
                                        <span class="text-sm font-medium text-gray-900">{{ Str::limit($product->title, 40) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($product->target_price, 2, ',', '.') }} €
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($product->raffle->total_revenue ?? 0, 2, ',', '.') }} €
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->raffle && $product->raffle->target_reached)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                            Ziel erreicht
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                            Nicht erreicht
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $product->updated_at->format('d.m.Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Keine abgeschlossenen Verlosungen</h3>
                <p class="text-gray-500">Hier erscheinen Ihre beendeten Verlosungen</p>
            </div>
        @endif
    </div>
    
    {{-- Quick Actions --}}
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Schnellaktionen</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('seller.products.create') }}" 
               class="flex items-center p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">Neues Produkt</p>
                    <p class="text-sm text-gray-500">Verlosung erstellen</p>
                </div>
            </a>
            
            <a href="{{ route('seller.products.index') }}" 
               class="flex items-center p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">Alle Produkte</p>
                    <p class="text-sm text-gray-500">Übersicht verwalten</p>
                </div>
            </a>
            
            <a href="{{ route('seller.analytics') }}" 
               class="flex items-center p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">Statistiken</p>
                    <p class="text-sm text-gray-500">Detaillierte Analysen</p>
                </div>
            </a>
        </div>
    </div>
    
</div>

{{-- JavaScript für Tab-Switching --}}
<script>
function switchTab(tabName) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(el => {
        el.classList.add('hidden');
    });
    
    // Remove active state from all buttons
    document.querySelectorAll('.tab-button').forEach(el => {
        el.classList.remove('border-blue-500', 'text-blue-600');
        el.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active state to selected button
    const button = document.getElementById('tab-' + tabName);
    button.classList.add('border-blue-500', 'text-blue-600');
    button.classList.remove('border-transparent', 'text-gray-500');
}
</script>
@endsection