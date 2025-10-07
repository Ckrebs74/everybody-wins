<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    /**
     * Prüft ob der aktuelle User Seller-Rechte hat
     */
    public static function isSeller(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $allowedRoles = ['seller', 'both', 'admin'];
        return in_array(Auth::user()->role, $allowedRoles);
    }

    /**
     * Prüft ob der aktuelle User Admin ist
     */
    public static function isAdmin(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return Auth::user()->role === 'admin';
    }

    /**
     * Prüft ob der aktuelle User Käufer-Rechte hat
     */
    public static function isBuyer(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $allowedRoles = ['buyer', 'both', 'admin'];
        return in_array(Auth::user()->role, $allowedRoles);
    }

    /**
     * Gibt ein Badge-HTML für die User-Rolle zurück
     */
    public static function getRoleBadge(?string $role = null): string
    {
        $role = $role ?? Auth::user()->role ?? 'guest';

        $badges = [
            'admin' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Admin</span>',
            'seller' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Verkäufer</span>',
            'buyer' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Käufer</span>',
            'both' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Käufer & Verkäufer</span>',
            'guest' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Gast</span>',
        ];

        return $badges[$role] ?? $badges['guest'];
    }

    /**
     * Gibt lesbaren Text für die Rolle zurück
     */
    public static function getRoleText(?string $role = null): string
    {
        $role = $role ?? Auth::user()->role ?? 'guest';

        $texts = [
            'admin' => 'Administrator',
            'seller' => 'Verkäufer',
            'buyer' => 'Käufer',
            'both' => 'Käufer & Verkäufer',
            'guest' => 'Gast',
        ];

        return $texts[$role] ?? 'Unbekannt';
    }
}