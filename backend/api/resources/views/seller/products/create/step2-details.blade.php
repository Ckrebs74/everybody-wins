{{-- resources/views/seller/products/create/step2-details.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Produktdetails</h1>
        <p class="mt-2 text-gray-600">Beschreiben Sie Ihr Produkt so detailliert wie möglich</p>
    </div>
    
    {{-- Fortschrittsanzeige --}}
    @include('seller.products.create._progress', ['step' => 2])
    
    {{-- Auto-Save Status --}}
    <div class="mb-4">
        <div id="autoSaveStatus" class="text-sm text-gray-500 hidden">
            <span class="inline-flex items-center">
                <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Speichere automatisch...
            </span>
        </div>
        <div id="autoSaveSuccess" class="text-sm text-green-600 hidden">
            ✓ Automatisch gespeichert um <span id="saveTime"></span>
        </div>
    </div>
    
    {{-- Formular --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="{{ route('seller.products.create.step2') }}" id="step2Form">
            @csrf
            
            {{-- Titel --}}
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Produkttitel <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="title" 
                       id="title" 
                       maxlength="255"
                       value="{{ old('title', $product->title ?? '') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                              @error('title') border-red-300 @enderror"
                       placeholder="z.B. iPhone 16 Pro Max 256GB Space Black">
                
                <div class="flex justify-between mt-2">
                    <p class="text-sm text-gray-500">Mindestens 10 Zeichen</p>
                    <p class="text-sm text-gray-500">
                        <span id="titleCount">0</span>/255
                    </p>
                </div>
                
                @error('title')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- Beschreibung --}}
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Produktbeschreibung <span class="text-red-500">*</span>
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="6"
                          maxlength="5000"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                 @error('description') border-red-300 @enderror"
                          placeholder="Beschreiben Sie Ihr Produkt detailliert: Zustand, Lieferumfang, Besonderheiten...">{{ old('description', $product->description ?? '') }}</textarea>
                
                <div class="flex justify-between mt-2">
                    <p class="text-sm text-gray-500">Mindestens 50 Zeichen</p>
                    <p class="text-sm text-gray-500">
                        <span id="descCount">0</span>/5000
                    </p>
                </div>
                
                @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- Zustand (erneut auswählbar) --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Zustand bestätigen <span class="text-red-500">*</span>
                </label>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach([
                        'new' => ['label' => 'Neu', 'icon' => '🆕'],
                        'like_new' => ['label' => 'Wie neu', 'icon' => '✨'],
                        'good' => ['label' => 'Gut', 'icon' => '👍'],
                        'acceptable' => ['label' => 'Akzeptabel', 'icon' => '👌'],
                    ] as $value => $info)
                        <label class="relative flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-blue-300
                            @error('condition') border-red-300 @else border-gray-200 @enderror">
                            
                            <input type="radio" 
                                   name="condition" 
                                   value="{{ $value }}" 
                                   class="peer sr-only"
                                   {{ old('condition', $product->condition ?? 'new') == $value ? 'checked' : '' }}>
                            
                            <span class="text-xl mr-2">{{ $info['icon'] }}</span>
                            <span class="text-sm font-medium">{{ $info['label'] }}</span>
                            
                            <div class="absolute inset-0 border-2 border-blue-600 rounded-lg opacity-0 peer-checked:opacity-100"></div>
                        </label>
                    @endforeach
                </div>
                
                @error('condition')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- Optionale Felder in Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Marke --}}
                <div>
                    <label for="brand" class="block text-sm font-medium text-gray-700 mb-2">
                        Marke (optional)
                    </label>
                    <input type="text" 
                           name="brand" 
                           id="brand" 
                           value="{{ old('brand', $product->brand ?? '') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="z.B. Apple, Samsung, Sony">
                    @error('brand')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Modell --}}
                <div>
                    <label for="model" class="block text-sm font-medium text-gray-700 mb-2">
                        Modell (optional)
                    </label>
                    <input type="text" 
                           name="model" 
                           id="model" 
                           value="{{ old('model', $product->model ?? '') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="z.B. Pro Max, S24 Ultra">
                    @error('model')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            {{-- UVP --}}
            <div class="mb-6">
                <label for="retail_price" class="block text-sm font-medium text-gray-700 mb-2">
                    Unverbindliche Preisempfehlung (UVP) (optional)
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500">€</span>
                    <input type="number" 
                           name="retail_price" 
                           id="retail_price" 
                           step="0.01"
                           min="0"
                           value="{{ old('retail_price', $product->retail_price ?? '') }}"
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="0.00">
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    Hilft bei der Preisempfehlung in Schritt 4
                </p>
                @error('retail_price')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- Navigation --}}
            <div class="flex justify-between items-center pt-6 border-t">
                <a href="{{ route('seller.products.create.step', 1) }}" 
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
// Character Counter
const titleInput = document.getElementById('title');
const descInput = document.getElementById('description');
const titleCount = document.getElementById('titleCount');
const descCount = document.getElementById('descCount');

function updateCount(input, counter) {
    counter.textContent = input.value.length;
}

titleInput.addEventListener('input', () => updateCount(titleInput, titleCount));
descInput.addEventListener('input', () => updateCount(descInput, descCount));

// Initial count
updateCount(titleInput, titleCount);
updateCount(descInput, descCount);

// Auto-Save alle 30 Sekunden
let autoSaveTimer;
let hasChanges = false;

function markAsChanged() {
    hasChanges = true;
}

['title', 'description', 'brand', 'model', 'retail_price'].forEach(field => {
    const el = document.getElementById(field);
    if (el) {
        el.addEventListener('input', markAsChanged);
    }
});

async function autoSave() {
    if (!hasChanges) return;
    
    const formData = new FormData(document.getElementById('step2Form'));
    const data = Object.fromEntries(formData);
    
    document.getElementById('autoSaveStatus').classList.remove('hidden');
    document.getElementById('autoSaveSuccess').classList.add('hidden');
    
    try {
        const response = await fetch('{{ route("seller.products.auto-save") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            hasChanges = false;
            document.getElementById('autoSaveStatus').classList.add('hidden');
            document.getElementById('autoSaveSuccess').classList.remove('hidden');
            document.getElementById('saveTime').textContent = result.timestamp;
        }
    } catch (error) {
        console.error('Auto-save failed:', error);
    }
}

// Auto-Save alle 30 Sekunden
setInterval(autoSave, 30000);

// Save before leaving page
window.addEventListener('beforeunload', (e) => {
    if (hasChanges) {
        autoSave();
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>
@endsection