@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold mb-4">🎯 Aktive Verlosungen</h1>
        <p class="text-gray-600">Sichere dir für nur 1€ die Chance auf dein Wunschprodukt!</p>
    </div>

    {{-- Sortierung --}}
    <div class="mb-6 flex justify-between items-center">
        <div class="flex gap-2">
            <a href="{{ route('raffles.index', ['sort' => 'newest']) }}" 
               class="px-4 py-2 rounded {{ request('sort', 'newest') == 'newest' ? 'bg-yellow-500 text-white' : 'bg-gray-200' }}">
                Neueste
            </a>
            <a href="{{ route('raffles.index', ['sort' => 'popular']) }}" 
               class="px-4 py-2 rounded {{ request('sort') == 'popular' ? 'bg-yellow-500 text-white' : 'bg-gray-200' }}">
                Beliebt
            </a>
            <a href="{{ route('raffles.index', ['sort' => 'ending']) }}" 
               class="px-4 py-2 rounded {{ request('sort') == 'ending' ? 'bg-yellow-500 text-white' : 'bg-gray-200' }}">
                Bald endend
            </a>
        </div>
    </div>

    {{-- Produkte Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($products as $product)
            @php
                // Lade die Bilder mit der funktionierenden Methode
                $productImages = $product->images()->get();
            @endphp
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                {{-- Bildanzeige mit product_images Tabelle --}}
                <div class="relative h-64 bg-gray-100">
                    @if($productImages->count() > 0)
                        @php
                            $mainImage = $productImages->where('is_primary', true)->first() 
                                      ?? $productImages->first();
                        @endphp
                        <img src="{{ $mainImage->image_path }}" 
                             alt="{{ $mainImage->alt_text ?? $product->title }}"
                             class="w-full h-full object-cover"
                             onerror="this.onerror=null; this.src='https://dummyimage.com/400x400/cccccc/666666?text=Bild+nicht+verfügbar'">
                    @else
                        <img src="https://dummyimage.com/400x400/{{ substr(md5($product->title), 0, 6) }}/FFFFFF?text={{ urlencode($product->title) }}" 
                             alt="{{ $product->title }}"
                             class="w-full h-full object-cover">
                    @endif
                    
                    {{-- Status Badge --}}
                    @if($product->raffle && $product->raffle->status == 'active')
                        <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded text-xs font-bold">
                            AKTIV
                        </div>
                    @endif
                </div>

                {{-- Produktinfo --}}
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-1 line-clamp-1">{{ $product->title }}</h3>
                    
                    @if($product->brand)
                        <p class="text-sm text-gray-500 mb-2">{{ $product->brand }}</p>
                    @endif
                    
                    {{-- Raffle Info --}}
                    @if($product->raffle)
                        <div class="mb-3">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-600">Zielpreis:</span>
                                <span class="font-bold text-green-600">{{ number_format($product->target_price, 0, ',', '.') }} €</span>
                            </div>
                            
                            {{-- Progress Bar --}}
                            <div class="mb-2">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>{{ $product->raffle->tickets_sold }} Lose</span>
                                    <span>{{ number_format($product->raffle->total_target, 0, ',', '.') }} € Ziel</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    @php
                                        $progress = ($product->raffle->tickets_sold / max($product->raffle->total_target, 1)) * 100;
                                    @endphp
                                    <div class="bg-gradient-to-r from-yellow-400 to-yellow-600 h-2 rounded-full transition-all duration-500" 
                                         style="width: {{ min($progress, 100) }}%"></div>
                                </div>
                            </div>
                            
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-clock"></i> 
                                Endet {{ $product->raffle->ends_at->diffForHumans() }}
                            </p>
                        </div>
                    @endif

                    {{-- CTA Button mit Slug --}}
                    <a href="{{ route('raffles.show', $product->slug) }}" 
                       class="block w-full bg-yellow-500 text-center text-white py-2 rounded-lg hover:bg-yellow-600 font-semibold transition-colors">
                        Jetzt mitspielen →
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Keine Verlosungen gefunden</h3>
                    <p class="text-gray-600">Aktuell sind keine aktiven Verlosungen verfügbar.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection