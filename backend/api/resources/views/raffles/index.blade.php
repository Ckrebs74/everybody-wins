@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-8">Aktive Verlosungen</h1>

    {{-- Produkte Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($products as $product)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                {{-- Bildanzeige --}}
                <div class="aspect-w-1 aspect-h-1 bg-gray-100">
                    @if($product->images && $product->images->count() > 0)
                        @php
                            // Primärbild oder erstes Bild verwenden
                            $mainImage = $product->images->where('is_primary', true)->first() 
                                ?? $product->images->first();
                        @endphp
                        <img src="{{ $mainImage->thumbnail_path ?? $mainImage->image_path }}" 
                             alt="{{ $mainImage->alt_text ?? $product->title }}"
                             class="w-full h-64 object-cover"
                             onerror="this.src='https://dummyimage.com/400x400/cccccc/666666?text=No+Image'">
                    @else
                        <img src="https://dummyimage.com/400x400/cccccc/666666?text={{ urlencode($product->title) }}" 
                             alt="{{ $product->title }}"
                             class="w-full h-64 object-cover">
                    @endif
                </div>

                {{-- Produktinfo --}}
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-1">{{ $product->title }}</h3>
                    
                    @if($product->raffle)
                        <div class="mb-3">
                            <div class="flex justify-between text-sm mb-2">
                                <span>Fortschritt:</span>
                                <span class="font-bold">{{ number_format($product->raffle->tickets_sold) }} / {{ number_format($product->raffle->total_target) }} €</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                @php
                                    $progress = ($product->raffle->tickets_sold / $product->raffle->total_target) * 100;
                                @endphp
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ min($progress, 100) }}%"></div>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-3">
                            Endet: {{ $product->raffle->ends_at->diffForHumans() }}
                        </p>
                    @endif

                    <a href="{{ route('raffles.show', $product->id) }}" 
                       class="block w-full bg-yellow-500 text-center text-white py-2 rounded-lg hover:bg-yellow-600 font-semibold">
                        Details ansehen →
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-8">
        {{ $products->links() }}
    </div>
</div>
@endsection