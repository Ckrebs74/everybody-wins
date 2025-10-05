{{-- resources/views/seller/products/create/step5-preview.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Vorschau & Veröffentlichen</h1>
        <p class="mt-2 text-gray-600">Überprüfen Sie alle Details vor der Veröffentlichung</p>
    </div>
    
    {{-- Fortschrittsanzeige --}}
    @include('seller.products.create._progress', ['step' => 5])
    
    {{-- Vollständige Vorschau --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Produktvorschau</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            {{-- Medien --}}
            <div>
                @if($product->images()->count() > 0)
                    @php $primaryImage = $product->images()->where('is_primary', true)->first() ?? $product->images()->first(); @endphp
                    
                    {{-- Haupt-Medium --}}
                    <div class="mb-4">
                        @if($primaryImage->media_type === 'image')
                            <img src="{{ $primaryImage->image_path }}" 
                                 alt="{{ $primaryImage->alt_text }}"
                                 class="w-full h-64 object-cover rounded-lg">
                        @else
                            <div class="relative w-full h-64 bg-gray-100 rounded-lg overflow-hidden">
                                <img src="{{ $primaryImage->thumbnail_path }}" 
                                     alt="{{ $primaryImage->alt_text }}"
                                     class="w-full h-full object-cover">
                                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-40">
                                    <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"></path>
                                    </svg>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Thumbnails --}}
                    <div class="grid grid-cols-4 gap-2">
                        @foreach($product->images()->orderBy('sort_order')->limit(4)->get() as $media)
                            <div class="relative">
                                @if($media->media_type === 'image')
                                    <img src="{{ $media->thumbnail_path ?: $media->image_path }}" 
                                         class="w-full h-16 object-cover rounded">
                                @else
                                    <div class="w-full h-16 bg-gray-100 rounded flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <a href="{{ route('seller.products.create.step', 3) }}" 
                   class="mt-2 text-sm text-blue-600 hover:text-blue-700">
                    Medien bearbeiten →
                </a>
            </div>
            
            {{-- Details --}}
            <div>
                <div class="mb-4">
                    <h3 class="text-xl font-bold text-gray-900">{{ $product->title }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ $product->category->name ?? 'Keine Kategorie' }} • 
                        @switch($product->condition)
                            @case('new') Neu @break
                            @case('like_new') Wie neu @break
                            @case('good') Gut @break
                            @case('acceptable') Akzeptabel @break
                        @endswitch
                    </p>
                </div>
                
                <div class="prose prose-sm max-w-none mb-4">
                    <p class="text-gray-700">{{ Str::limit($product->description, 200) }}</p>
                </div>
                
                @if($product->brand || $product->model)
                    <div class="flex items-center space-x-4 text-sm text-gray-600 mb-4">
                        @if($product->brand)
                            <span><strong>Marke:</strong> {{ $product->brand }}</span>
                        @endif
                        @if($product->model)
                            <span><strong>Modell:</strong> {{ $product->model }}</span>
                        @endif
                    </div>
                @endif
                
                @if($product->retail_price)
                    <p class="text-sm text-gray-500 mb-4">
                        <strong>UVP:</strong> {{ number_format($product->retail_price, 2, ',', '.') }} €
                    </p>
                @endif
                
                {{-- Preis-Box --}}
                <div class="p-4 bg-blue-50 border-2 border-blue-200 rounded-lg">
                    <p class="text-sm text-gray-600 mb-1">Zielpreis</p>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ number_format($product->target_price, 2, ',', '.') }} €
                    </p>
                    
                    @php
                        $totalTarget = round($product->target_price / 0.7, 2);
                        $platformFee = round($totalTarget * 0.3, 2);
                    @endphp
                    
                    <div class="mt-3 pt-3 border-t border-blue-200 text-sm">
                        <div class="flex justify-between mb-1">
                            <span class="text-gray-600">Plattformprovision:</span>
                            <span class="font-medium">{{ number_format($platformFee, 2, ',', '.') }} €</span>
                        </div>
                        <div class="flex justify-between font-bold">
                            <span>Benötigte Lose:</span>
                            <span>{{ number_format($totalTarget, 0, ',', '.') }} Stück</span>
                        </div>
                    </div>
                </div>
                
                <a href="{{ route('seller.products.create.step', 4) }}" 
                   class="mt-2 text-sm text-blue-600 hover:text-blue-700">
                    Preis bearbeiten →
                </a>
                
                {{-- Entscheidung --}}
                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm font-medium text-gray-700 mb-1">Ihre Entscheidung:</p>
                    <p class="text-sm text-gray-600">
                        @if($product->decision_type === 'give')
                            🎁 Produkt wird auch bei Nichterreichung des Zielpreises abgegeben
                        @else
                            💰 Produkt wird behalten, Gewinner erhält Nettoerlös
                        @endif
                    </p>
                </div>
            </div>
        </div>
        
        {{-- Alle Details anzeigen --}}
        <div class="border-t pt-6">
            <details class="group">
                <summary class="cursor-pointer text-blue-600 hover:text-blue-700 font-medium flex items-center">
                    Vollständige Beschreibung anzeigen
                    <svg class="w-5 h-5 ml-1 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </summary>
                <div class="mt-4 prose prose-sm max-w-none">
                    <p class="text-gray-700 whitespace-pre-line">{{ $product->description }}</p>
                </div>
            </details>
        </div>
    </div>
    
    {{-- Zeitplanung --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Zeitplanung</h2>
        
        <form method="POST" action="{{ route('seller.products.create.step5') }}" id="step5Form">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Verlosungsdauer --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Verlosungsdauer <span class="text-red-500">*</span>
                    </label>
                    
                    <div class="space-y-2">
                        @foreach([3, 5, 7, 10] as $days)
                            <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all hover:border-blue-300 border-gray-200">
                                <input type="radio" 
                                       name="duration_days" 
                                       value="{{ $days }}" 
                                       class="peer sr-only"
                                       {{ old('duration_days', 7) == $days ? 'checked' : '' }}>
                                
                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full mr-3 peer-checked:border-blue-600 peer-checked:border-[6px] transition"></div>
                                
                                <span class="flex-1 font-medium text-gray-900">{{ $days }} Tage</span>
                                
                                <span class="text-sm text-gray-500">
                                    @if($days === 3) Schnell @elseif($days === 7) Empfohlen @elseif($days === 10) Maximal @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                    
                    @error('duration_days')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Startzeit --}}
                <div>
                    <label for="starts_at" class="block text-sm font-medium text-gray-700 mb-3">
                        Startzeitpunkt
                    </label>
                    
                    <div class="space-y-3">
                        <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all hover:border-blue-300 border-gray-200">
                            <input type="radio" 
                                   name="start_option" 
                                   value="now" 
                                   class="peer sr-only"
                                   checked
                                   onchange="toggleStartDate(false)">
                            
                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full mr-3 peer-checked:border-blue-600 peer-checked:border-[6px]"></div>
                            
                            <div class="flex-1">
                                <span class="font-medium text-gray-900">Sofort starten</span>
                                <p class="text-xs text-gray-500">Verlosung beginnt direkt nach Veröffentlichung</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all hover:border-blue-300 border-gray-200">
                            <input type="radio" 
                                   name="start_option" 
                                   value="scheduled" 
                                   class="peer sr-only"
                                   onchange="toggleStartDate(true)">
                            
                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full mr-3 peer-checked:border-blue-600 peer-checked:border-[6px]"></div>
                            
                            <div class="flex-1">
                                <span class="font-medium text-gray-900">Geplanter Start</span>
                                <p class="text-xs text-gray-500">Verlosung beginnt zu festgelegtem Zeitpunkt</p>
                            </div>
                        </label>
                        
                        <div id="scheduledDateInput" class="hidden pl-8">
                            <input type="datetime-local" 
                                   name="starts_at" 
                                   id="starts_at"
                                   min="{{ now()->format('Y-m-d\TH:i') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    @error('starts_at')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            {{-- Info Box --}}
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Empfehlung zur Verlosungsdauer</h3>
                        <p class="mt-1 text-sm text-blue-700">
                            7 Tage sind optimal für die meisten Produkte. Längere Zeiträume erhöhen die Reichweite, kürzere erzeugen mehr Dringlichkeit.
                        </p>
                    </div>
                </div>
            </div>
            
            {{-- Navigation --}}
            <div class="flex justify-between items-center pt-6 border-t mt-6">
                <a href="{{ route('seller.products.create.step', 4) }}" 
                   class="px-6 py-2 text-gray-700 hover:text-gray-900 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Zurück
                </a>
                
                <div class="flex space-x-3">
                    <button type="button" 
                            onclick="saveDraft()"
                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition">
                        Als Entwurf speichern
                    </button>
                    
                    <button type="submit" 
                            class="px-8 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Veröffentlichen
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleStartDate(show) {
    const input = document.getElementById('scheduledDateInput');
    if (show) {
        input.classList.remove('hidden');
        document.getElementById('starts_at').required = true;
    } else {
        input.classList.add('hidden');
        document.getElementById('starts_at').required = false;
        document.getElementById('starts_at').value = '';
    }
}

function saveDraft() {
    if (confirm('Möchten Sie diesen Entwurf für später speichern? Sie können jederzeit fortfahren.')) {
        window.location.href = '{{ route("seller.dashboard") }}';
    }
}

// Confirmation before publish
document.getElementById('step5Form').addEventListener('submit', function(e) {
    const duration = document.querySelector('input[name="duration_days"]:checked');
    const startOption = document.querySelector('input[name="start_option"]:checked');
    
    if (!duration) {
        e.preventDefault();
        alert('Bitte wählen Sie eine Verlosungsdauer');
        return false;
    }
    
    let message = 'Produkt jetzt veröffentlichen?\n\n';
    message += `• Verlosungsdauer: ${duration.value} Tage\n`;
    message += `• Start: ${startOption.value === 'now' ? 'Sofort' : 'Geplant'}\n`;
    message += `• Zielpreis: {{ number_format($product->target_price, 2, ',', '.') }} €`;
    
    if (!confirm(message)) {
        e.preventDefault();
        return false;
    }
});
</script>
@endsection