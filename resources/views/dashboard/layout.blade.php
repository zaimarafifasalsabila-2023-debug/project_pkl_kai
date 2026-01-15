<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'KA Parcel Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-purple-700 to-purple-900 text-white">
            <div class="p-6">
                <h1 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-train mr-2"></i>
                    KA Parcel
                </h1>
            </div>
            
            <nav class="mt-6">
                <a href="{{ route('dashboard') }}" class="flex items-center px-6 py-3 hover:bg-purple-800 transition duration-200 {{ request()->routeIs('dashboard') ? 'bg-purple-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-home w-5 mr-3"></i>
                    Dashboard
                </a>
                <a href="{{ route('input.data') }}" class="flex items-center px-6 py-3 hover:bg-purple-800 transition duration-200 {{ request()->routeIs('input.data') ? 'bg-purple-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-plus-circle w-5 mr-3"></i>
                    Input Data
                </a>
                <a href="{{ route('preview.data') }}" class="flex items-center px-6 py-3 hover:bg-purple-800 transition duration-200 {{ request()->routeIs('preview.data') ? 'bg-purple-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-eye w-5 mr-3"></i>
                    Preview Data
                </a>
                <a href="{{ route('statistik') }}" class="flex items-center px-6 py-3 hover:bg-purple-800 transition duration-200 {{ request()->routeIs('statistik') ? 'bg-purple-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-chart-bar w-5 mr-3"></i>
                    Statistik
                </a>
            </nav>
            
            <div class="absolute bottom-0 w-64 p-6">
                <form action="{{ route('logout') }}" method="POST" class="inline-block w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg transition duration-200">
                        <i class="fas fa-sign-out-alt w-5 mr-2"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">@yield('header', 'Dashboard')</h2>
                    <div class="flex items-center">
                        <span class="text-gray-600">Welcome, {{ Auth::user()->name }}</span>
                        <div class="ml-4 w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
