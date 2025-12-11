<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'UKM Keuangan') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
    <body class="antialiased">
        <div class="min-h-screen flex">
            <!-- Left Side - Branding & Illustration -->
            <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden" style="background: linear-gradient(135deg, #0891B2 0%, #0E7490 50%, #164E63 100%);">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <defs>
                            <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                                <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid)"/>
                    </svg>
                </div>

                <!-- Floating Elements -->
                <div class="absolute top-20 left-20 w-24 h-24 bg-white/10 rounded-3xl backdrop-blur-sm animate-pulse"></div>
                <div class="absolute bottom-40 left-40 w-36 h-36 bg-white/10 rounded-full backdrop-blur-sm" style="animation: float 6s ease-in-out infinite;"></div>
                <div class="absolute top-1/3 right-20 w-20 h-20 bg-white/10 rounded-2xl backdrop-blur-sm" style="animation: float 8s ease-in-out infinite reverse;"></div>
                <div class="absolute bottom-20 right-32 w-16 h-16 bg-cyan-300/20 rounded-xl backdrop-blur-sm animate-pulse"></div>

                <!-- Content -->
                <div class="relative z-10 flex flex-col justify-center px-16">
                    <!-- Logo -->
                    <div class="flex items-center gap-4 mb-14">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-14 h-14 rounded-2xl shadow-xl object-cover">
                        <span class="text-2xl font-bold text-white">UKM Keuangan</span>
                    </div>

                    <!-- Tagline -->
                    <h1 class="text-4xl lg:text-5xl font-extrabold text-white leading-tight mb-8">
                        Kelola Keuangan<br>Usaha Anda dengan<br>
                        <span class="text-cyan-200">Mudah & Cepat</span>
                    </h1>

                    <p class="text-cyan-100 text-lg max-w-md mb-14 leading-relaxed">
                        Catat pemasukan, pengeluaran, dan pantau kesehatan keuangan bisnis Anda dalam satu aplikasi.
                    </p>

                    <!-- Features -->
                    <div class="space-y-5">
                        <div class="flex items-center gap-4 text-white/90">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="font-medium">Multi-toko dalam satu akun</span>
                        </div>
                        <div class="flex items-center gap-4 text-white/90">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="font-medium">Laporan keuangan otomatis</span>
                        </div>
                        <div class="flex items-center gap-4 text-white/90">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="font-medium">Export PDF & Excel</span>
                        </div>
                    </div>
                </div>

                <style>
                    @keyframes float {
                        0%, 100% { transform: translateY(0) translateX(0); }
                        25% { transform: translateY(-20px) translateX(10px); }
                        50% { transform: translateY(-10px) translateX(-10px); }
                        75% { transform: translateY(-15px) translateX(5px); }
                    }
                </style>
            </div>

            <!-- Right Side - Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gradient-to-br from-slate-50 via-cyan-50/30 to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
