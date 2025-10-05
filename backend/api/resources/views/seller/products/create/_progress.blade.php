{{-- resources/views/seller/products/create/_progress.blade.php --}}

<div class="mb-8">
    {{-- Fortschrittsbalken --}}
    <div class="relative">
        {{-- Hintergrund-Linie --}}
        <div class="absolute top-5 left-0 right-0 h-1 bg-gray-200"></div>
        
        {{-- Aktiver Fortschritt --}}
        <div class="absolute top-5 left-0 h-1 bg-blue-600 transition-all duration-300" 
             style="width: {{ (($step - 1) / 4) * 100 }}%"></div>
        
        {{-- Schritte --}}
        <div class="relative flex justify-between">
            @foreach([
                1 => ['icon' => '📦', 'label' => 'Kategorie'],
                2 => ['icon' => '📝', 'label' => 'Details'],
                3 => ['icon' => '📸', 'label' => 'Medien'],
                4 => ['icon' => '💰', 'label' => 'Preis'],
                5 => ['icon' => '✅', 'label' => 'Vorschau'],
            ] as $num => $info)
                <div class="flex flex-col items-center">
                    {{-- Kreis --}}
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg font-bold transition-all
                        @if($num < $step) bg-blue-600 text-white
                        @elseif($num === $step) bg-blue-600 text-white ring-4 ring-blue-200
                        @else bg-gray-200 text-gray-400
                        @endif">
                        @if($num < $step)
                            ✓
                        @else
                            {{ $info['icon'] }}
                        @endif
                    </div>
                    
                    {{-- Label --}}
                    <span class="mt-2 text-xs font-medium 
                        @if($num <= $step) text-gray-900
                        @else text-gray-400
                        @endif">
                        {{ $info['label'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
    
    {{-- Info-Text --}}
    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
            Schritt <span class="font-bold text-blue-600">{{ $step }}</span> von 5
        </p>
    </div>
</div>