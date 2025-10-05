@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb Navigation -->
    <div class="mb-6">
        <nav class="flex text-sm text-gray-600">
            <a href="{{ route('seller.dashboard') }}" class="hover:text-yellow-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('seller.products.index') }}" class="hover:text-yellow-600">Meine Produkte</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800">{{ $product->title }}</span>
        </nav>
    </div>

    <!-- Header mit Status und Actions -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="mb-4 lg:mb-0">
                <div class="flex items-center space-x-3 mb-2">
                    <h1 class="text-3xl font-bold text-gray-800">{{ $product->title }}</h1>
                    @php
                        $statusClasses = [
                            'draft' => 'bg-gray-100 text-gray-800',
                            'active' => 'bg-green-100 text-green-800',
                            'scheduled' => 'bg-blue-100 text-blue-800',
                            'completed' => 'bg-purple-100 text-purple-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                        ];
                        $statusLabels = [
                            'draft' => 'Entwurf',
                            'active' => 'Aktiv',
                            'scheduled' => 'Geplant',
                            'completed' => 'Abgeschlossen',
                            'cancelled' => 'Abgebrochen',
                        ];
                    @endphp
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $statusClasses[$product->status] }}">
                        {{ $statusLabels[$product->status] }}
                    </span>
                </div>
                <p class="text-gray-600">{{ $product->category->name ?? 'Keine Kategorie' }}</p>
            </div>

            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-2">
                @if($product->raffle)
                <a href="{{ route('raffles.show', $product->slug) }}" 
                   target="_blank"
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-300 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    Öffentliche Ansicht
                </a>
                
                <button onclick="copyRaffleLink()" 
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-300 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Link kopieren
                </button>
                @endif
                
                <a href="{{ route('seller.products.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-300 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Zurück
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Linke Spalte: Produktinformationen -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Bildergalerie -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Produktbilder</h2>
                
                @if($product->images()->exists())
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($product->images()->orderBy('sort_order')->get() as $image)
                        <div class="relative group">
                            <img src="{{ $image->image_path }}" 
                                 alt="{{ $image->alt_text ?? $product->title }}"
                                 class="w-full h-48 object-cover rounded-lg">
                            @if($image->is_primary)
                                <span class="absolute top-2 left-2 bg-yellow-500 text-white text-xs font-semibold px-2 py-1 rounded">
                                    Hauptbild
                                </span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 bg-gray-50 rounded-lg">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-gray-600">Keine Bilder vorhanden</p>
                    </div>
                @endif
            </div>

            <!-- Produktdetails -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Produktinformationen</h2>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm font-semibold text-gray-600">Marke:</span>
                            <p class="text-gray-800">{{ $product->brand ?? 'Nicht angegeben' }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-600">Modell:</span>
                            <p class="text-gray-800">{{ $product->model ?? 'Nicht angegeben' }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-600">Zustand:</span>
                            <p class="text-gray-800">
                                @php
                                    $conditions = [
                                        'new' => 'Neu',
                                        'like_new' => 'Wie neu',
                                        'good' => 'Gut',
                                        'acceptable' => 'Akzeptabel'
                                    ];
                                @endphp
                                {{ $conditions[$product->condition] ?? $product->condition }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-gray-600">UVP:</span>
                            <p class="text-gray-800">{{ $product->retail_price ? number_format($product->retail_price, 2, ',', '.') . ' €' : 'Nicht angegeben' }}</p>
                        </div>
                    </div>

                    <div>
                        <span class="text-sm font-semibold text-gray-600">Beschreibung:</span>
                        <p class="text-gray-800 mt-2 whitespace-pre-line">{{ $product->description }}</p>
                    </div>

                    <div>
                        <span class="text-sm font-semibold text-gray-600">Deine Entscheidung bei Nichterreichen des Zielpreises:</span>
                        <p class="text-gray-800 mt-1">
                            @if($product->decision_type === 'give')
                                <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Produkt trotzdem abgeben
                                </span>
                            @elseif($product->decision_type === 'keep')
                                <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Produkt behalten
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold">
                                    Noch nicht festgelegt
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Letzte Ticket-Käufe -->
            @if($product->raffle && $recentTickets->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Letzte Teilnehmer</h2>
                
                <div class="space-y-3">
                    @foreach($recentTickets as $ticket)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <div class="flex items-center space-x-3">
                            <div class="bg-yellow-100 rounded-full p-2">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{ substr($ticket->user->email, 0, 3) }}***{{ substr($ticket->user->email, -8) }}
                                </p>
                                <p class="text-sm text-gray-600">{{ $ticket->purchased_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-800">{{ number_format($ticket->price, 2, ',', '.') }} €</p>
                            <p class="text-xs text-gray-600">{{ $ticket->ticket_number }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Rechte Spalte: Statistiken und Status -->
        <div class="space-y-6">
            
            @if($stats)
            <!-- Verlosungs-Statistiken -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Live-Statistiken</h2>
                
                <!-- Fortschrittsbalken -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-semibold text-gray-600">Fortschritt</span>
                        <span class="text-lg font-bold text-yellow-600">{{ number_format($stats['progress_percentage'], 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="bg-yellow-500 h-4 rounded-full transition-all duration-500" 
                             style="width: {{ min(100, $stats['progress_percentage']) }}%"></div>
                    </div>
                    <div class="flex justify-between mt-2 text-sm text-gray-600">
                        <span>{{ number_format($product->raffle->total_revenue, 2, ',', '.') }} €</span>
                        <span>Ziel: {{ number_format($product->raffle->total_target, 2, ',', '.') }} €</span>
                    </div>
                </div>

                <!-- Statistik-Karten -->
                <div class="space-y-4">
                    <!-- Verkaufte Lose -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-blue-600 font-semibold">Verkaufte Lose</p>
                                <p class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['tickets_sold'] }}</p>
                            </div>
                            <div class="bg-blue-100 rounded-full p-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Umsatz -->
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-green-600 font-semibold">Aktueller Umsatz</p>
                                <p class="text-2xl font-bold text-green-900 mt-1">{{ number_format($stats['total_revenue'], 2, ',', '.') }} €</p>
                            </div>
                            <div class="bg-green-100 rounded-full p-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Teilnehmer -->
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-purple-600 font-semibold">Einzigartige Teilnehmer</p>
                                <p class="text-2xl font-bold text-purple-900 mt-1">{{ $stats['unique_participants'] }}</p>
                            </div>
                            <div class="bg-purple-100 rounded-full p-3">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Countdown / Zeit Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Zeitinformationen</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Gestartet:</span>
                        <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($product->raffle->starts_at)->format('d.m.Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Endet:</span>
                        <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($product->raffle->ends_at)->format('d.m.Y H:i') }}</span>
                    </div>
                    
                    @if($stats['days_remaining'] > 0)
                        @php
                            $daysRemaining = floor($stats['days_remaining']);
                            $hoursRemaining = round(($stats['days_remaining'] - $daysRemaining) * 24);
                        @endphp
                        <div class="bg-blue-50 rounded-lg p-4 mt-4">
                            <p class="text-sm text-blue-600 font-semibold mb-1">Verbleibende Zeit</p>
                            <p class="text-3xl font-bold text-blue-900">{{ $daysRemaining }}</p>
                            <p class="text-sm text-blue-600">
                                {{ $daysRemaining == 1 ? 'Tag' : 'Tage' }}
                                @if($hoursRemaining > 0)
                                    und {{ $hoursRemaining }} {{ $hoursRemaining == 1 ? 'Stunde' : 'Stunden' }}
                                @endif
                            </p>
                        </div>
                    @elseif($stats['days_remaining'] == 0)
                        <div class="bg-yellow-50 rounded-lg p-4 mt-4">
                            <p class="text-sm text-yellow-600 font-semibold mb-1">Status</p>
                            <p class="text-lg font-bold text-yellow-900">Endet heute!</p>
                        </div>
                    @else
                        <div class="bg-red-50 rounded-lg p-4 mt-4">
                            <p class="text-sm text-red-600 font-semibold mb-1">Status</p>
                            <p class="text-lg font-bold text-red-900">Verlosung beendet</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Zielpreis erreicht? -->
            @if($stats['target_reached'])
                <div class="bg-green-50 border-2 border-green-500 rounded-lg p-6">
                    <div class="flex items-center mb-3">
                        <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-bold text-green-900">Zielpreis erreicht!</h3>
                    </div>
                    <p class="text-green-800">
                        Herzlichen Glückwunsch! Der Zielpreis von {{ number_format($product->target_price, 2, ',', '.') }} € wurde erreicht.
                    </p>
                </div>
            @else
                <div class="bg-yellow-50 border-2 border-yellow-500 rounded-lg p-6">
                    <div class="flex items-center mb-3">
                        <svg class="w-8 h-8 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-bold text-yellow-900">Zielpreis noch nicht erreicht</h3>
                    </div>
                    <p class="text-yellow-800">
                        Noch {{ number_format($product->raffle->total_target - $product->raffle->total_revenue, 2, ',', '.') }} € bis zum Zielpreis.
                    </p>
                </div>
            @endif

            @else
                <!-- Keine aktive Verlosung -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <p class="text-gray-600 text-center">Keine aktive Verlosung für dieses Produkt.</p>
                </div>
            @endif

            <!-- Dein Erlös -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Dein Zielpreis</h2>
                <div class="text-center">
                    <p class="text-4xl font-bold text-gray-900">{{ number_format($product->target_price, 2, ',', '.') }} €</p>
                    <p class="text-sm text-gray-600 mt-2">Netto-Erlös bei Zielpreiserreichung</p>
                    @if($product->raffle)
                    <p class="text-xs text-gray-500 mt-4">
                        Plattform-Gebühr (30%): {{ number_format($product->raffle->platform_fee, 2, ',', '.') }} €
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Link kopieren Script -->
@if($product->raffle)
<script>
function copyRaffleLink() {
    const url = '{{ route('raffles.show', $product->slug) }}';
    navigator.clipboard.writeText(url).then(function() {
        alert('Link wurde in die Zwischenablage kopiert!');
    }, function(err) {
        console.error('Fehler beim Kopieren: ', err);
    });
}
</script>
@endif
@endsection