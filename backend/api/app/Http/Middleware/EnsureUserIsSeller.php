<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSeller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Prüfe ob User eingeloggt ist
        if (!$request->user()) {
            return redirect()->route('login')->with('error', 'Bitte melde dich an, um fortzufahren.');
        }

        // Prüfe ob User Verkäufer, Both oder Admin ist
        $allowedRoles = ['seller', 'both', 'admin'];
        
        if (!in_array($request->user()->role, $allowedRoles)) {
            return redirect()->route('dashboard')
                ->with('error', 'Du hast keine Berechtigung für diesen Bereich. Bitte registriere dich als Verkäufer.');
        }

        return $next($request);
    }
}