@extends('layouts.app')

@section('content')
@php
    // Lade die Bilder mit der funktionierenden Methode
    $productImages = $product->images()->get();
    
    // Berechne verfügbares Budget (10€ Limit pro Stunde)
    $maxSpendingPerHour = 10; // Gesetzliches Limit
    $currentHourSpending = 0; // TODO: Aus der Datenbank laden für eingeloggten User
    $remainingBudget = $maxSpendingPerHour - $currentHourSpending;
@endphp

<div class="container mx-auto px-4 py-8">
    {{-- Ausgabenlimit Warnung --}}
    @if($remainingBudget < 10)
        <div class="bg-yellow-100 border-2 border-yellow-400 rounded-lg p-4 mb-6">
            <p class="text-yellow-800 font-semibold">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Hinweis: Sie können in dieser Stunde noch maximal {{ number_format($remainingBudget, 2, ',', '.') }} € ausgeben.
                <span class="text-sm block mt-1">Gesetzliches Ausgabenlimit: 10€/Stunde</span>
            </p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        {{-- Image Gallery Section --}}
        <div>
            {{-- Main Image --}}
            <div class="mb-4 relative">
                @php
                    // Hole das Hauptbild
                    $mainImageUrl = null;
                    if($productImages->count() > 0) {
                        $mainImageUrl = $productImages->first()->image_path;
                    } else {
                        $mainImageUrl = 'https://dummyimage.com/600x600/FFD700/333333?text=' . urlencode($product->title);
                    }
                @endphp
                <img id="mainImage" 
                     src="{{ $mainImageUrl }}" 
                     alt="{{ $product->title }}"
                     class="w-full h-96 object-contain bg-gray-100 rounded-lg"
                     onerror="this.src='https://dummyimage.com/600x600/FFD700/333333?text={{ urlencode($product->title) }}'">
                
                @if($productImages->count() > 1)
                    {{-- Navigation Arrows --}}
                    <button onclick="previousImage()" 
                            class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full shadow-lg">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button onclick="nextImage()" 
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full shadow-lg">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                @endif
            </div>
            
            {{-- Thumbnail Gallery --}}
            @if($productImages->count() > 1)
                <div class="grid grid-cols-4 gap-2">
                    @foreach($productImages as $index => $image)
                        <img src="{{ $image->thumbnail_path ?? $image->image_path }}" 
                             alt="{{ $image->alt_text ?? $product->title }}"
                             class="w-full h-20 object-cover rounded cursor-pointer border-2 hover:border-yellow-500 transition-colors {{ $index === 0 ? 'border-yellow-500' : 'border-transparent' }}"
                             onclick="selectImage({{ $index }})"
                             onerror="this.src='https://dummyimage.com/150x150/cccccc/666666?text=Thumb'">
                    @endforeach
                </div>
            @endif
        </div>
        
        {{-- Product Info Section --}}
        <div>
            {{-- Title and Brand --}}
            <h1 class="text-3xl font-bold mb-2">{{ $product->title }}</h1>
            @if($product->brand)
                <p class="text-gray-600 mb-4">von {{ $product->brand }}</p>
            @endif
            
            {{-- Price Info Box --}}
            <div class="bg-yellow-50 border-2 border-yellow-400 rounded-lg p-6 mb-6">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-600">Zielpreis</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($product->target_price, 0, ',', '.') }} €</p>
                    </div>
                    @if($product->retail_price)
                        <div>
                            <p class="text-sm text-gray-600">UVP</p>
                            <p class="text-xl text-gray-500 line-through">{{ number_format($product->retail_price, 0, ',', '.') }} €</p>
                        </div>
                    @endif
                </div>
                
                {{-- Raffle Progress --}}
                @if($product->raffle)
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-2">
                            <span>Fortschritt</span>
                            <span class="font-bold">
                                {{ number_format($product->raffle->tickets_sold, 0, ',', '.') }} / 
                                {{ number_format($product->raffle->total_target, 0, ',', '.') }} €
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            @php
                                $progress = ($product->raffle->tickets_sold / max($product->raffle->total_target, 1)) * 100;
                            @endphp
                            <div class="bg-gradient-to-r from-yellow-400 to-yellow-600 h-3 rounded-full transition-all duration-500" 
                                 style="width: {{ min($progress, 100) }}%"></div>
                        </div>
                    </div>
                    
                    {{-- Stats --}}
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div>
                            <p class="text-2xl font-bold">{{ $product->raffle->tickets_sold }}</p>
                            <p class="text-xs text-gray-600">Lose verkauft</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ $product->raffle->unique_participants }}</p>
                            <p class="text-xs text-gray-600">Teilnehmer</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ max(0, $product->raffle->ends_at->diffInDays(now())) }}</p>
                            <p class="text-xs text-gray-600">Tage noch</p>
                        </div>
                    </div>
                @endif
            </div>
            
            {{-- Buy Tickets Section --}}
            <div class="bg-white border rounded-lg p-6 mb-6">
                <h3 class="font-bold text-lg mb-4">🎟️ Lose kaufen</h3>
                
                {{-- Spending Limit Info --}}
                <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Maximales Ausgabenlimit: <strong>10€ pro Stunde</strong> (Spielerschutz)
                    </p>
                    @if($remainingBudget < 10)
                        <p class="text-sm text-blue-600 mt-1">
                            Verbleibendes Budget diese Stunde: <strong>{{ number_format($remainingBudget, 2, ',', '.') }} €</strong>
                        </p>
                    @endif
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Anzahl Lose (je 1€)</label>
                    <div class="flex items-center gap-2">
                        <button onclick="decreaseQuantity()" 
                                class="w-10 h-10 bg-gray-200 hover:bg-gray-300 rounded-lg font-bold transition-colors">-</button>
                        <input type="number" id="ticketQuantity" value="1" min="1" max="{{ min(10, $remainingBudget) }}" 
                               class="w-20 text-center border rounded-lg px-2 py-1"
                               onchange="validateQuantity()">
                        <button onclick="increaseQuantity()" 
                                class="w-10 h-10 bg-gray-200 hover:bg-gray-300 rounded-lg font-bold transition-colors">+</button>
                        
                        {{-- Quick select buttons --}}
                        <button onclick="setQuantity(5)" class="ml-4 px-3 py-1 bg-gray-100 rounded hover:bg-gray-200 transition-colors {{ $remainingBudget < 5 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $remainingBudget < 5 ? 'disabled' : '' }}>5</button>
                        <button onclick="setQuantity(10)" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200 transition-colors {{ $remainingBudget < 10 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $remainingBudget < 10 ? 'disabled' : '' }}>10</button>
                        <button onclick="setQuantity(20)" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200 transition-colors opacity-50 cursor-not-allowed" disabled title="Maximum 10€/Stunde">20</button>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Max. {{ min(10, $remainingBudget) }} Lose möglich (Ausgabenlimit)</p>
                </div>
                
                <div class="flex items-center justify-between mb-4">
                    <span class="text-lg">Gesamtpreis:</span>
                    <span class="text-2xl font-bold text-green-600" id="totalPrice">1,00 €</span>
                </div>
                
                <button id="buyButton" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 rounded-lg text-lg transition-colors">
                    <i class="fas fa-ticket-alt mr-2"></i>
                    Jetzt Lose kaufen
                </button>
                
                <p class="text-xs text-gray-500 mt-2 text-center">
                    <i class="fas fa-lock mr-1"></i>
                    Sichere Bezahlung über Stripe
                </p>
            </div>
            
            {{-- Time Info --}}
            @if($product->raffle)
                <div class="bg-red-50 border border-red-300 rounded-lg p-4 mb-6">
                    <p class="font-bold text-red-700">
                        <i class="fas fa-clock mr-2"></i>
                        Verlosung endet {{ $product->raffle->ends_at->format('d.m.Y \u\m H:i \U\h\r') }}
                    </p>
                    <p class="text-sm text-red-600 mt-1">
                        Noch {{ $product->raffle->ends_at->diffForHumans() }}
                    </p>
                </div>
            @endif
        </div>
    </div>
    
    {{-- Product Description --}}
    <div class="mt-12 bg-white rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Produktbeschreibung</h2>
        <p class="text-gray-700 whitespace-pre-line">{{ $product->description }}</p>
        
        @if($product->condition)
            <div class="mt-4 inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded">
                Zustand: {{ ucfirst(str_replace('_', ' ', $product->condition)) }}
            </div>
        @endif
    </div>
    
    {{-- Related Products --}}
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold mb-6">Ähnliche Verlosungen</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $related)
                    @php
                        $relatedImages = $related->images()->get();
                    @endphp
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow">
                        <div class="h-48 bg-gray-100">
                            @if($relatedImages->count() > 0)
                                <img src="{{ $relatedImages->first()->image_path }}" 
                                     alt="{{ $related->title }}"
                                     class="w-full h-full object-cover rounded-t-lg"
                                     onerror="this.src='https://dummyimage.com/400x300/cccccc/666666?text=No+Image'">
                            @else
                                <img src="https://dummyimage.com/400x300/{{ substr(md5($related->title), 0, 6) }}/FFFFFF?text={{ urlencode($related->title) }}" 
                                     alt="{{ $related->title }}"
                                     class="w-full h-full object-cover rounded-t-lg">
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold mb-2 line-clamp-1">{{ $related->title }}</h3>
                            <p class="text-green-600 font-bold mb-2">{{ number_format($related->target_price, 0, ',', '.') }} €</p>
                            <a href="{{ route('raffles.show', $related->id) }}" 
                               class="block w-full bg-yellow-500 text-white text-center py-2 rounded hover:bg-yellow-600">
                                Details →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Maximales Ausgabenlimit pro Stunde
    const MAX_SPENDING_PER_HOUR = 10;
    const remainingBudget = {{ $remainingBudget }};
    const maxTickets = Math.min(MAX_SPENDING_PER_HOUR, remainingBudget);
    
    // Bildergalerie-Funktionen
    @if($productImages->count() > 0)
        const productImages = @json($productImages->pluck('image_path'));
        let currentImageIndex = 0;
        
        function selectImage(index) {
            currentImageIndex = index;
            document.getElementById('mainImage').src = productImages[index];
            
            // Update thumbnail borders
            document.querySelectorAll('.grid.grid-cols-4 img').forEach((img, i) => {
                if(i === index) {
                    img.classList.add('border-yellow-500');
                    img.classList.remove('border-transparent');
                } else {
                    img.classList.remove('border-yellow-500');
                    img.classList.add('border-transparent');
                }
            });
        }
        
        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % productImages.length;
            selectImage(currentImageIndex);
        }
        
        function previousImage() {
            currentImageIndex = (currentImageIndex - 1 + productImages.length) % productImages.length;
            selectImage(currentImageIndex);
        }
    @endif
    
    // Ticket-Mengen-Funktionen
    function updatePrice() {
        const quantity = parseInt(document.getElementById('ticketQuantity').value) || 1;
        const formattedPrice = quantity.toFixed(2).replace('.', ',');
        document.getElementById('totalPrice').textContent = formattedPrice + ' €';
        
        // Button aktivieren/deaktivieren basierend auf Budget
        const buyButton = document.getElementById('buyButton');
        if (quantity > maxTickets) {
            buyButton.disabled = true;
            buyButton.classList.add('opacity-50', 'cursor-not-allowed');
            buyButton.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Ausgabenlimit überschritten';
        } else {
            buyButton.disabled = false;
            buyButton.classList.remove('opacity-50', 'cursor-not-allowed');
            buyButton.innerHTML = '<i class="fas fa-ticket-alt mr-2"></i>Jetzt Lose kaufen';
        }
    }
    
    function increaseQuantity() {
        const input = document.getElementById('ticketQuantity');
        const currentValue = parseInt(input.value) || 1;
        const newValue = Math.min(currentValue + 1, maxTickets);
        input.value = newValue;
        updatePrice();
    }
    
    function decreaseQuantity() {
        const input = document.getElementById('ticketQuantity');
        const currentValue = parseInt(input.value) || 1;
        const newValue = Math.max(currentValue - 1, 1);
        input.value = newValue;
        updatePrice();
    }
    
    function setQuantity(qty) {
        const input = document.getElementById('ticketQuantity');
        const validQuantity = Math.min(qty, maxTickets);
        input.value = validQuantity;
        updatePrice();
    }
    
    function validateQuantity() {
        const input = document.getElementById('ticketQuantity');
        const value = parseInt(input.value) || 1;
        
        // Begrenze auf Maximum
        if (value > maxTickets) {
            input.value = maxTickets;
            alert('Maximales Ausgabenlimit: ' + maxTickets + '€ pro Stunde');
        }
        
        // Minimum ist 1
        if (value < 1) {
            input.value = 1;
        }
        
        updatePrice();
    }
    
    // Event Listener für manuelle Eingabe
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('ticketQuantity');
        if (input) {
            input.addEventListener('input', validateQuantity);
            input.addEventListener('change', validateQuantity);
            
            // Initiale Preisanzeige
            updatePrice();
        }
    });
</script>
@endpush