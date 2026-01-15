<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'KA Parcel Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* KAI Color Palette */
        :root {
            --kai-orange: #FF6B35;
            --kai-orange-dark: #E55A2B;
            --kai-orange-light: #FF8C5A;
            --kai-navy: #1E3A5F;
            --kai-navy-dark: #152844;
            --kai-navy-light: #2C4E7C;
        }
        
        .sidebar-transition {
            transition: all 0.3s ease-in-out;
        }
        
        .sidebar-collapsed {
            width: 80px !important;
        }
        
        .sidebar-collapsed .sidebar-text {
            display: none;
        }
        
        .sidebar-collapsed .sidebar-header h1 {
            display: none;
        }
        
        .main-content-expanded {
            margin-left: 80px !important;
        }
        
        .kai-gradient {
            background: linear-gradient(135deg, var(--kai-navy) 0%, var(--kai-navy-dark) 100%);
        }
        
        .kai-orange-gradient {
            background: linear-gradient(135deg, var(--kai-orange) 0%, var(--kai-orange-dark) 100%);
        }

        .kai-navy-gradient {
            background: linear-gradient(135deg, var(--kai-navy) 0%, var(--kai-navy-light) 100%);
        }

        .bg-kai-orange { background-color: var(--kai-orange); }
        .bg-kai-orange-dark { background-color: var(--kai-orange-dark); }
        .bg-kai-navy { background-color: var(--kai-navy); }
        .bg-kai-navy-light { background-color: var(--kai-navy-light); }

        .text-kai-orange { color: var(--kai-orange); }
        .text-kai-navy { color: var(--kai-navy); }
        .border-kai-orange { border-color: var(--kai-orange); }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen relative">
        <!-- Toggle Button -->
        <button id="sidebarToggle" class="fixed z-50 left-4 top-4 bg-kai-orange text-white p-3 rounded-lg shadow-lg hover:bg-kai-orange-dark transition-all duration-200">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Sidebar -->
        <div id="sidebar" class="sidebar-transition w-64 kai-gradient text-white fixed h-full z-40">
            <div class="p-6 sidebar-header">
                <h1 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-train mr-2 text-kai-orange"></i>
                    <span class="sidebar-text">KA Parcel</span>
                </h1>
            </div>
            
            <nav class="mt-6">
                <a href="{{ route('dashboard') }}" class="flex items-center px-6 py-3 hover:bg-kai-navy-light transition duration-200 {{ request()->routeIs('dashboard') ? 'bg-kai-navy-light border-l-4 border-kai-orange' : '' }}">
                    <i class="fas fa-home w-5 text-kai-orange"></i>
                    <span class="sidebar-text ml-3">Dashboard</span>
                </a>
                <a href="{{ route('input.data') }}" class="flex items-center px-6 py-3 hover:bg-kai-navy-light transition duration-200 {{ request()->routeIs('input.data') ? 'bg-kai-navy-light border-l-4 border-kai-orange' : '' }}">
                    <i class="fas fa-plus-circle w-5 text-kai-orange"></i>
                    <span class="sidebar-text ml-3">Input Data</span>
                </a>
                <a href="{{ route('preview.data') }}" class="flex items-center px-6 py-3 hover:bg-kai-navy-light transition duration-200 {{ request()->routeIs('preview.data') ? 'bg-kai-navy-light border-l-4 border-kai-orange' : '' }}">
                    <i class="fas fa-eye w-5 text-kai-orange"></i>
                    <span class="sidebar-text ml-3">Preview Data</span>
                </a>
                <a href="{{ route('statistik') }}" class="flex items-center px-6 py-3 hover:bg-kai-navy-light transition duration-200 {{ request()->routeIs('statistik') ? 'bg-kai-navy-light border-l-4 border-kai-orange' : '' }}">
                    <i class="fas fa-chart-bar w-5 text-kai-orange"></i>
                    <span class="sidebar-text ml-3">Statistik</span>
                </a>
            </nav>
            
            <div class="absolute bottom-0 w-full p-6">
                <form action="{{ route('logout') }}" method="POST" class="inline-block w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg transition duration-200">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span class="sidebar-text ml-2">Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div id="mainContent" class="flex-1 flex flex-col overflow-hidden sidebar-transition ml-64">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">@yield('header', 'Dashboard')</h2>
                    <div class="flex items-center">
                        <span class="text-gray-600">Welcome, {{ Auth::user()->name }}</span>
                        <div class="ml-4 w-10 h-10 bg-kai-orange rounded-full flex items-center justify-center">
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleBtn = document.getElementById('sidebarToggle');
            const toggleIcon = toggleBtn.querySelector('i');
            
            let isCollapsed = false;

            const applyState = () => {
                if (isCollapsed) {
                    sidebar.classList.add('sidebar-collapsed');
                    mainContent.classList.add('main-content-expanded');
                    toggleIcon.classList.remove('fa-bars');
                    toggleIcon.classList.add('fa-chevron-right');
                } else {
                    sidebar.classList.remove('sidebar-collapsed');
                    mainContent.classList.remove('main-content-expanded');
                    toggleIcon.classList.remove('fa-chevron-right');
                    toggleIcon.classList.add('fa-bars');
                }
            };

            try {
                isCollapsed = localStorage.getItem('sidebarCollapsed') === '1';
            } catch (e) {
                isCollapsed = false;
            }

            applyState();
            
            toggleBtn.addEventListener('click', function() {
                isCollapsed = !isCollapsed;

                try {
                    localStorage.setItem('sidebarCollapsed', isCollapsed ? '1' : '0');
                } catch (e) {
                    // ignore
                }

                applyState();
            });
        });
    </script>
</body>
</html>
