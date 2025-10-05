@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header mit Action Button -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Meine Produkte</h1>
            <p class="text-gray-600 mt-2">Verwalte alle deine Verlosungen an einem Ort</p>
        </div>
        <div class="bg-gray-300 text-gray-600 font-semibold py-3 px-6 rounded-lg cursor-not-allowed flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Neues Produkt (Coming Soon)
        </div>
    </div>

    <!-- Filter & Suche -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('seller.products.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Suche -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Suche</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Titel, Marke..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Alle Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Entwurf</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktiv</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Geplant</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Abgeschlossen</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Abgebrochen</option>
                </select>
            </div>

            <!-- Kategorie Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kategorie</label>
                <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                    <option value="all">Alle Kategorien</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }} ({{ $cat->products_count }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sortierung -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sortierung</label>
                <select name="sort" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Neueste zuerst</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Älteste zuerst</option>
                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Preis absteigend</option>
                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Preis aufsteigend</option>
                    <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Name A-Z</option>
                </select>
            </div>

            <!-- Submit Buttons -->
            <div class="md:col-span-4 flex justify-end space-x-2">
                <a href="{{ route('seller.products.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-300">
                    Zurücksetzen
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition duration-300">
                    Filtern
                </button>
            </div>
        </form>
    </div>

    <!-- Produktliste -->
    @if($products->count() > 0)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Desktop Tabelle -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Produkt
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Zielpreis
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lose verkauft
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fortschritt
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Endet am
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aktionen
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($products as $product)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <!-- Produkt mit Bild -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-16 w-16">
                                        @if($product->images()->exists())
                                            <img class="h-16 w-16 rounded object-cover" 
                                                 src="{{ $product->images()->where('is_primary', true)->first()->image_path ?? $product->images()->first()->image_path }}" 
                                                 alt="{{ $product->title }}">
                                        @else
                                            <div class="h-16 w-16 rounded bg-gray-200 flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $product->title }}</div>
                                        <div class="text-sm text-gray-500">{{ $product->category->name ?? 'Keine Kategorie' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusClasses = [
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'active' => 'bg-green-100 text-green-800',
                                        'scheduled' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-purple-100 text-purple-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                    ];
                                    
                                    $statusLabels = [
                                        'draft' => 'Entwurf',
                                        'active' => 'Aktiv',
                                        'scheduled' => 'Geplant',
                                        'completed' => 'Abgeschlossen',
                                        'cancelled' => 'Abgebrochen',
                                    ];
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses[$product->status] }}">
                                    {{ $statusLabels[$product->status] }}
                                </span>
                            </td>

                            <!-- Zielpreis -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($product->target_price, 2, ',', '.') }} €
                            </td>

                            <!-- Lose verkauft -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($product->raffle)
                                    {{ $product->raffle->tickets_sold }} Lose
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <!-- Fortschritt -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->raffle)
                                    @php
                                        $progress = ($product->raffle->total_target > 0) 
                                            ? ($product->raffle->total_revenue / $product->raffle->total_target) * 100 
                                            : 0;
                                    @endphp
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ min(100, $progress) }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">{{ number_format($progress, 0) }}%</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">Keine Verlosung</span>
                                @endif
                            </td>

                            <!-- Endet am -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($product->raffle && $product->raffle->ends_at)
                                    {{ \Carbon\Carbon::parse($product->raffle->ends_at)->format('d.m.Y H:i') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <!-- Aktionen -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('seller.products.show', $product->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Details anzeigen">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    
                                    @if($product->status === 'draft')
                                        <span class="text-gray-400 cursor-not-allowed" title="Bearbeiten (Coming Soon)">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </span>
                                    @endif
                                    
                                    <a href="{{ route('raffles.show', $product->slug) }}" 
                                       class="text-purple-600 hover:text-purple-900" title="Öffentliche Ansicht" target="_blank">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="lg:hidden divide-y divide-gray-200">
                @foreach($products as $product)
                <div class="p-4">
                    <div class="flex items-start space-x-4">
                        <!-- Produktbild -->
                        <div class="flex-shrink-0">
                            @if($product->images()->exists())
                                <img class="h-20 w-20 rounded object-cover" 
                                     src="{{ $product->images()->where('is_primary', true)->first()->image_path ?? $product->images()->first()->image_path }}" 
                                     alt="{{ $product->title }}">
                            @else
                                <div class="h-20 w-20 rounded bg-gray-200 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Produktinfo -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $product->title }}</h3>
                                @php
                                    $statusClasses = [
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'active' => 'bg-green-100 text-green-800',
                                        'scheduled' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-purple-100 text-purple-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                    ];
                                    $statusLabels = [
                                        'draft' => 'Entwurf',
                                        'active' => 'Aktiv',
                                        'scheduled' => 'Geplant',
                                        'completed' => 'Abgeschlossen',
                                        'cancelled' => 'Abgebrochen',
                                    ];
                                @endphp
                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses[$product->status] }}">
                                    {{ $statusLabels[$product->status] }}
                                </span>
                            </div>

                            <div class="space-y-1 text-sm text-gray-600">
                                <p>Zielpreis: <span class="font-semibold">{{ number_format($product->target_price, 2, ',', '.') }} €</span></p>
                                @if($product->raffle)
                                    <p>Lose verkauft: <span class="font-semibold">{{ $product->raffle->tickets_sold }}</span></p>
                                    @php
                                        $progress = ($product->raffle->total_target > 0) 
                                            ? ($product->raffle->total_revenue / $product->raffle->total_target) * 100 
                                            : 0;
                                    @endphp
                                    <div class="flex items-center">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ min(100, $progress) }}%"></div>
                                        </div>
                                        <span class="text-xs">{{ number_format($progress, 0) }}%</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-3 flex space-x-2">
                                <a href="{{ route('seller.products.show', $product->id) }}" 
                                   class="flex-1 text-center bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold py-2 px-3 rounded transition duration-300">
                                    Details
                                </a>
                                @if($product->status === 'draft')
                                    <div class="flex-1 text-center bg-gray-300 text-gray-600 text-xs font-semibold py-2 px-3 rounded cursor-not-allowed">
                                        Bearbeiten (Soon)
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @else
        <!-- Leer-Zustand -->
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Keine Produkte gefunden</h3>
            <p class="text-gray-600 mb-6">
                @if(request()->hasAny(['search', 'status', 'category']))
                    Keine Produkte entsprechen deinen Filterkriterien. Versuche es mit anderen Filtern.
                @else
                    Du hast noch keine Produkte erstellt. Starte jetzt deine erste Verlosung!
                @endif
            </p>
            
            @if(request()->hasAny(['search', 'status', 'category']))
                <a href="{{ route('seller.products.index') }}" 
                   class="inline-block bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 mr-2">
                    Filter zurücksetzen
                </a>
            @endif
            
            <div class="inline-block bg-gray-300 text-gray-600 font-semibold py-3 px-6 rounded-lg cursor-not-allowed">
                Produkt erstellen (Coming Soon)
            </div>
        </div>
    @endif
</div>
@endsection