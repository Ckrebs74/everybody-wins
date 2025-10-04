@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">🎯 Aktive Verlosungen - DEBUG MODE</h1>

    {{-- Debug Info --}}
    <div class="bg-yellow-100 p-4 mb-4 rounded">
        <p>Anzahl Produkte: {{ $products->count() }}</p>
    </div>

    {{-- Products Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($products as $product)
            @php
                // Debug: Hole das erste Bild DIREKT
                $firstImage = null;
                $imageUrl = '';
                
                // Check ob images geladen sind
                if ($product->images && $product->images->count() > 0) {
                    // Suche primary image
                    $primaryImage = $product->images->where('is_primary', 1)->first();
                    if ($primaryImage) {
                        $imageUrl = $primaryImage->image_path;
                    } else {
                        // Nimm erstes Bild
                        $firstImage = $product->images->first();
                        if ($firstImage) {
                            $imageUrl = $firstImage->image_path;
                        }
                    }
                }
                
                // Fallback
                if (empty($imageUrl)) {
                    $imageUrl = 'https://via.placeholder.com/400x400/FFD700/333333?text=' . urlencode($product->title);
                }
            @endphp
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                {{-- Debug Info --}}
                <div class="bg-red-500 text-white text-xs p-2">
                    ID: {{ $product->id }} | 
                    Bilder: {{ $product->images ? $product->images->count() : 0 }} |
                    Raffle: {{ $product->raffle ? 'JA' : 'NEIN' }}
                </div>
                
                {{-- Bild --}}
                <a href="{{ route('raffles.show', $product->id) }}" class="block">
                    <img src="{{ $imageUrl }}" 
                         alt="{{ $product->title }}"
                         class="w-full h-64 object-cover"
                         style="background-color: #f0f0f0;">
                </a>
                
                {{-- URL anzeigen zum Debuggen --}}
                <div class="bg-gray-100 text-xs p-2 break-all">
                    URL: {{ substr($imageUrl, 0, 80) }}...
                </div>

                <div class="p-4">
                    <h3 class="font-semibold text-lg mb-2">{{ $product->title }}</h3>
                    
                    @if($product->raffle)
                        <div class="mb-3">
                            <div class="text-sm text-gray-600">
                                {{ $product->raffle->tickets_sold }} Lose verkauft
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                @php
                                    $progress = 0;
                                    if ($product->raffle->total_target > 0) {
                                        $progress = min(100, ($product->raffle->total_revenue / $product->raffle->total_target) * 100);
                                    }
                                @endphp
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold text-yellow-600">1€</span>
                            <span class="text-sm">Wert: {{ number_format($product->target_price, 0) }}€</span>
                        </div>
                    @endif
                    
                    <a href="{{ route('raffles.show', $product->id) }}" 
                       class="block w-full bg-yellow-500 text-center text-white py-2 rounded mt-3 hover:bg-yellow-600">
                        Details →
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection