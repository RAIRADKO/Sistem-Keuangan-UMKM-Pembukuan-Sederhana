<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'UKM Keuangan') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
    <body class="antialiased">
        <div class="min-h-screen flex">
            <!-- Left Side - Branding & Illustration -->
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 relative overflow-hidden">
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
                <div class="absolute top-20 left-20 w-20 h-20 bg-white/10 rounded-2xl backdrop-blur-sm animate-pulse"></div>
                <div class="absolute bottom-40 left-40 w-32 h-32 bg-white/10 rounded-full backdrop-blur-sm"></div>
                <div class="absolute top-1/3 right-20 w-16 h-16 bg-white/10 rounded-xl backdrop-blur-sm"></div>

                <!-- Content -->
                <div class="relative z-10 flex flex-col justify-center px-16">
                    <!-- Logo -->
                    <div class="flex items-center gap-3 mb-12">
                        <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center text-2xl">
                            💰
                        </div>
                        <span class="text-2xl font-bold text-white">UKM Keuangan</span>
                    </div>

                    <!-- Tagline -->
                    <h1 class="text-4xl lg:text-5xl font-bold text-white leading-tight mb-6">
                        Kelola Keuangan<br>Usaha Anda dengan<br>
                        <span class="text-indigo-200">Mudah & Cepat</span>
                    </h1>

                    <p class="text-indigo-200 text-lg max-w-md mb-12">
                        Catat pemasukan, pengeluaran, dan pantau kesehatan keuangan bisnis Anda dalam satu aplikasi.
                    </p>

                    <!-- Features -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 text-white/90">
                            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span>Multi-toko dalam satu akun</span>
                        </div>
                        <div class="flex items-center gap-3 text-white/90">
                            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span>Laporan keuangan otomatis</span>
                        </div>
                        <div class="flex items-center gap-3 text-white/90">
                            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span>Export PDF & Excel</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gray-50 dark:bg-gray-900">
                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
