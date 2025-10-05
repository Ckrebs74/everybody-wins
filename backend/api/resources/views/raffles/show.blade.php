@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    
    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        {{-- Linke Spalte: Bilder --}}
        <div>
            {{-- Hauptbild --}}
            <div class="mb-4 bg-gray-100 rounded-lg overflow-hidden">
                @php
                    $images = $product->images()->get();
                    $mainImage = $images->where('is_primary', true)->first() ?? $images->first();
                @endphp
                
                @if($mainImage)
                    <img id="mainImage" 
                         src="{{ $mainImage->image_path }}" 
                         alt="{{ $mainImage->alt_text ?? $product->title }}"
                         class="w-full h-96 object-cover">
                @else
                    <div class="w-full h-96 flex items-center justify-center bg-gradient-to-br from-yellow-400 to-yellow-600">
                        <span class="text-white text-6xl font-bold">{{ substr($product->title, 0, 1) }}</span>
                    </div>
                @endif
            </div>

            {{-- Thumbnail-Galerie --}}
            @if($images->count() > 1)
                <div class="grid grid-cols-4 gap-2">
                    @foreach($images as $image)
                        <div class="cursor-pointer border-2 border-transparent hover:border-yellow-500 rounded overflow-hidden transition"
                             onclick="document.getElementById('mainImage').src='{{ $image->image_path }}'">
                            <img src="{{ $image->thumbnail_path ?? $image->image_path }}" 
                                 alt="{{ $image->alt_text }}"
                                 class="w-full h-20 object-cover">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Rechte Spalte: Produktinfo & Kauf --}}
        <div>
            <div class="mb-4">
                <span class="text-sm text-gray-500">{{ $product->category->name ?? 'Allgemein' }}</span>
                <h1 class="text-3xl font-bold mb-2">{{ $product->title }}</h1>
            </div>

            {{-- Beschreibung --}}
            <div class="mb-6">
                <p class="text-gray-700">{{ $product->description }}</p>
            </div>

            {{-- Preisinfo --}}
            <div class="bg-yellow-50 border-2 border-yellow-500 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-lg font-semibold">Zielpreis:</span>
                    <span class="text-2xl font-bold text-yellow-600">{{ number_format($product->target_price, 2, ',', '.') }} €</span>
                </div>
                <div class="text-sm text-gray-600">
                    Lose à 1€ | Endpreis: {{ number_format($product->raffle->total_target, 2, ',', '.') }} €
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="mb-6">
                <div class="flex justify-between text-sm mb-2">
                    <span class="font-semibold">Verkaufte Lose</span>
                    <span class="text-gray-600">{{ $product->raffle->tickets_sold }} / {{ $product->raffle->total_tickets }}</span>
                </div>
                @php
                    $progress = ($product->raffle->tickets_sold / max($product->raffle->total_tickets, 1)) * 100;
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
                @if($product->seller_id === Auth::id())
                    {{-- Eigenes Produkt --}}
                    <div class="bg-blue-50 border border-blue-400 p-4 rounded-lg">
                        <p class="text-blue-700">Dies ist Ihre eigene Verlosung. Sie können nicht teilnehmen.</p>
                    </div>
                @elseif($product->raffle->status !== 'active' || $product->raffle->ends_at <= now())
                    {{-- Verlosung beendet --}}
                    <div class="bg-gray-100 border border-gray-400 p-4 rounded-lg">
                        <p class="text-gray-700">Diese Verlosung ist beendet.</p>
                    </div>
                @else
                    {{-- Wallet & Spending Info --}}
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
                    <form action="{{ route('tickets.purchase', $product->raffle->id) }}" method="POST">
                        @csrf
                        
                        <div class="bg-white border-2 border-gray-300 rounded-lg p-6 mb-4">
                            <label class="block text-sm font-semibold mb-3">Anzahl Lose (à 1€)</label>
                            
                            {{-- Mengenauswahl --}}
                            <div class="flex items-center gap-3 mb-4">
                                <button type="button" onclick="changeQuantity(-1)" 
                                        class="bg-gray-200 hover:bg-gray-300 w-10 h-10 rounded-lg font-bold text-xl">
                                    −
                                </button>
                                
                                <input type="number" 
                                       name="quantity" 
                                       id="quantity"
                                       value="1" 
                                       min="1" 
                                       max="10"
                                       class="w-20 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg py-2"
                                       onchange="updateTotal()">
                                
                                <button type="button" onclick="changeQuantity(1)" 
                                        class="bg-gray-200 hover:bg-gray-300 w-10 h-10 rounded-lg font-bold text-xl">
                                    +
                                </button>
                            </div>

                            {{-- Quick Select Buttons --}}
                            <div class="grid grid-cols-3 gap-2 mb-4">
                                <button type="button" onclick="setQuantity(5)" 
                                        class="bg-gray-100 hover:bg-yellow-100 border-2 border-gray-300 hover:border-yellow-500 py-2 rounded-lg font-semibold">
                                    5 Lose
                                </button>
                                <button type="button" onclick="setQuantity(10)" 
                                        class="bg-gray-100 hover:bg-yellow-100 border-2 border-gray-300 hover:border-yellow-500 py-2 rounded-lg font-semibold">
                                    10 Lose
                                </button>
                                <button type="button" onclick="setQuantity(Math.min({{ (int)$stats['remaining_hour'] }}, 10))" 
                                        class="bg-blue-100 hover:bg-blue-200 border-2 border-blue-300 py-2 rounded-lg font-semibold text-sm">
                                    Max ({{ min($stats['remaining_hour'], 10) }}€)
                                </button>
                            </div>

                            {{-- Gesamtpreis --}}
                            <div class="bg-gray-50 rounded-lg p-4 flex justify-between items-center">
                                <span class="text-lg font-semibold">Gesamtpreis:</span>
                                <span id="totalPrice" class="text-3xl font-bold text-yellow-600">1,00 €</span>
                            </div>
                        </div>

                        {{-- Kaufbutton --}}
                        <button type="submit" 
                                class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-4 rounded-lg font-bold text-lg transition shadow-lg hover:shadow-xl">
                            🎯 JETZT MITMACHEN - für <span id="buttonPrice">1</span>€
                        </button>

                        <p class="text-xs text-gray-500 text-center mt-2">
                            Max. 10€ pro Stunde gemäß Glücksspielregulierung
                        </p>
                    </form>
                @endif
            @else
                {{-- Nicht eingeloggt --}}
                <div class="bg-yellow-50 border-2 border-yellow-500 rounded-lg p-6 text-center">
                    <p class="text-lg mb-4">Melden Sie sich an, um teilzunehmen!</p>
                    <a href="{{ route('login') }}" 
                       class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white px-8 py-3 rounded-lg font-bold">
                        Jetzt anmelden
                    </a>
                </div>
            @endauth

            {{-- Garantie-Info --}}
            <div class="mt-6 p-4 bg-green-50 border-2 border-green-500 rounded-lg">
                <h3 class="font-bold text-green-700 mb-2">✅ Jeder gewinnt!</h3>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li>• Zielpreis erreicht → Gewinner erhält Produkt</li>
                    <li>• Zielpreis nicht erreicht → Gewinner erhält Erlös oder Produkt</li>
                    <li>• Faire Chance für nur 1€ pro Los</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function changeQuantity(delta) {
    const input = document.getElementById('quantity');
    let value = parseInt(input.value) + delta;
    value = Math.max(1, Math.min(10, value));
    input.value = value;
    updateTotal();
}

function setQuantity(amount) {
    const input = document.getElementById('quantity');
    input.value = Math.max(1, Math.min(10, amount));
    updateTotal();
}

function updateTotal() {
    const quantity = parseInt(document.getElementById('quantity').value);
    const total = quantity * 1.00;
    document.getElementById('totalPrice').textContent = total.toFixed(2).replace('.', ',') + ' €';
    document.getElementById('buttonPrice').textContent = total.toFixed(0);
}

// Initial update
updateTotal();
</script>
@endpush
@endsection