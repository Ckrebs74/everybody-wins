{{-- resources/views/seller/products/create/step1-category.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Produkt erstellen</h1>
        <p class="mt-2 text-gray-600">Erstellen Sie Ihre Verlosung in wenigen einfachen Schritten</p>
    </div>
    
    {{-- Fortschrittsanzeige --}}
    @include('seller.products.create._progress', ['step' => 1])
    
    {{-- Formular --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="{{ route('seller.products.create.step1') }}" id="step1Form">
            @csrf
            
            {{-- Kategorie-Auswahl --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Kategorie auswählen <span class="text-red-500">*</span>
                </label>
                
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($categories as $category)
                        <label class="relative flex flex-col items-center p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-blue-300
                            @error('category_id') border-red-300 @else border-gray-200 @enderror">
                            
                            <input type="radio" 
                                   name="category_id" 
                                   value="{{ $category->id }}" 
                                   class="peer sr-only"
                                   {{ old('category_id') == $category->id ? 'checked' : '' }}>
                            
                            {{-- Icon --}}
                            <div class="text-4xl mb-2">
                                {{ $category->icon ?? '📦' }}
                            </div>
                            
                            {{-- Name --}}
                            <span class="text-sm font-medium text-gray-900">{{ $category->name }}</span>
                            
                            {{-- Selected Ring --}}
                            <div class="absolute inset-0 border-2 border-blue-600 rounded-lg opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            <div class="absolute top-2 right-2 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center opacity-0 peer-checked:opacity-100 transition-opacity">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </label>
                    @endforeach
                </div>
                
                @error('category_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- Produktzustand --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Zustand des Produkts <span class="text-red-500">*</span>
                </label>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach([
                        'new' => ['label' => 'Neu', 'desc' => 'Originalverpackt', 'icon' => '🆕'],
                        'like_new' => ['label' => 'Wie neu', 'desc' => 'Kaum benutzt', 'icon' => '✨'],
                        'good' => ['label' => 'Gut', 'desc' => 'Normale Gebrauchsspuren', 'icon' => '👍'],
                        'acceptable' => ['label' => 'Akzeptabel', 'desc' => 'Sichtbare Gebrauchsspuren', 'icon' => '👌'],
                    ] as $value => $info)
                        <label class="relative flex flex-col p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-blue-300
                            @error('condition') border-red-300 @else border-gray-200 @enderror">
                            
                            <input type="radio" 
                                   name="condition" 
                                   value="{{ $value }}" 
                                   class="peer sr-only"
                                   {{ old('condition', 'new') == $value ? 'checked' : '' }}>
                            
                            <div class="text-2xl mb-1">{{ $info['icon'] }}</div>
                            <span class="text-sm font-medium text-gray-900">{{ $info['label'] }}</span>
                            <span class="text-xs text-gray-500">{{ $info['desc'] }}</span>
                            
                            {{-- Selected State --}}
                            <div class="absolute inset-0 border-2 border-blue-600 rounded-lg opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                        </label>
                    @endforeach
                </div>
                
                @error('condition')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- Navigation --}}
            <div class="flex justify-between items-center pt-6 border-t">
                <a href="{{ route('seller.dashboard') }}" 
                   class="px-6 py-2 text-gray-700 hover:text-gray-900 transition">
                    Abbrechen
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
    
    {{-- Hilfe-Box --}}
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Tipp zur Kategorie-Auswahl</h3>
                <p class="mt-1 text-sm text-blue-700">
                    Wählen Sie die passende Kategorie sorgfältig aus - dies hilft Käufern, Ihr Produkt zu finden und verbessert die Preisempfehlung in Schritt 4.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Formular-Validierung vor Submit
document.getElementById('step1Form').addEventListener('submit', function(e) {
    const category = document.querySelector('input[name="category_id"]:checked');
    const condition = document.querySelector('input[name="condition"]:checked');
    
    if (!category) {
        e.preventDefault();
        alert('Bitte wählen Sie eine Kategorie aus');
        return false;
    }
    
    if (!condition) {
        e.preventDefault();
        alert('Bitte wählen Sie einen Zustand aus');
        return false;
    }
});
</script>
@endsection