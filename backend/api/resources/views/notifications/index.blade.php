@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">
                            Benachrichtigungen
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            @if($unreadCount > 0)
                                Du hast {{ $unreadCount }} ungelesene Benachrichtigung{{ $unreadCount > 1 ? 'en' : '' }}
                            @else
                                Alle Benachrichtigungen gelesen
                            @endif
                        </p>
                    </div>
                    
                    @if($unreadCount > 0)
                        <form action="{{ route('notifications.read-all') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Alle als gelesen markieren
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Notifications List --}}
        @if($notifications->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="divide-y divide-gray-200">
                    @foreach($notifications as $notification)
                        <div class="p-6 hover:bg-gray-50 transition-colors {{ $notification->isUnread() ? 'bg-blue-50' : '' }}">
                            <div class="flex items-start space-x-4">
                                {{-- Icon basierend auf Typ --}}
                                <div class="flex-shrink-0">
                                    @if($notification->type === 'winner_notification')
                                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                            <span class="text-2xl">🎉</span>
                                        </div>
                                    @elseif($notification->type === 'seller_payout')
                                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span class="text-2xl">💰</span>
                                        </div>
                                    @elseif($notification->type === 'wallet_deposit')
                                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                            <span class="text-2xl">💳</span>
                                        </div>
                                    @elseif($notification->type === 'raffle_completed')
                                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                                            <span class="text-2xl">🎲</span>
                                        </div>
                                    @elseif($notification->type === 'spending_limit')
                                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                                            <span class="text-2xl">⚠️</span>
                                        </div>
                                    @else
                                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                                            <span class="text-2xl">📢</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Content --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="text-lg font-semibold text-gray-900">
                                                {{ $notification->title }}
                                            </p>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ $notification->message }}
                                            </p>
                                            <p class="text-xs text-gray-400 mt-2">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </p>
                                        </div>

                                        {{-- Unread Badge --}}
                                        @if($notification->isUnread())
                                            <div class="ml-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Neu
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="flex items-center space-x-3 mt-4">
                                        @if($notification->action_url)
                                            <a href="{{ $notification->action_url }}" 
                                               class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                                Details anzeigen →
                                            </a>
                                        @endif

                                        @if($notification->isUnread())
                                            <form action="{{ route('notifications.read', $notification) }}" 
                                                  method="POST" 
                                                  class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="text-sm text-gray-600 hover:text-gray-800">
                                                    Als gelesen markieren
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('notifications.destroy', $notification) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('Benachrichtigung wirklich löschen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-sm text-red-600 hover:text-red-800">
                                                Löschen
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">
                        Keine Benachrichtigungen
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Du hast noch keine Benachrichtigungen erhalten.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('dashboard') }}" 
                           class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                            Zurück zum Dashboard
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection