@extends('layouts.app')

@section('content')
@php
    // Lade die Bilder
    $productImages = $product->images()->get();
@endphp

<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        {{-- Image Gallery Section --}}
        <div>
            {{-- Main Image --}}
            <div class="mb-4 relative">
                @php
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
                     class="w-full h-96 object-contain bg-gray-100 rounded-lg">
            </div>

            {{-- Thumbnail Gallery --}}
            @if($productImages->count() > 0)
                <div class="grid grid-cols-5 gap-2">
                    @foreach($productImages as $index => $image)
                        <img src="{{ $image->image_path }}" 
                             alt="{{ $product->title }}"
                             onclick="document.getElementById('mainImage').src='{{ $image->image_path }}'"
                             class="w-full h-20 object-cover rounded cursor-pointer border-2 hover:border-yellow-500 transition-colors">
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Product Details Section --}}
        <div>
            <h1 class="text-3xl font-bold mb-4">{{ $product->title }}</h1>

            {{-- Category & Brand --}}
            <div class="flex gap-2 mb-4">
                @if($product->category)
                    <span class="bg-gray-200 px-3 py-1 rounded-full text-sm">{{ $product->category->name }}</span>
                @endif
                @if($product->brand)
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">{{ $product->brand }}</span>
                @endif
            </div>

            {{-- Price Section --}}
            <div class="bg-yellow-50 border-2 border-yellow-400 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-700">Zielpreis:</span>
                    <span class="text-2xl font-bold text-yellow-600">{{ number_format($product->target_price, 0, ',', '.') }} €</span>
                </div>
                <div class="text-sm text-gray-600">
                    Lose à 1€ | Endpreis: {{ number_format($product->raffle->total_target, 2, ',', '.') }} €
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="mb-6">
                <div class="flex justify-between text-sm mb-2">
                    <span class="font-semibold">Verkaufte Lose</span>
                    <span class="text-gray-600">{{ $product->raffle->tickets_sold }} / {{ (int)($product->raffle->total_target) }}</span>
                </div>
                @php
                    $totalTarget = $product->raffle->total_target;
                    $ticketsSold = $product->raffle->tickets_sold;
                    $progress = $totalTarget > 0 ? ($ticketsSold / $totalTarget) * 100 : 0;
                @endphp
                <div class="w-full bg-gray-200 rounded-full h-4 mb-2">
                    <div class="bg-yellow-500 h-4 rounded-full transition-all duration-300 flex items-center justify-end pr-2" 
                         style="width: {{ min($progress, 100) }}%">
                        <span class="text-xs text-white font-bold">{{ number_format($progress, 1) }}%</span>
                    </div>
                </div>
            </div>

            {{-- Zeitinfo --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-semibold">Endet am:</span>
                </div>
                <p class="text-lg">{{ $product->raffle->ends_at->format('d.m.Y H:i') }} Uhr</p>
                <p class="text-sm text-gray-600">{{ $product->raffle->ends_at->diffForHumans() }}</p>
            </div>

            {{-- KAUFFORMULAR --}}
            @auth
                @if($product->seller_id !== Auth::id() && $product->raffle->status === 'active')
                    {{-- Guthaben & Spending Limit Info --}}
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-semibold">💰 Ihr Guthaben:</span>
                            <span class="text-lg font-bold text-blue-600">{{ number_format(Auth::user()->wallet_balance, 2, ',', '.') }} €</span>
                        </div>
                        @php
                            $spendingService = app(\App\Services\SpendingLimitService::class);
                            $stats = $spendingService->getStatistics(Auth::id());
                        @endphp
                        <div class="flex justify-between items-center text-sm">
                            <span>⏱️ Diese Stunde ausgegeben:</span>
                            <span class="font-semibold {{ $stats['remaining_hour'] < 3 ? 'text-red-600' : '' }}">
                                {{ number_format($stats['current_hour'], 2, ',', '.') }} € / 10,00 €
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-blue-500 h-2 rounded-full" 
                                 style="width: {{ $stats['percentage_used'] }}%"></div>
                        </div>
                    </div>

                    {{-- Kaufformular - Verwendet Raffle ID (nicht Slug) --}}
                    <form action="{{ route('tickets.purchase', $product->raffle->id) }}" method="POST">
                        @csrf
                        
                        <div class="bg-white border-2 border-gray-300 rounded-lg p-6 mb-4">
                            <label class="block text-sm font-semibold mb-3">Anzahl Lose (à 1€)</label>
                            
                            {{-- Mengenauswahl --}}
                            <div class="flex items-center gap-2 mb-4">
                                <button type="button" onclick="changeQuantity(-1)" 
                                        class="bg-gray-200 hover:bg-gray-300 w-10 h-10 rounded-lg font-bold">
                                    −
                                </button>
                                <input type="number" 
                                       name="quantity" 
                                       id="quantity" 
                                       value="1" 
                                       min="1" 
                                       max="10"
                                       class="flex-1 text-center border-2 border-gray-300 rounded-lg py-2 text-lg font-bold">
                                <button type="button" onclick="changeQuantity(1)" 
                                        class="bg-gray-200 hover:bg-gray-300 w-10 h-10 rounded-lg font-bold">
                                    +
                                </button>
                            </div>

                            {{-- Quick Select Buttons --}}
                            <div class="grid grid-cols-3 gap-2 mb-4">
                                <button type="button" onclick="setQuantity(5)" 
                                        class="bg-yellow-100 hover:bg-yellow-200 py-2 rounded font-semibold">
                                    5 Lose
                                </button>
                                <button type="button" onclick="setQuantity(10)" 
                                        class="bg-yellow-100 hover:bg-yellow-200 py-2 rounded font-semibold">
                                    10 Lose
                                </button>
                                <button type="button" 
                                        class="bg-gray-300 py-2 rounded font-semibold cursor-not-allowed" 
                                        disabled>
                                    20 Lose
                                </button>
                            </div>

                            {{-- Gesamtpreis --}}
                            <div class="bg-gray-50 p-3 rounded text-center">
                                <span class="text-sm text-gray-600">Gesamtpreis: </span>
                                <span id="totalPrice" class="text-2xl font-bold text-gray-800">1,00 €</span>
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-4 rounded-lg font-bold text-lg transition">
                            🎫 Jetzt Lose kaufen
                        </button>
                    </form>
                @else
                    <div class="bg-gray-100 p-4 rounded-lg text-center">
                        <p class="text-gray-700">{{ $product->seller_id === Auth::id() ? 'Dies ist Ihre eigene Verlosung' : 'Diese Verlosung ist beendet' }}</p>
                    </div>
                @endif
            @else
                <a href="{{ route('login') }}" 
                   class="block w-full bg-yellow-500 hover:bg-yellow-600 text-white py-4 rounded-lg font-bold text-lg text-center transition">
                    Anmelden zum Mitspielen
                </a>
            @endauth

            {{-- Produktbeschreibung --}}
            <div class="mt-6">
                <h3 class="text-xl font-bold mb-3">Beschreibung</h3>
                <p class="text-gray-700 whitespace-pre-line">{{ $product->description }}</p>
            </div>
        </div>
    </div>

    {{-- Ähnliche Produkte mit Slug --}}
    @if($relatedProducts && $relatedProducts->count() > 0)
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Ähnliche Verlosungen</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedProducts as $related)
                @php
                    $relatedImages = $related->images()->get();
                @endphp
                <a href="{{ route('raffles.show', $related->slug) }}" class="bg-white rounded-lg shadow hover:shadow-lg transition">
                    @if($relatedImages->count() > 0)
                        <img src="{{ $relatedImages->first()->image_path }}" 
                             alt="{{ $related->title }}"
                             class="w-full h-48 object-cover rounded-t-lg">
                    @else
                        <div class="w-full h-48 bg-gray-200 rounded-t-lg flex items-center justify-center">
                            <span class="text-gray-400">Kein Bild</span>
                        </div>
                    @endif
                    <div class="p-4">
                        <h3 class="font-bold mb-2">{{ $related->title }}</h3>
                        <p class="text-sm text-gray-600">{{ number_format($related->target_price, 0, ',', '.') }} €</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    const maxQuantity = 10;
    
    function changeQuantity(delta) {
        const input = document.getElementById('quantity');
        let newValue = parseInt(input.value) + delta;
        newValue = Math.max(1, Math.min(maxQuantity, newValue));
        input.value = newValue;
        updatePrice();
    }
    
    function setQuantity(value) {
        const input = document.getElementById('quantity');
        if (value <= maxQuantity) {
            input.value = value;
            updatePrice();
        }
    }
    
    function updatePrice() {
        const quantity = document.getElementById('quantity').value;
        const total = quantity * 1;
        document.getElementById('totalPrice').textContent = total.toFixed(2).replace('.', ',') + ' €';
    }
    
    document.getElementById('quantity').addEventListener('input', updatePrice);
</script>
@endpush
@endsection