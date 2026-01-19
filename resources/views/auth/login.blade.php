<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UPT Terminal Babat Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --kai-orange: #FF6B35;
            --kai-orange-dark: #E55A2B;
            --kai-orange-light: #FF8C5A;
            --kai-navy: #1E3A5F;
            --kai-navy-dark: #152844;
            --kai-navy-light: #2C4E7C;
        }
        
        body {
            background-image:
                linear-gradient(135deg, rgba(0, 0, 0, 0.55) 0%, rgba(0, 0, 0, 0.60) 100%),
                url("{{ asset('images/bg-login.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        
        .kai-orange-gradient {
            background: linear-gradient(135deg, var(--kai-orange) 0%, var(--kai-orange-dark) 100%);
        }
        
        .kai-navy-gradient {
            background: linear-gradient(135deg, var(--kai-navy) 0%, var(--kai-navy-dark) 100%);
        }
        
        .focus-kai:focus {
            ring: 2px;
            ring-color: var(--kai-orange);
            border-color: transparent;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-20 h-20 kai-orange-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-train text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">UPT Terminal Babat Dashboard</h1>
            <p class="text-gray-600">Silakan login untuk melanjutkan</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ url('/login') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent transition duration-200 mt-1"
                        placeholder="Masukkan username"
                    >
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-kai-orange focus:border-transparent transition duration-200 mt-1"
                        placeholder="Masukkan password"
                    >
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700" aria-label="Lihat password">
                        <i id="togglePasswordIcon" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button 
                type="submit" 
                class="w-full kai-orange-gradient text-white font-semibold py-3 rounded-lg hover:opacity-90 transition duration-200 transform hover:scale-[1.02] shadow-lg"
            >
                <i class="fas fa-sign-in-alt mr-2"></i>
                Login
            </button>
        </form>

        <div class="text-center mt-6 text-sm text-gray-600">
            <p> 2026 UPT Terminal Babat Dashboard</p>
            <p class="mt-1">Kereta Api Indonesia</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('password');
            const btn = document.getElementById('togglePassword');
            const icon = document.getElementById('togglePasswordIcon');

            if (!input || !btn || !icon) return;

            btn.addEventListener('click', function () {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                icon.classList.toggle('fa-eye', !isPassword);
                icon.classList.toggle('fa-eye-slash', isPassword);
            });
        });
    </script>
</body>
</html>