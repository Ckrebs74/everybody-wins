{{-- Notification Dropdown Component --}}
<div class="relative" x-data="notificationDropdown()">
    {{-- Bell Icon mit Badge --}}
    <button @click="toggleDropdown" 
            class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none">
        {{-- Bell Icon --}}
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        
        {{-- Unread Badge --}}
        <span x-show="unreadCount > 0" 
              x-text="unreadCount" 
              class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
        </span>
    </button>

    {{-- Dropdown Menu --}}
    <div x-show="isOpen" 
         @click.away="isOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 z-50 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200"
         style="display: none;">
        
        {{-- Header --}}
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 rounded-t-lg">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">
                    Benachrichtigungen
                </h3>
                <button @click="markAllAsRead" 
                        x-show="unreadCount > 0"
                        class="text-xs text-blue-600 hover:text-blue-800">
                    Alle als gelesen
                </button>
            </div>
        </div>

        {{-- Notifications List --}}
        <div class="max-h-96 overflow-y-auto">
            <template x-if="notifications.length === 0">
                <div class="px-4 py-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-sm">Keine neuen Benachrichtigungen</p>
                </div>
            </template>

            <template x-for="notification in notifications" :key="notification.id">
                <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer"
                     :class="{ 'bg-blue-50': !notification.read_at }"
                     @click="handleNotificationClick(notification)">
                    <div class="flex items-start space-x-3">
                        {{-- Icon basierend auf Typ --}}
                        <div class="flex-shrink-0">
                            <template x-if="notification.type === 'winner_notification'">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-xl">🎉</span>
                                </div>
                            </template>
                            <template x-if="notification.type === 'seller_payout'">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-xl">💰</span>
                                </div>
                            </template>
                            <template x-if="notification.type === 'wallet_deposit'">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-xl">💳</span>
                                </div>
                            </template>
                            <template x-if="notification.type === 'raffle_completed'">
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                    <span class="text-xl">🎲</span>
                                </div>
                            </template>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                            <p class="text-xs text-gray-600 mt-1 line-clamp-2" x-text="notification.message"></p>
                            <p class="text-xs text-gray-400 mt-1" x-text="formatTime(notification.created_at)"></p>
                        </div>

                        {{-- Unread Indicator --}}
                        <template x-if="!notification.read_at">
                            <div class="flex-shrink-0">
                                <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
            <a href="{{ route('notifications.index') }}" 
               class="block text-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                Alle Benachrichtigungen anzeigen
            </a>
        </div>
    </div>
</div>

{{-- Alpine.js Component Logic --}}
<script>
function notificationDropdown() {
    return {
        isOpen: false,
        notifications: [],
        unreadCount: 0,

        init() {
            this.loadNotifications();
            // Optional: Alle 30 Sekunden aktualisieren
            setInterval(() => {
                if (!this.isOpen) {
                    this.loadNotifications();
                }
            }, 30000);
        },

        toggleDropdown() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.loadNotifications();
            }
        },

        async loadNotifications() {
            try {
                const response = await fetch('{{ route('api.notifications.unread') }}');
                const data = await response.json();
                this.notifications = data.notifications;
                this.unreadCount = data.unreadCount;
            } catch (error) {
                console.error('Fehler beim Laden der Benachrichtigungen:', error);
            }
        },

        async markAllAsRead() {
            try {
                const response = await fetch('{{ route('notifications.read-all') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    this.loadNotifications();
                }
            } catch (error) {
                console.error('Fehler beim Markieren:', error);
            }
        },

        async handleNotificationClick(notification) {
            // Markiere als gelesen
            if (!notification.read_at) {
                try {
                    await fetch(`/notifications/${notification.id}/read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                } catch (error) {
                    console.error('Fehler beim Markieren:', error);
                }
            }

            // Navigiere zur Action URL
            if (notification.action_url) {
                window.location.href = notification.action_url;
            }
        },

        formatTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'Gerade eben';
            if (diffMins < 60) return `vor ${diffMins} Min`;
            if (diffHours < 24) return `vor ${diffHours} Std`;
            if (diffDays === 1) return 'Gestern';
            if (diffDays < 7) return `vor ${diffDays} Tagen`;
            
            return date.toLocaleDateString('de-DE');
        }
    }
}
</script>