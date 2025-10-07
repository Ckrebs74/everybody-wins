@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Raffle Management</h1>
            <p class="text-gray-600 mt-1">Verwalte und ziehe Verlosungen</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.raffles.index') }}" 
               class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                🔄 Aktualisieren
            </a>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-5 gap-4 mb-8">
        <a href="?status=scheduled" class="bg-blue-50 border-2 {{ $status === 'scheduled' ? 'border-blue-500' : 'border-transparent' }} p-4 rounded-lg hover:shadow-md transition">
            <div class="text-sm text-gray-600 mb-1">Geplant</div>
            <div class="text-3xl font-bold text-blue-600">{{ $stats['scheduled'] }}</div>
        </a>
        
        <a href="?status=active" class="bg-green-50 border-2 {{ $status === 'active' ? 'border-green-500' : 'border-transparent' }} p-4 rounded-lg hover:shadow-md transition">
            <div class="text-sm text-gray-600 mb-1">Aktiv</div>
            <div class="text-3xl font-bold text-green-600">{{ $stats['active'] }}</div>
        </a>
        
        <a href="?status=pending_draw" class="bg-yellow-50 border-2 {{ $status === 'pending_draw' ? 'border-yellow-500' : 'border-transparent' }} p-4 rounded-lg hover:shadow-md transition">
            <div class="text-sm text-gray-600 mb-1">Bereit zur Ziehung</div>
            <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending_draw'] }}</div>
        </a>
        
        <a href="?status=completed" class="bg-purple-50 border-2 {{ $status === 'completed' ? 'border-purple-500' : 'border-transparent' }} p-4 rounded-lg hover:shadow-md transition">
            <div class="text-sm text-gray-600 mb-1">Abgeschlossen</div>
            <div class="text-3xl font-bold text-purple-600">{{ $stats['completed'] }}</div>
        </a>
        
        <a href="?status=cancelled" class="bg-gray-50 border-2 {{ $status === 'cancelled' ? 'border-gray-500' : 'border-transparent' }} p-4 rounded-lg hover:shadow-md transition">
            <div class="text-sm text-gray-600 mb-1">Abgebrochen</div>
            <div class="text-3xl font-bold text-gray-600">{{ $stats['cancelled'] }}</div>
        </a>
    </div>

    {{-- Filter Tabs --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="flex border-b">
            <a href="?status=all" 
               class="px-6 py-3 {{ $status === 'all' ? 'border-b-2 border-blue-500 text-blue-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                Alle
            </a>
            <a href="?status=pending_draw" 
               class="px-6 py-3 {{ $status === 'pending_draw' ? 'border-b-2 border-blue-500 text-blue-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                🎲 Bereit zur Ziehung
            </a>
            <a href="?status=active" 
               class="px-6 py-3 {{ $status === 'active' ? 'border-b-2 border-blue-500 text-blue-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                ✓ Aktive Verlosungen
            </a>
            <a href="?status=completed" 
               class="px-6 py-3 {{ $status === 'completed' ? 'border-b-2 border-blue-500 text-blue-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                📦 Abgeschlossen
            </a>
        </div>
    </div>

    {{-- Raffle Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produkt</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zeitraum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verkauf</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fortschritt</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aktionen</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($raffles as $raffle)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        #{{ $raffle->id }}
                    </td>
                    
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @if($raffle->product->images->isNotEmpty())
                                <img src="{{ $raffle->product->images->first()->image_path }}" 
                                     class="w-12 h-12 rounded object-cover mr-3">
                            @endif
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $raffle->product->title }}</div>
                                <div class="text-sm text-gray-500">{{ $raffle->product->seller->email }}</div>
                            </div>
                        </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'scheduled' => 'bg-blue-100 text-blue-800',
                                'active' => 'bg-green-100 text-green-800',
                                'pending_draw' => 'bg-yellow-100 text-yellow-800',
                                'completed' => 'bg-purple-100 text-purple-800',
                                'cancelled' => 'bg-gray-100 text-gray-800',
                                'refunded' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$raffle->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($raffle->status) }}
                        </span>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div>Start: {{ $raffle->starts_at->format('d.m.Y H:i') }}</div>
                        <div>Ende: {{ $raffle->ends_at->format('d.m.Y H:i') }}</div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div>{{ $raffle->tickets_sold }} Lose</div>
                        <div class="text-gray-500">€{{ number_format($raffle->total_revenue, 2) }}</div>
                    </td>
                    
                    <td class="px-6 py-4">
                        @php
                            $percentage = $raffle->total_target > 0 
                                ? min(100, ($raffle->total_revenue / $raffle->total_target) * 100) 
                                : 0;
                        @endphp
                        <div class="flex items-center">
                            <div class="flex-1">
                                <div class="text-xs text-gray-600 mb-1">
                                    €{{ number_format($raffle->total_revenue, 2) }} / €{{ number_format($raffle->total_target, 2) }}
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                            <div class="ml-2 text-sm font-semibold {{ $percentage >= 100 ? 'text-green-600' : 'text-gray-600' }}">
                                {{ round($percentage) }}%
                            </div>
                        </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.raffles.show', $raffle->id) }}" 
                               class="text-blue-600 hover:text-blue-900">
                                Details
                            </a>
                            
                            @if($raffle->status === 'pending_draw')
                                <a href="{{ route('admin.raffles.live-drawing', $raffle->id) }}" 
                                   class="text-green-600 hover:text-green-900 font-semibold">
                                    🎲 Ziehen
                                </a>
                            @endif
                            
                            @if($raffle->status === 'scheduled')
                                <form action="{{ route('admin.raffles.start', $raffle->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900">
                                        ▶️ Starten
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        Keine Verlosungen gefunden.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $raffles->links() }}
    </div>
</div>
@endsection