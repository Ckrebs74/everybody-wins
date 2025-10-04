@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8">
    <h2 class="text-2xl font-bold mb-6">Registrierung</h2>
    
    <form method="POST" action="/register">
        @csrf
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">E-Mail</label>
            <input type="email" name="email" required 
                   class="w-full px-4 py-2 border rounded-lg @error('email') border-red-500 @enderror"
                   value="{{ old('email') }}">
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Vorname</label>
            <input type="text" name="first_name" required 
                   class="w-full px-4 py-2 border rounded-lg @error('first_name') border-red-500 @enderror"
                   value="{{ old('first_name') }}">
            @error('first_name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Nachname</label>
            <input type="text" name="last_name" required 
                   class="w-full px-4 py-2 border rounded-lg @error('last_name') border-red-500 @enderror"
                   value="{{ old('last_name') }}">
            @error('last_name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Geburtsdatum (Min. 18 Jahre)</label>
            <input type="date" name="birth_date" required 
                   class="w-full px-4 py-2 border rounded-lg @error('birth_date') border-red-500 @enderror"
                   value="{{ old('birth_date') }}">
            @error('birth_date')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Passwort</label>
            <input type="password" name="password" required 
                   class="w-full px-4 py-2 border rounded-lg @error('password') border-red-500 @enderror">
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Passwort bestätigen</label>
            <input type="password" name="password_confirmation" required 
                   class="w-full px-4 py-2 border rounded-lg">
        </div>
        
        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="accept_terms" required class="mr-2">
                <span class="text-sm">
                    Ich akzeptiere die AGB und bestätige, dass ich mindestens 18 Jahre alt bin.
                    Ich verstehe das 10€/Stunde Ausgabenlimit.
                </span>
            </label>
            @error('accept_terms')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <button type="submit" class="w-full bg-yellow-500 text-gray-800 font-bold py-3 rounded-lg hover:bg-yellow-400">
            Registrieren & 5€ Bonus sichern!
        </button>
    </form>
    
    <p class="mt-4 text-center text-gray-600">
        Schon registriert? <a href="/login" class="text-yellow-500 hover:underline">Zum Login</a>
    </p>
</div>
@endsection