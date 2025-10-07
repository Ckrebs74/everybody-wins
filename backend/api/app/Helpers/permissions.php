<?php

use App\Helpers\PermissionHelper;

/**
 * Globale Helper-Funktionen für Berechtigungen
 * Können direkt in Blade-Views verwendet werden
 */

if (!function_exists('is_seller')) {
    /**
     * Prüft ob der aktuelle User Seller-Rechte hat
     */
    function is_seller(): bool
    {
        return PermissionHelper::isSeller();
    }
}

if (!function_exists('is_admin')) {
    /**
     * Prüft ob der aktuelle User Admin ist
     */
    function is_admin(): bool
    {
        return PermissionHelper::isAdmin();
    }
}

if (!function_exists('is_buyer')) {
    /**
     * Prüft ob der aktuelle User Käufer-Rechte hat
     */
    function is_buyer(): bool
    {
        return PermissionHelper::isBuyer();
    }
}

if (!function_exists('role_badge')) {
    /**
     * Gibt ein Badge-HTML für die User-Rolle zurück
     */
    function role_badge(?string $role = null): string
    {
        return PermissionHelper::getRoleBadge($role);
    }
}

if (!function_exists('role_text')) {
    /**
     * Gibt lesbaren Text für die Rolle zurück
     */
    function role_text(?string $role = null): string
    {
        return PermissionHelper::getRoleText($role);
    }
}