@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8">
    <h2 class="text-2xl font-bold mb-6">Registrierung</h2>
    
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form method="POST" action="{{ route('register') }}">
        @csrf
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Name</label>
            <input type="text" name="name" required 
                   class="w-full px-4 py-2 border rounded-lg @error('name') border-red-500 @enderror"
                   value="{{ old('name') }}">
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
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
        
        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" name="terms" required class="mr-2">
                <span class="text-sm">Ich akzeptiere die <a href="#" class="text-yellow-500 hover:underline">AGB</a> und <a href="#" class="text-yellow-500 hover:underline">Datenschutzerklärung</a></span>
            </label>
        </div>
        
        <button type="submit" class="w-full bg-yellow-500 text-gray-800 font-bold py-3 rounded-lg hover:bg-yellow-400 transition">
            Registrieren
        </button>
    </form>
    
    <p class="mt-4 text-center text-gray-600">
        Bereits ein Konto? <a href="{{ route('login') }}" class="text-yellow-500 hover:underline">Jetzt einloggen</a>
    </p>
</div>
@endsection