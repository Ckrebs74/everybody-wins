@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-6">
            
            {{-- Image Gallery Section --}}
            <div>
                {{-- Main Image --}}
                <div class="mb-4 relative">
                    @php
                        $mainImageUrl = $product->images->first()->image_path ?? $product->primary_image_url ?? 'https://via.placeholder.com/600x600/FFD700/333333?text=' . urlencode($product->title);
                    @endphp
                    <img id="mainImage" 
                         src="{{ $mainImageUrl }}" 
                         alt="{{ $product->title }}"
                         class="w-full h-96 object-contain bg-gray-100 rounded-lg"
                         onerror="this.src='https://via.placeholder.com/600x600/FFD700/333333?text={{ urlencode($product->title) }}'">
                    
                    @if($product->images->count() > 1)
                        {{-- Navigation Arrows --}}
                        <button onclick="previousImage()" 
                                class="absolute left-2 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button onclick="nextImage()" 
                                class="absolute right-2 top-1/2 -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    @endif

                    {{-- Image Counter --}}
                    @if($product->images->count() > 1)
                        <div class="absolute bottom-2 right-2 bg-black bg-opacity-60 text-white px-3 py-1 rounded-lg text-sm">
                            <span id="imageCounter">1</span> / {{ $product->images->count() }}
                        </div>
                    @endif
                </div>

                {{-- Thumbnail Gallery --}}
                @if($product->images->count() > 0)
                    <div class="grid grid-cols-5 gap-2">
                        @foreach($product->images as $index => $image)
                            <img src="{{ $image->thumbnail_path ?? $image->image_path }}" 
                                 alt="{{ $product->title }} - Bild {{ $index + 1 }}"
                                 data-full-url="{{ $image->image_path }}"
                                 onclick="changeMainImage(this, {{ $index }})"
                                 class="thumbnail w-full h-20 object-cover rounded cursor-pointer border-2 hover:border-yellow-500 transition-colors {{ $index === 0 ? 'border-yellow-500' : 'border-gray-300' }}"
                                 onerror="this.src='https://via.placeholder.com/150x150/FFD700/333333?text=Bild+{{ $index + 1 }}'">
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Product Information Section --}}
            <div>
                {{-- Category & Condition Badges --}}
                <div class="flex gap-2 mb-3">
                    @if($product->category)
                        <span class="inline-block bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm">
                            {{ $product->category->name }}
                        </span>
                    @endif
                    @if(isset($product->condition))
                        @if($product->condition == 'new')
                            <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                NEU
                            </span>
                        @elseif($product->condition == 'like_new')
                            <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                WIE NEU
                            </span>
                        @elseif($product->condition == 'good')
                            <span class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold">
                                GUT
                            </span>
                        @else
                            <span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-semibold">
                                AKZEPTABEL
                            </span>
                        @endif
                    @endif
                </div>

                {{-- Title --}}
                <h1 class="text-3xl font-bold mb-2">{{ $product->title }}</h1>

                {{-- Brand & Model --}}
                @if($product->brand || $product->model_number)
                    <div class="flex gap-4 text-gray-600 mb-4">
                        @if($product->brand)
                            <span>Marke: <strong>{{ $product->brand }}</strong></span>
                        @endif
                        @if($product->model_number)
                            <span>Modell: <strong>{{ $product->model_number }}</strong></span>
                        @endif
                    </div>
                @endif

                {{-- Progress Section --}}
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 p-4 rounded-lg mb-6">
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-semibold">Fortschritt</span>
                        <span class="text-sm font-semibold">{{ $stats['progress_percentage'] ?? 0 }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 mb-3 overflow-hidden">
                        <div class="bg-gradient-to-r from-yellow-400 to-yellow-600 h-3 rounded-full transition-all duration-500"
                             style="width: {{ min($stats['progress_percentage'] ?? 0, 100) }}%"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div>
                            <p class="text-2xl font-bold text-yellow-600">{{ $stats['tickets_sold'] ?? 0 }}</p>
                            <p class="text-xs text-gray-600">Lose verkauft</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-green-600">{{ number_format($product->ticket_price ?? 1, 0) }}€</p>
                            <p class="text-xs text-gray-600">pro Los</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($product->target_price ?? 0, 0) }}€</p>
                            <p class="text-xs text-gray-600">Produktwert</p>
                        </div>
                    </div>
                </div>

                {{-- Status Info --}}
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                    <p class="font-semibold text-blue-700">📍 Status der Verlosung:</p>
                    @if($product->status == 'active')
                        <p class="text-lg font-bold text-blue-600">AKTIV - Lose können gekauft werden</p>
                    @else
                        <p class="text-lg font-bold text-gray-600">{{ strtoupper($product->status) }}</p>
                    @endif
                    <p class="text-sm text-gray-600 mt-1">
                        Erstellt: {{ $product->created_at->format('d.m.Y \u\m H:i \U\h\r') }}
                    </p>
                </div>

                {{-- Purchase Section --}}
                @auth
                    @if($product->seller_id !== Auth::id())
                        @if($product->isActive())
                            <form method="POST" action="{{ route('tickets.purchase') }}" class="mb-6">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                
                                <div class="flex items-center gap-4 mb-4">
                                    <label class="text-sm font-semibold">Anzahl Lose:</label>
                                    <div class="flex items-center">
                                        <button type="button" onclick="decreaseQuantity()" 
                                                class="bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded-l">-</button>
                                        <input type="number" 
                                               id="ticketQuantity"
                                               name="quantity" 
                                               value="1" 
                                               min="1" 
                                               max="10"
                                               readonly
                                               class="w-16 text-center px-2 py-1 border-t border-b">
                                        <button type="button" onclick="increaseQuantity()" 
                                                class="bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded-r">+</button>
                                    </div>
                                    <span class="text-sm text-gray-600">
                                        = <span id="totalPrice" class="font-bold">{{ number_format($product->ticket_price ?? 1, 0) }}€</span>
                                    </span>
                                </div>
                                
                                <div class="bg-blue-50 p-3 rounded-lg mb-4 text-sm">
                                    <p class="text-blue-700">
                                        ⚠️ Maximal 10€ pro Stunde erlaubt (Spielerschutz)
                                    </p>
                                </div>
                                
                                <button type="submit" 
                                        class="w-full bg-yellow-500 text-white py-3 rounded-lg font-semibold text-lg hover:bg-yellow-600 transition-colors">
                                    🎫 Lose kaufen und teilnehmen
                                </button>
                            </form>
                        @else
                            <div class="bg-gray-100 p-4 rounded-lg mb-6 text-center">
                                <p class="text-gray-700 font-semibold">Diese Verlosung ist nicht aktiv</p>
                            </div>
                        @endif
                    @else
                        <div class="bg-blue-50 p-4 rounded-lg mb-6">
                            <p class="text-blue-700">Dies ist dein eigenes Produkt.</p>
                            <a href="/seller/products/{{ $product->id }}/edit" 
                               class="text-blue-600 underline">Produkt bearbeiten →</a>
                        </div>
                    @endif
                @else
                    <a href="{{ route('login') }}" 
                       class="block w-full bg-gray-400 text-white py-3 rounded-lg font-semibold text-lg text-center hover:bg-gray-500 transition-colors">
                        Zum Kauf anmelden
                    </a>
                    <p class="text-center text-sm text-gray-600 mt-2">
                        Noch kein Konto? 
                        <a href="{{ route('register') }}" class="text-yellow-600 underline">
                            Jetzt registrieren und 5€ Startguthaben sichern!
                        </a>
                    </p>
                @endauth

                {{-- Seller Info --}}
                <div class="border-t pt-6 mb-6">
                    <h3 class="font-semibold mb-3">Verkäufer-Informationen</h3>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
                            {{ substr($product->seller->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <p class="font-semibold">{{ $product->seller->name ?? 'Unbekannt' }}</p>
                            @if($product->seller)
                                <p class="text-sm text-gray-600">
                                    Mitglied seit {{ $product->seller->created_at->format('M Y') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Shipping Info --}}
                <div class="border-t pt-6">
                    <h3 class="font-semibold mb-3">Versandinformationen</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Versandkosten</p>
                            <p class="font-semibold">
                                @if(($product->shipping_cost ?? 0) > 0)
                                    {{ number_format($product->shipping_cost, 2) }}€
                                @else
                                    Kostenloser Versand
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Versandart</p>
                            <p class="font-semibold">
                                {{ $product->shipping_info ?? 'Standardversand' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Description Section --}}
        <div class="border-t p-6">
            <h2 class="text-2xl font-bold mb-4">Produktbeschreibung</h2>
            <div class="prose max-w-none text-gray-700">
                {!! nl2br(e($product->description)) !!}
            </div>
        </div>

        {{-- Recent Participants --}}
        @if($recentParticipants->count() > 0)
            <div class="border-t p-6 bg-gray-50">
                <h2 class="text-2xl font-bold mb-4">
                    👥 Bisherige Teilnehmer ({{ $stats['participants'] ?? 0 }})
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    @foreach($recentParticipants as $participant)
                        <div class="bg-white p-3 rounded-lg text-center shadow-sm">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold mx-auto mb-2">
                                {{ $participant['name'][0] }}
                            </div>
                            <p class="font-semibold text-sm">{{ $participant['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ $participant['time'] }}</p>
                        </div>
                    @endforeach
                </div>
                @if(($stats['tickets_sold'] ?? 0) > 20)
                    <p class="text-center text-gray-600 mt-4">
                        ... und {{ $stats['tickets_sold'] - 20 }} weitere Teilnehmer
                    </p>
                @endif
            </div>
        @endif

        {{-- Related Products --}}
        @if($relatedProducts->count() > 0)
            <div class="border-t p-6">
                <h2 class="text-2xl font-bold mb-4">🎯 Ähnliche Verlosungen</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($relatedProducts as $related)
                        <a href="{{ route('raffles.show', $related->id) }}" 
                           class="bg-white border rounded-lg overflow-hidden hover:shadow-lg transition-shadow group">
                            <div class="aspect-w-1 aspect-h-1 bg-gray-100">
                                <img src="{{ $related->primary_image_url ?? 'https://via.placeholder.com/300x300/FFD700/333333?text=' . urlencode($related->title) }}" 
                                     alt="{{ $related->title }}"
                                     class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-300"
                                     loading="lazy"
                                     onerror="this.src='https://via.placeholder.com/300x300/FFD700/333333?text={{ urlencode($related->title) }}'">
                            </div>
                            <div class="p-3">
                                <p class="font-semibold text-sm line-clamp-2 mb-2">{{ $related->title }}</p>
                                <div class="flex justify-between items-center">
                                    <p class="text-yellow-600 font-bold">{{ number_format($related->ticket_price ?? 1, 0) }}€</p>
                                    <p class="text-xs text-gray-500">{{ $related->tickets_sold ?? 0 }} Lose</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<script>
// Image Gallery Functions
let currentImageIndex = 0;
const thumbnails = document.querySelectorAll('.thumbnail');
const totalImages = thumbnails.length;

function changeMainImage(thumbnail, index) {
    document.getElementById('mainImage').src = thumbnail.dataset.fullUrl;
    currentImageIndex = index;
    
    // Update active thumbnail
    thumbnails.forEach(t => {
        t.classList.remove('border-yellow-500');
        t.classList.add('border-gray-300');
    });
    thumbnail.classList.remove('border-gray-300');
    thumbnail.classList.add('border-yellow-500');
    
    // Update counter
    if (document.getElementById('imageCounter')) {
        document.getElementById('imageCounter').textContent = index + 1;
    }
}

function nextImage() {
    if (totalImages > 0) {
        currentImageIndex = (currentImageIndex + 1) % totalImages;
        thumbnails[currentImageIndex].click();
    }
}

function previousImage() {
    if (totalImages > 0) {
        currentImageIndex = (currentImageIndex - 1 + totalImages) % totalImages;
        thumbnails[currentImageIndex].click();
    }
}

// Keyboard navigation
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') previousImage();
    if (e.key === 'ArrowRight') nextImage();
});

// Quantity Controls
const ticketPrice = {{ $product->ticket_price ?? 1 }};

function increaseQuantity() {
    const input = document.getElementById('ticketQuantity');
    const currentValue = parseInt(input.value);
    if (currentValue < 10) {
        input.value = currentValue + 1;
        updateTotalPrice(currentValue + 1);
    }
}

function decreaseQuantity() {
    const input = document.getElementById('ticketQuantity');
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
        updateTotalPrice(currentValue - 1);
    }
}

function updateTotalPrice(quantity) {
    const total = quantity * ticketPrice;
    document.getElementById('totalPrice').textContent = total.toFixed(0) + '€';
}
</script>
@endsection