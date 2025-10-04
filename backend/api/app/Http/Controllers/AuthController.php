<?php
// =====================================================
// FILE: app/Http/Controllers/AuthController.php
// =====================================================

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Registrierung neuer User
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'birth_date' => 'required|date|before:-18 years', // Min. 18 Jahre
            'accept_terms' => 'required|accepted'
        ]);

        DB::beginTransaction();
        try {
            // User erstellen
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'birth_date' => $validated['birth_date'],
                'role' => 'buyer',
                'age_verified' => true, // Da wir birth_date prüfen
            ]);

            // Wallet erstellen
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'bonus_balance' => 5.00, // 5€ Startguthaben!
            ]);

            DB::commit();

            // Automatisch einloggen
            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Willkommen bei Jeder Gewinnt! Du hast 5€ Startguthaben erhalten!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Registrierung fehlgeschlagen.');
        }
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Update last login
            Auth::user()->update(['last_login_at' => now()]);

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Die Anmeldedaten sind ungültig.',
        ])->onlyInput('email');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}