{{-- resources/views/seller/products/create/step4-pricing.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Preisgestaltung</h1>
        <p class="mt-2 text-gray-600">Legen Sie Ihren Zielpreis fest und treffen Sie Ihre Entscheidung</p>
    </div>
    
    {{-- Fortschrittsanzeige --}}
    @include('seller.products.create._progress', ['step' => 4])
    
    {{-- KI-Preisempfehlung --}}
    @if(isset($priceSuggestion) && $priceSuggestion['confidence'] > 0)
        <div class="bg-gradient-to-r from-purple-50 to-blue-50 border-2 border-purple-200 rounded-lg p-6 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center text-2xl">
                        🤖
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">
                        KI-Preisempfehlung
                        <span class="ml-2 px-2 py-1 text-xs font-medium bg-purple-600 text-white rounded">
                            {{ $priceSuggestion['confidence'] }}% Konfidenz
                        </span>
                    </h3>
                    
                    {{-- Empfohlener Preis --}}
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Empfohlener Zielpreis:</p>
                        <div class="flex items-baseline">
                            <span class="text-4xl font-bold text-purple-600">
                                {{ number_format($priceSuggestion['suggested_price'], 2, ',', '.') }} €
                            </span>
                            <button type="button" 
                                    onclick="applyPrice({{ $priceSuggestion['suggested_price'] }})"
                                    class="ml-4 px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition">
                                Übernehmen
                            </button>
                        </div>
                        
                        <div class="mt-2 flex items-center space-x-4 text-sm text-gray-600">
                            <span>Min: {{ number_format($priceSuggestion['min_price'], 2, ',', '.') }} €</span>
                            <span>•</span>
                            <span>Max: {{ number_format($priceSuggestion['max_price'], 2, ',', '.') }} €</span>
                        </div>
                    </div>
                    
                    {{-- Begründung --}}
                    <div class="p-3 bg-white rounded-lg">
                        <p class="text-sm text-gray-700 mb-2">
                            <strong>Begründung:</strong> {{ $priceSuggestion['reasoning'] }}
                        </p>
                        
                        @if($priceSuggestion['based_on'] > 0)
                            <p class="text-xs text-gray-600 mt-2">
                                Basierend auf {{ $priceSuggestion['based_on'] }} ähnlichen Produkten
                            </p>
                            
                            {{-- Ähnliche Produkte --}}
                            @if(!empty($priceSuggestion['similar_products']))
                                <div class="mt-3">
                                    <p class="text-xs font-medium text-gray-700 mb-2">Vergleichsprodukte:</p>
                                    <div class="space-y-1">
                                        @foreach($priceSuggestion['similar_products'] as $similar)
                                            <div class="flex justify-between text-xs text-gray-600">
                                                <span class="truncate mr-2">{{ $similar['title'] }}</span>
                                                <span class="font-medium whitespace-nowrap">{{ number_format($similar['price'], 2, ',', '.') }} €</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    {{-- Formular --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="{{ route('seller.products.create.step4') }}" id="step4Form">
            @csrf
            
            {{-- Zielpreis --}}
            <div class="mb-6">
                <label for="target_price" class="block text-sm font-medium text-gray-700 mb-2">
                    Ihr Zielpreis <span class="text-red-500">*</span>
                </label>
                
                <div class="relative">
                    <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 text-lg">€</span>
                    <input type="number" 
                           name="target_price" 
                           id="target_price" 
                           step="0.01"
                           min="10"
                           max="50000"
                           value="{{ old('target_price', $product->target_price ?? '') }}"
                           class="w-full pl-12 pr-4 py-4 text-2xl font-bold border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                  @error('target_price') border-red-300 @enderror"
                           placeholder="0.00"
                           required>
                </div>
                
                <p class="mt-2 text-sm text-gray-500">
                    Der Betrag, den Sie erhalten möchten (Min. 10 €, Max. 50.000 €)
                </p>
                
                @error('target_price')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- Kalkulations-Vorschau --}}
            <div id="priceCalculation" class="mb-6 p-4 bg-gray-50 rounded-lg hidden">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Kalkulationsübersicht</h3>
                
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Ihr Zielpreis:</span>
                        <span class="font-medium" id="calc-target">0,00 €</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Plattformprovision (30%):</span>
                        <span class="font-medium text-blue-600" id="calc-fee">0,00 €</span>
                    </div>
                    
                    <div class="border-t pt-2 flex justify-between">
                        <span class="font-bold text-gray-900">Endpreis (Losverkauf):</span>
                        <span class="font-bold text-lg" id="calc-total">0,00 €</span>
                    </div>
                    
                    <p class="text-xs text-gray-500 mt-2">
                        = Anzahl der benötigten 1€-Lose
                    </p>
                </div>
            </div>
            
            {{-- Entscheidung --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Ihre Entscheidung <span class="text-red-500">*</span>
                </label>
                
                <p class="text-sm text-gray-600 mb-4">
                    Was passiert, wenn der Zielpreis <strong>nicht</strong> erreicht wird?
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Option: Produkt abgeben --}}
                    <label class="relative flex flex-col p-6 border-2 rounded-lg cursor-pointer transition-all hover:border-blue-300
                        @error('decision_type') border-red-300 @else border-gray-200 @enderror">
                        
                        <input type="radio" 
                               name="decision_type" 
                               value="give" 
                               class="peer sr-only"
                               {{ old('decision_type', $product->decision_type ?? '') == 'give' ? 'checked' : '' }}>
                        
                        <div class="text-4xl mb-3">🎁</div>
                        
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Produkt abgeben</h4>
                        
                        <p class="text-sm text-gray-600 mb-3">
                            Der Gewinner erhält das Produkt, auch wenn der Zielpreis nicht erreicht wurde.
                        </p>
                        
                        <div class="p-3 bg-green-50 border border-green-200 rounded text-xs text-green-700">
                            <strong>Vorteil:</strong> Höhere Losverkäufe durch garantierte Gewinnchance
                        </div>
                        
                        <div class="absolute inset-0 border-2 border-green-600 rounded-lg opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                    </label>
                    
                    {{-- Option: Produkt behalten --}}
                    <label class="relative flex flex-col p-6 border-2 rounded-lg cursor-pointer transition-all hover:border-blue-300
                        @error('decision_type') border-red-300 @else border-gray-200 @enderror">
                        
                        <input type="radio" 
                               name="decision_type" 
                               value="keep" 
                               class="peer sr-only"
                               {{ old('decision_type', $product->decision_type ?? '') == 'keep' ? 'checked' : '' }}>
                        
                        <div class="text-4xl mb-3">💰</div>
                        
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Produkt behalten</h4>
                        
                        <p class="text-sm text-gray-600 mb-3">
                            Der Gewinner erhält den Nettoerlös aus dem Losverkauf als Geldpreis.
                        </p>
                        
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded text-xs text-blue-700">
                            <strong>Vorteil:</strong> Sie behalten Ihr Produkt und erhalten den Nettoerlös
                        </div>
                        
                        <div class="absolute inset-0 border-2 border-blue-600 rounded-lg opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                    </label>
                </div>
                
                @error('decision_type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- Info Box --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <svg class="h-5 w-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Wichtige Information</h3>
                        <p class="mt-1 text-sm text-yellow-700">
                            Die Plattformprovision beträgt 30% vom Endpreis. Ihr Zielpreis ist der Betrag, den Sie garantiert erhalten, wenn dieser erreicht wird.
                        </p>
                    </div>
                </div>
            </div>
            
            {{-- Navigation --}}
            <div class="flex justify-between items-center pt-6 border-t">
                <a href="{{ route('seller.products.create.step', 3) }}" 
                   class="px-6 py-2 text-gray-700 hover:text-gray-900 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Zurück
                </a>
                
                <button type="submit" 
                        class="px-8 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition flex items-center">
                    Weiter
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const targetPriceInput = document.getElementById('target_price');
const priceCalculation = document.getElementById('priceCalculation');

// Preisberechnung in Echtzeit
targetPriceInput.addEventListener('input', function() {
    const targetPrice = parseFloat(this.value) || 0;
    
    if (targetPrice > 0) {
        // Berechnung: Endpreis = Zielpreis / 0.7 (damit 30% Provision vom Endpreis)
        const totalTarget = targetPrice / 0.7;
        const platformFee = totalTarget * 0.3;
        
        document.getElementById('calc-target').textContent = formatPrice(targetPrice);
        document.getElementById('calc-fee').textContent = formatPrice(platformFee);
        document.getElementById('calc-total').textContent = formatPrice(totalTarget);
        
        priceCalculation.classList.remove('hidden');
    } else {
        priceCalculation.classList.add('hidden');
    }
});

function formatPrice(amount) {
    return amount.toFixed(2).replace('.', ',') + ' €';
}

function applyPrice(price) {
    targetPriceInput.value = price.toFixed(2);
    targetPriceInput.dispatchEvent(new Event('input'));
    
    // Scroll to input
    targetPriceInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    targetPriceInput.focus();
}

// Initial calculation if value exists
if (targetPriceInput.value) {
    targetPriceInput.dispatchEvent(new Event('input'));
}
</script>
@endsection