{{-- resources/views/seller/products/create/step3-media.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Bilder & Videos</h1>
        <p class="mt-2 text-gray-600">Min. 1 Medium, Max. 10 Medien gesamt (Bilder + Videos)</p>
    </div>
    
    {{-- Fortschrittsanzeige --}}
    @include('seller.products.create._progress', ['step' => 3])
    
    {{-- Formular --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="{{ route('seller.products.create.step3') }}" enctype="multipart/form-data" id="step3Form">
            @csrf
            
            {{-- Vorhandene Medien --}}
            @if($product->images()->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Hochgeladene Medien</h3>
                    
                    <div id="existingMedia" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($product->images()->orderBy('sort_order')->get() as $media)
                            <div class="relative group" data-media-id="{{ $media->id }}">
                                {{-- Bild/Video Preview --}}
                                @if($media->media_type === 'image')
                                    <img src="{{ $media->thumbnail_path ?: $media->image_path }}" 
                                         alt="{{ $media->alt_text }}"
                                         class="w-full h-32 object-cover rounded-lg">
                                @else
                                    <div class="relative w-full h-32 bg-gray-100 rounded-lg overflow-hidden">
                                        <img src="{{ $media->thumbnail_path }}" 
                                             alt="{{ $media->alt_text }}"
                                             class="w-full h-full object-cover">
                                        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-40">
                                            <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"></path>
                                            </svg>
                                        </div>
                                        @if($media->duration)
                                            <span class="absolute bottom-2 right-2 px-2 py-1 bg-black bg-opacity-70 text-white text-xs rounded">
                                                {{ gmdate('i:s', $media->duration) }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                                
                                {{-- Primary Badge --}}
                                @if($media->is_primary)
                                    <span class="absolute top-2 left-2 px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded">
                                        Hauptbild
                                    </span>
                                @endif
                                
                                {{-- Löschen Button --}}
                                <button type="button"
                                        onclick="deleteMedia({{ $media->id }})"
                                        class="absolute top-2 right-2 w-8 h-8 bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            {{-- Upload Area --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Neue Medien hochladen <span class="text-red-500">*</span>
                </label>
                
                {{-- Drag & Drop Zone --}}
                <div id="dropZone" 
                     class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition-all hover:border-blue-400 cursor-pointer
                            @error('media') border-red-300 @enderror">
                    
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    
                    <p class="text-lg font-medium text-gray-700 mb-2">
                        Dateien hier ablegen oder klicken zum Hochladen
                    </p>
                    
                    <p class="text-sm text-gray-500 mb-4">
                        Erlaubte Formate: JPEG, PNG, WebP (max. 5MB) • MP4, WebM, MOV (max. 50MB)
                    </p>
                    
                    <input type="file" 
                           id="mediaInput" 
                           name="media[]" 
                           multiple 
                           accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime"
                           class="hidden">
                    
                    <button type="button" 
                            onclick="document.getElementById('mediaInput').click()"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Dateien auswählen
                    </button>
                </div>
                
                @error('media')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('media.*')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- Preview der neuen Uploads --}}
            <div id="previewContainer" class="hidden mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Vorschau der neuen Uploads</h3>
                <div id="previews" class="grid grid-cols-2 md:grid-cols-4 gap-4"></div>
            </div>
            
            {{-- Info Box --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Tipps für bessere Verkäufe:</h3>
                        <ul class="mt-2 text-sm text-blue-700 list-disc list-inside space-y-1">
                            <li>Verwenden Sie hochauflösende Bilder mit guter Beleuchtung</li>
                            <li>Zeigen Sie das Produkt aus verschiedenen Perspektiven</li>
                            <li>Videos erhöhen die Glaubwürdigkeit deutlich</li>
                            <li>Das erste Medium wird als Hauptbild verwendet</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            {{-- Navigation --}}
            <div class="flex justify-between items-center pt-6 border-t">
                <a href="{{ route('seller.products.create.step', 2) }}" 
                   class="px-6 py-2 text-gray-700 hover:text-gray-900 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Zurück
                </a>
                
                <button type="submit" 
                        id="submitBtn"
                        class="px-8 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition flex items-center disabled:bg-gray-400 disabled:cursor-not-allowed">
                    <span id="submitText">Weiter</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const dropZone = document.getElementById('dropZone');
const mediaInput = document.getElementById('mediaInput');
const previewContainer = document.getElementById('previewContainer');
const previews = document.getElementById('previews');
const submitBtn = document.getElementById('submitBtn');
const submitText = document.getElementById('submitText');

// Drag & Drop Events
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => {
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
    });
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });
});

dropZone.addEventListener('drop', (e) => {
    const files = e.dataTransfer.files;
    handleFiles(files);
});

mediaInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
});

function handleFiles(files) {
    const filesArray = Array.from(files);
    
    // Prüfe Anzahl
    const existingCount = document.querySelectorAll('#existingMedia [data-media-id]').length;
    const totalCount = existingCount + filesArray.length;
    
    if (totalCount > 10) {
        alert(`Maximal 10 Medien erlaubt. Sie haben bereits ${existingCount} Medien hochgeladen.`);
        return;
    }
    
    // Clear existing previews
    previews.innerHTML = '';
    previewContainer.classList.remove('hidden');
    
    filesArray.forEach((file, index) => {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            const preview = createPreview(file, e.target.result);
            previews.appendChild(preview);
        };
        
        if (file.type.startsWith('image/')) {
            reader.readAsDataURL(file);
        } else if (file.type.startsWith('video/')) {
            // For videos, create a placeholder
            createVideoPreview(file);
        }
    });
}

function createPreview(file, src) {
    const div = document.createElement('div');
    div.className = 'relative group';
    
    const isVideo = file.type.startsWith('video/');
    const sizeKB = (file.size / 1024).toFixed(0);
    const sizeMB = (file.size / 1024 / 1024).toFixed(2);
    
    div.innerHTML = `
        ${isVideo ? `
            <div class="relative w-full h-32 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
                <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"></path>
                </svg>
                <span class="absolute bottom-2 right-2 px-2 py-1 bg-black bg-opacity-70 text-white text-xs rounded">
                    ${sizeMB} MB
                </span>
            </div>
        ` : `
            <img src="${src}" class="w-full h-32 object-cover rounded-lg">
        `}
        <p class="mt-1 text-xs text-gray-600 truncate">${file.name}</p>
        <p class="text-xs text-gray-500">${isVideo ? sizeMB + ' MB' : sizeKB + ' KB'}</p>
    `;
    
    return div;
}

function createVideoPreview(file) {
    const div = createPreview(file, '');
    previews.appendChild(div);
}

// AJAX: Medium löschen
async function deleteMedia(mediaId) {
    if (!confirm('Medium wirklich löschen?')) return;
    
    try {
        const response = await fetch(`/seller/products/media/${mediaId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.querySelector(`[data-media-id="${mediaId}"]`).remove();
        } else {
            alert('Fehler beim Löschen: ' + result.message);
        }
    } catch (error) {
        alert('Fehler beim Löschen');
    }
}

// Upload-Fortschritt
document.getElementById('step3Form').addEventListener('submit', function(e) {
    const files = mediaInput.files;
    const existingCount = document.querySelectorAll('#existingMedia [data-media-id]').length;
    
    // Prüfe ob mindestens 1 Medium vorhanden
    if (existingCount === 0 && files.length === 0) {
        e.preventDefault();
        alert('Bitte laden Sie mindestens 1 Bild oder Video hoch');
        return false;
    }
    
    // Show loading state
    submitBtn.disabled = true;
    submitText.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Lädt hoch...';
});
</script>
@endsection