<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="@yield('description', 'Professional Automotive Repaint & General Services Platform')">

        <title>@yield('title', 'Khanza Repaint - Automotive Services')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <!-- Navigation -->
        <livewire:navigation />

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-black text-white mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid md:grid-cols-4 gap-8 mb-8">
                    <!-- Brand -->
                    <div>
                        <h3 class="text-xl font-bold mb-4">
                            <span class="text-red-600">KHANZA</span> Repaint
                        </h3>
                        <p class="text-gray-400 text-sm">
                            Professional automotive repaint and general services with trusted quality.
                        </p>
                    </div>

                    <!-- Services -->
                    <div>
                        <h4 class="font-semibold mb-4">Services</h4>
                        <ul class="space-y-2 text-gray-400 text-sm">
                            <li><a href="/service" class="hover:text-red-600">Repaint Services</a></li>
                            <li><a href="/service" class="hover:text-red-600">General Services</a></li>
                            <li><a href="/booking" class="hover:text-red-600">Book Appointment</a></li>
                        </ul>
                    </div>

                    <!-- Community -->
                    <div>
                        <h4 class="font-semibold mb-4">Community</h4>
                        <ul class="space-y-2 text-gray-400 text-sm">
                            <li><a href="/garage" class="hover:text-red-600">Garage Marketplace</a></li>
                            <li><a href="/testimoni" class="hover:text-red-600">Testimonials</a></li>
                            <li><a href="/faq" class="hover:text-red-600">FAQ</a></li>
                        </ul>
                    </div>

                    <!-- Support -->
                    <div>
                        <h4 class="font-semibold mb-4">Support</h4>
                        <ul class="space-y-2 text-gray-400 text-sm">
                            <li><a href="mailto:info@khanzarepaint.com" class="hover:text-red-600">info@khanzarepaint.com</a></li>
                            <li><a href="tel:+6281234567890" class="hover:text-red-600">+62 812 3456 7890</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-800 pt-8">
                    <div class="flex flex-col md:flex-row justify-between items-center text-gray-400 text-sm">
                        <p>&copy; 2024 Khanza Repaint. All rights reserved.</p>
                        <div class="flex space-x-6 mt-4 md:mt-0">
                            <a href="#" class="hover:text-red-600">Privacy Policy</a>
                            <a href="#" class="hover:text-red-600">Terms of Service</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        @livewireScripts
    </body>
</html>
