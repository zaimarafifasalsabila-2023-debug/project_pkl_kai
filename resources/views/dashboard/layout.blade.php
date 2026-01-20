<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'UPT Terminal Babat Dashboard')</title>
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

        .page-enter {
            opacity: 0;
            transform: translateY(6px);
            animation: pageEnter 450ms ease forwards;
        }

        @keyframes pageEnter {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .reveal {
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 500ms ease, transform 500ms ease;
            will-change: opacity, transform;
        }

        .reveal.reveal-active {
            opacity: 1;
            transform: translateY(0);
        }

        @media (prefers-reduced-motion: reduce) {
            .page-enter {
                opacity: 1;
                transform: none;
                animation: none;
            }

            .reveal {
                opacity: 1;
                transform: none;
                transition: none;
            }
        }

        .toast-container {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .toast {
            pointer-events: auto;
            min-width: 280px;
            max-width: 420px;
            padding: 12px 14px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
            border: 1px solid rgba(0, 0, 0, 0.08);
            background: #fff;
            opacity: 0;
            transform: translateY(-8px);
            transition: opacity 220ms ease, transform 220ms ease;
        }

        .toast.toast-show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast-success { border-left: 6px solid #16a34a; }
        .toast-warning { border-left: 6px solid #f59e0b; }
        .toast-error { border-left: 6px solid #dc2626; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen relative">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar-transition w-64 kai-gradient text-white fixed h-full z-40">
            <div class="p-6 sidebar-header">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-train mr-2 text-kai-orange"></i>
                        <span class="sidebar-text">UPT Terminal Babat</span>
                    </h1>

                    <button id="sidebarToggle" type="button" class="bg-kai-orange text-white p-2 rounded-lg shadow hover:bg-kai-orange-dark transition-all duration-200">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
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
                <a href="{{ route('preview.target') }}" class="flex items-center px-6 py-3 hover:bg-kai-navy-light transition duration-200 {{ request()->routeIs('preview.target') ? 'bg-kai-navy-light border-l-4 border-kai-orange' : '' }}">
                    <i class="fas fa-bullseye w-5 text-kai-orange"></i>
                    <span class="sidebar-text ml-3">Capaian Target</span>
                </a>
                <a href="{{ route('statistik') }}" class="flex items-center px-6 py-3 hover:bg-kai-navy-light transition duration-200 {{ request()->routeIs('statistik') ? 'bg-kai-navy-light border-l-4 border-kai-orange' : '' }}">
                    <i class="fas fa-chart-bar w-5 text-kai-orange"></i>
                    <span class="sidebar-text ml-3">Statistik</span>
                </a>
            </nav>
            
        </div>

        <!-- Main Content -->
        <div id="mainContent" class="flex-1 flex flex-col overflow-hidden sidebar-transition ml-64">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">@yield('header', 'Dashboard')</h2>
                    <div class="flex items-center gap-3">
                        <span class="text-gray-600">Welcome, {{ Auth::user()->name }}</span>
                        <div class="w-10 h-10 bg-kai-orange rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>

                        <form action="{{ route('logout') }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" class="flex items-center justify-center px-3 h-10 bg-red-600 hover:bg-red-700 text-white rounded-lg transition duration-200">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="ml-2 hidden md:inline">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6 page-enter">
                @yield('content')
            </main>
        </div>
    </div>

    <div id="toastContainer" class="toast-container"></div>

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

            const revealEls = document.querySelectorAll('.reveal');
            if (revealEls && revealEls.length > 0 && 'IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('reveal-active');
                            io.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });

                revealEls.forEach((el) => io.observe(el));
            } else {
                revealEls.forEach((el) => el.classList.add('reveal-active'));
            }

            const toastContainer = document.getElementById('toastContainer');
            window.showToast = function (type, message) {
                if (!toastContainer) {
                    return;
                }

                const toast = document.createElement('div');
                toast.className = 'toast ' + (type ? ('toast-' + type) : '');

                const wrap = document.createElement('div');
                wrap.className = 'flex items-start gap-3';

                const icon = document.createElement('div');
                icon.className = 'mt-0.5';
                let iconHtml = '<i class="fas fa-check-circle text-green-600"></i>';
                if (type === 'warning') {
                    iconHtml = '<i class="fas fa-exclamation-triangle text-yellow-600"></i>';
                } else if (type === 'error') {
                    iconHtml = '<i class="fas fa-times-circle text-red-600"></i>';
                }
                icon.innerHTML = iconHtml;

                const text = document.createElement('div');
                text.className = 'text-sm text-gray-800';
                text.textContent = String(message || '');

                const close = document.createElement('button');
                close.type = 'button';
                close.className = 'ml-auto text-gray-400 hover:text-gray-600';
                close.innerHTML = '<i class="fas fa-times"></i>';
                close.addEventListener('click', function () {
                    toast.classList.remove('toast-show');
                    setTimeout(() => toast.remove(), 220);
                });

                wrap.appendChild(icon);
                wrap.appendChild(text);
                wrap.appendChild(close);
                toast.appendChild(wrap);
                toastContainer.appendChild(toast);

                requestAnimationFrame(() => toast.classList.add('toast-show'));

                setTimeout(() => {
                    toast.classList.remove('toast-show');
                    setTimeout(() => toast.remove(), 220);
                }, 4200);
            };
        });
    </script>
</body>
</html>
