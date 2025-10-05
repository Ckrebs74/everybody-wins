@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header Section --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h1 class="text-3xl font-bold mb-6">
            🎯 Aktive Verlosungen
        </h1>
        
        {{-- Search and Filter Bar --}}
        <form method="GET" action="{{ route('raffles.index') }}" class="flex flex-wrap gap-4">
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Suche nach Produkten..."
                   class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            
            <select name="category" 
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                <option value="all">Alle Kategorien</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }} ({{ $cat->products_count }})
                    </option>
                @endforeach
            </select>
            
            <select name="sort" 
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>
                    Neueste zuerst
                </option>
                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>
                    Preis aufsteigend
                </option>
                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>
                    Preis absteigend
                </option>
                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>
                    Beliebteste
                </option>
            </select>
            
            <button type="submit" 
                    class="bg-yellow-500 text-white px-6 py-2 rounded-lg hover:bg-yellow-600 transition-colors">
                Suchen
            </button>
        </form>
    </div>

    {{-- Products Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($products as $product)
            @php
                // Hole das Hauptbild
                $imageUrl = '';
                if ($product->images && $product->images->count() > 0) {
                    $primaryImage = $product->images->where('is_primary', true)->first();
                    if ($primaryImage) {
                        $imageUrl = $primaryImage->image_path;
                    } else {
                        $firstImage = $product->images->first();
                        $imageUrl = $firstImage ? $firstImage->image_path : '';
                    }
                }
                
                // Fallback wenn kein Bild
                if (empty($imageUrl)) {
                    $imageUrl = 'https://dummyimage.com/400x400/FFD700/333333?text=' . urlencode($product->title);
                }
            @endphp
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                {{-- Product Image with Badges --}}
                <a href="{{ route('raffles.show', $product->id) }}" class="block relative group">
                    <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden bg-gray-100">
                        <img src="{{ $imageUrl }}" 
                             alt="{{ $product->title }}"
                             class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-300"
                             loading="lazy">
                        
                        {{-- Condition Badge --}}
                        @if(isset($product->condition))
                            @if($product->condition == 'new')
                                <span class="absolute top-2 left-2 bg-green-500 text-white px-2 py-1 text-xs font-semibold rounded">
                                    NEU
                                </span>
                            @elseif($product->condition == 'like_new')
                                <span class="absolute top-2 left-2 bg-blue-500 text-white px-2 py-1 text-xs font-semibold rounded">
                                    WIE NEU
                                </span>
                            @endif
                        @endif

                        {{-- Time Badge --}}
                        @if($product->raffle && $product->raffle->ends_at)
                            @php
                                $hoursLeft = $product->raffle->ends_at->diffInHours(now());
                            @endphp
                            @if($hoursLeft <= 24 && $hoursLeft > 0)
                                <span class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 text-xs font-semibold rounded animate-pulse">
                                    ⏰ {{ $hoursLeft }}h
                                </span>
                            @elseif($hoursLeft <= 0)
                                <span class="absolute top-2 right-2 bg-gray-800 text-white px-2 py-1 text-xs font-semibold rounded">
                                    BEENDET
                                </span>
                            @endif
                        @endif

                        {{-- Category Badge --}}
                        @if($product->category)
                            <span class="absolute bottom-2 left-2 bg-black bg-opacity-60 text-white px-2 py-1 text-xs rounded">
                                {{ $product->category->name }}
                            </span>
                        @endif
                    </div>
                </a>

                {{-- Product Info --}}
                <div class="p-4">
                    {{-- Title --}}
                    <h3 class="font-semibold text-lg mb-2 line-clamp-2 min-h-[3.5rem]">
                        <a href="{{ route('raffles.show', $product->id) }}" 
                           class="hover:text-yellow-600 transition-colors">
                            {{ $product->title }}
                        </a>
                    </h3>

                    {{-- Brand --}}
                    @if(isset($product->brand) && $product->brand)
                        <p class="text-sm text-gray-500 mb-2">
                            <span class="font-medium">{{ $product->brand }}</span>
                        </p>
                    @endif

                    {{-- Progress Bar from Raffle --}}
                    @if($product->raffle)
                        <div class="mb-3">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>{{ $product->raffle->tickets_sold }} Lose verkauft</span>
                                <span class="font-semibold">{{ round(($product->raffle->total_revenue / max($product->raffle->total_target, 1)) * 100, 1) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                <div class="bg-gradient-to-r from-yellow-400 to-yellow-600 h-2 rounded-full transition-all duration-500"
                                     style="width: {{ min(($product->raffle->total_revenue / max($product->raffle->total_target, 1)) * 100, 100) }}%">
                                </div>
                            </div>
                        </div>

                        {{-- Price Info --}}
                        <div class="flex justify-between items-center mb-3">
                            <div>
                                <p class="text-2xl font-bold text-yellow-600">1€</p>
                                <p class="text-xs text-gray-500">pro Los</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Wert</p>
                                <p class="font-semibold text-lg">{{ number_format($product->target_price ?? 0, 0) }}€</p>
                            </div>
                        </div>

                        {{-- Time Remaining --}}
                        @if($product->raffle->ends_at)
                            <p class="text-xs text-gray-500 text-center mt-2">
                                <i class="far fa-clock"></i> Endet {{ $product->raffle->ends_at->diffForHumans() }}
                            </p>
                        @endif
                    @endif

                    {{-- CTA Button --}}
                    <a href="{{ route('raffles.show', $product->id) }}" 
                       class="block w-full bg-yellow-500 text-center text-white py-2 rounded-lg hover:bg-yellow-600 transition-colors font-semibold mt-3">
                        Jetzt teilnehmen →
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <div class="text-6xl mb-4">🎯</div>
                    <h3 class="text-xl font-semibold mb-2">Keine Verlosungen gefunden</h3>
                    <p class="text-gray-600">Aktuell sind keine aktiven Verlosungen verfügbar.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @endif

    {{-- Quick Stats --}}
    @if($products->count() > 0)
        <div class="mt-8 bg-gray-100 rounded-lg p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div>
                    <p class="text-3xl font-bold text-yellow-600">{{ $products->count() }}</p>
                    <p class="text-sm text-gray-600">Aktive Verlosungen</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-green-600">{{ $categories->count() }}</p>
                    <p class="text-sm text-gray-600">Kategorien</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ $products->sum(function($p) { return $p->raffle ? $p->raffle->tickets_sold : 0; }) }}
                    </p>
                    <p class="text-sm text-gray-600">Verkaufte Lose</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-purple-600">1€</p>
                    <p class="text-sm text-gray-600">Standard Lospreis</p>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- CSS for line-clamp --}}
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection