{{-- Ähnliche Produkte --}}
    @if($relatedProducts->count() > 0)
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Ähnliche Verlosungen</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedProducts as $related)
            <a href="{{ route('raffles.show', $related->id) }}" class="bg-white rounded-lg shadow hover:shadow-lg transition">
                @if($related->images && $related->images->count() > 0)
                    <img src="{{ $related->images->first()->image_path }}" 
                         alt="{{ $related->title }}"
                         class="w-full h-48 object-cover rounded-t-lg">
                @else
                    <div class="w-full h-48 bg-gray-200 rounded-t-lg"></div>
                @endif
                <div class="p-4">
                    <h3 class="font-bold mb-2 line-clamp-2">{{ $related->title }}</h3>
                    <p class="text-sm text-gray-600">{{ number_format($related->target_price, 0, ',', '.') }} €</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-6">
            
            {{-- Bildergalerie --}}
            <div>
                <div class="mb-4">
                    @if($product->images && $product->images->count() > 0)
                        <img id="mainImage" 
                             src="{{ $product->images->first()->image_path }}" 
                             alt="{{ $product->title }}"
                             class="w-full h-96 object-cover rounded-lg">
                    @else
                        <div class="w-full h-96 bg-gray-200 rounded-lg flex items-center justify-center">
                            <span class="text-gray-400">Kein Bild verfügbar</span>
                        </div>
                    @endif
                </div>
                
                @if($product->images && $product->images->count() > 1)
                <div class="grid grid-cols-4 gap-2">
                    @foreach($product->images as $image)
                        <img src="{{ $image->image_path }}" 
                             alt="{{ $image->alt_text }}"
                             class="w-full h-20 object-cover rounded cursor-pointer hover:opacity-75 transition"
                             onclick="document.getElementById('mainImage').src='{{ $image->image_path }}'">
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Produktinformationen --}}
            <div>
                <h1 class="text-3xl font-bold mb-4">{{ $product->title }}</h1>
                
                {{-- Kategorie & Marke --}}
                <div class="flex gap-4 mb-4 text-sm text-gray-600">
                    @if($product->category)
                        <span class="bg-gray-100 px-3 py-1 rounded">{{ $product->category->name }}</span>
                    @endif
                    @if($product->brand)
                        <span class="bg-gray-100 px-3 py-1 rounded">{{ $product->brand }}</span>
                    @endif
                    @if($product->condition)
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded">{{ ucfirst($product->condition) }}</span>
                    @endif
                </div>

                {{-- Fortschrittsbalken --}}
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 p-4 rounded-lg mb-6">
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-semibold">Verkaufte Lose</span>
                        <span class="text-sm font-semibold">{{ number_format($ticketsSold, 0, ',', '.') }} / {{ number_format($totalTarget, 0, ',', '.') }}€</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4 mb-3 overflow-hidden">
                        <div class="bg-gradient-to-r from-yellow-400 to-yellow-600 h-4 rounded-full transition-all duration-500 flex items-center justify-end pr-2" 
                             style="width: {{ min($progressPercentage, 100) }}%">
                            <span class="text-xs text-white font-bold">{{ number_format($progressPercentage, 1, ',', '.') }}%</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div>
                            <p class="text-2xl font-bold text-yellow-600">{{ number_format($ticketsSold, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-600">Lose verkauft</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-green-600">1€</p>
                            <p class="text-xs text-gray-600">pro Los</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($product->target_price, 0, ',', '.') }}€</p>
                            <p class="text-xs text-gray-600">Zielpreis</p>
                        </div>
                    </div>
                </div>

                {{-- Countdown --}}
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <p class="font-semibold text-red-700 mb-1">⏰ Verlosung endet:</p>
                    <p class="text-lg">{{ $product->raffle->ends_at->format('d.m.Y H:i') }} Uhr</p>
                    <p class="text-sm text-gray-600">{{ $product->raffle->ends_at->diffForHumans() }}</p>
                </div>

                {{-- Kaufformular --}}
                @auth
                    @if($product->seller_id !== Auth::id() && $product->raffle->status === 'active')
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

                        {{-- Kaufformular --}}
                        <form action="{{ route('tickets.purchase', $product->id) }}" method="POST">
                            @csrf
                            
                            <div class="bg-white border-2 border-gray-300 rounded-lg p-6 mb-4">
                                <label class="block text-sm font-semibold mb-3">Anzahl Lose (à 1€)</label>
                                
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
                                           max="{{ min(10, $remainingBudget) }}"
                                           class="flex-1 text-center border-2 border-gray-300 rounded-lg py-2 text-lg font-bold">
                                    <button type="button" onclick="changeQuantity(1)" 
                                            class="bg-gray-200 hover:bg-gray-300 w-10 h-10 rounded-lg font-bold">
                                        +
                                    </button>
                                </div>

                                <div class="grid grid-cols-3 gap-2 mb-4">
                                    <button type="button" onclick="setQuantity(5)" 
                                            class="bg-yellow-100 hover:bg-yellow-200 py-2 rounded font-semibold"
                                            {{ $remainingBudget < 5 ? 'disabled' : '' }}>
                                        5 Lose
                                    </button>
                                    <button type="button" onclick="setQuantity(10)" 
                                            class="bg-yellow-100 hover:bg-yellow-200 py-2 rounded font-semibold"
                                            {{ $remainingBudget < 10 ? 'disabled' : '' }}>
                                        10 Lose
                                    </button>
                                    <button type="button" onclick="setQuantity(20)" 
                                            class="bg-gray-300 py-2 rounded font-semibold cursor-not-allowed" 
                                            disabled>
                                        20 Lose
                                    </button>
                                </div>

                                <div class="bg-gray-50 p-3 rounded text-center">
                                    <span class="text-sm text-gray-600">Gesamtpreis: </span>
                                    <span id="totalPrice" class="text-2xl font-bold text-gray-800">1,00 €</span>
                                </div>
                            </div>

                            @if($remainingBudget < 1)
                                <div class="bg-red-50 border border-red-400 p-4 rounded-lg mb-4 text-center">
                                    <p class="text-red-700 font-semibold">⚠️ Stundenlimit erreicht</p>
                                    <p class="text-sm text-red-600">Sie können in dieser Stunde keine weiteren Lose kaufen.</p>
                                </div>
                            @endif

                            <button type="submit" 
                                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-4 rounded-lg font-bold text-lg transition disabled:bg-gray-400 disabled:cursor-not-allowed"
                                    {{ $remainingBudget < 1 ? 'disabled' : '' }}>
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
    </div>

    {{-- Ähnliche Produkte --}}
    @if($relatedProducts->count() > 0)
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Ähnliche Verlosungen</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedProducts as $related)
            <a href="{{ route('raffles.show', $related->id) }}" class="bg-white rounded-lg shadow hover:shadow-lg transition">
                @if($related->images->count() > 0)
                    <img src="{{ $related->images->first()->image_path }}" 
                         alt="{{ $related->title }}"
                         class="w-full h-48 object-cover rounded-t-lg">
                @else
                    <div class="w-full h-48 bg-gray-200 rounded-t-lg"></div>
                @endif
                <div class="p-4">
                    <h3 class="font-bold mb-2 line-clamp-2">{{ $related->title }}</h3>
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
    const maxQuantity = {{ min(10, $remainingBudget) }};
    
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