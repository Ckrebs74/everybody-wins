@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Verlosungs-Verwaltung</h1>
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline">
            ← Zurück zum Dashboard
        </a>
    </div>

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
        
        <a href="?status=all" class="bg-gray-50 border-2 {{ $status === 'all' ? 'border-gray-500' : 'border-transparent' }} p-4 rounded-lg hover:shadow-md transition">
            <div class="text-sm text-gray-600 mb-1">Gesamt</div>
            <div class="text-3xl font-bold text-gray-600">{{ $stats['total'] }}</div>
        </a>
    </div>

    {{-- Raffles Table --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produkt</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Lose</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Einnahmen</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Endet am</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aktionen</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($raffles as $raffle)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        #{{ $raffle->id }}
                    </td>
                    
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @php
                                $productImages = $raffle->product->images()->orderBy('sort_order')->get();
                            @endphp
                            @if($productImages->isNotEmpty())
                                <img src="{{ $productImages->first()->image_path }}" 
                                     alt="{{ $raffle->product->title }}"
                                     class="w-12 h-12 rounded object-cover mr-3">
                            @else
                                <div class="w-12 h-12 bg-gray-200 rounded mr-3 flex items-center justify-center">
                                    <span class="text-xs text-gray-400">?</span>
                                </div>
                            @endif
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $raffle->product->title }}</div>
                                <div class="text-sm text-gray-500">{{ $raffle->product->seller->email }}</div>
                            </div>
                        </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            @if($raffle->status === 'active') bg-green-100 text-green-800
                            @elseif($raffle->status === 'pending_draw') bg-yellow-100 text-yellow-800
                            @elseif($raffle->status === 'completed') bg-blue-100 text-blue-800
                            @elseif($raffle->status === 'cancelled') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($raffle->status) }}
                        </span>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                        {{ $raffle->tickets_sold }}
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-green-600">
                        {{ number_format($raffle->total_revenue, 2, ',', '.') }} €
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $raffle->ends_at->format('d.m.Y H:i') }}
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="{{ route('admin.raffles.show', $raffle) }}" 
                               class="text-blue-600 hover:text-blue-900">
                                Details
                            </a>
                            
                            @if($raffle->status === 'active')
                                <span class="text-gray-300">|</span>
                                <a href="{{ route('admin.raffles.live-drawing', $raffle) }}" 
                                   class="text-yellow-600 hover:text-yellow-900">
                                    Live-Draw
                                </a>
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