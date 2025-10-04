<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Jeder Gewinnt!')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-yellow-500 shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="/" class="text-2xl font-bold text-gray-800">🎯 Jeder Gewinnt!</a>
                
                <div class="flex items-center space-x-4">
                    <a href="/raffles" class="text-gray-800 hover:text-gray-600">Verlosungen</a>
                    
                    @auth
                        <a href="/dashboard" class="text-gray-800 hover:text-gray-600">Dashboard</a>
                        <span class="text-gray-700">
                            Guthaben: {{ Auth::user()->wallet->balance }}€
                        </span>
                        <form method="POST" action="/logout" class="inline">
                            @csrf
                            <button class="bg-gray-800 text-white px-4 py-2 rounded">Logout</button>
                        </form>
                    @else
                        <a href="/login" class="text-gray-800 hover:text-gray-600">Login</a>
                        <a href="/register" class="bg-gray-800 text-white px-4 py-2 rounded">Registrieren</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="bg-green-500 text-white p-4 text-center">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-500 text-white p-4 text-center">
            {{ session('error') }}
        </div>
    @endif

    <main class="container mx-auto px-4 py-8">
        @yield('content')
    </main>

    <footer class="bg-gray-800 text-white text-center p-4 mt-8">
        <p>© 2024 Jeder Gewinnt! - Max. 10€/Stunde Ausgabenlimit</p>
    </footer>
</body>
</html>