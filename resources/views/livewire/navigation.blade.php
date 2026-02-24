<!-- Navigation Component -->
<nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="/" class="flex items-center">
                    <span class="text-2xl font-bold bg-gradient-to-r from-red-600 to-black bg-clip-text text-transparent">
                        KHANZA
                    </span>
                    <span class="text-2xl font-bold text-black ml-2">Repaint</span>
                </a>
            </div>

            <!-- Desktop Navigation Menu -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="/" class="text-gray-700 hover:text-red-600 transition duration-200 font-medium">Home</a>
                <a href="/service" class="text-gray-700 hover:text-red-600 transition duration-200 font-medium">Service</a>

                @if ($isAuthenticated)
                    <a href="/booking" class="text-gray-700 hover:text-red-600 transition duration-200 font-medium">Booking</a>
                    <a href="/garage" class="text-gray-700 hover:text-red-600 transition duration-200 font-medium">Garage</a>
                @else
                    <a href="/booking" class="text-gray-400 cursor-not-allowed font-medium">Booking</a>
                    <a href="/garage" class="text-gray-700 hover:text-red-600 transition duration-200 font-medium">Garage</a>
                @endif

                <a href="/testimoni" class="text-gray-700 hover:text-red-600 transition duration-200 font-medium">Testimoni</a>
                <a href="/faq" class="text-gray-700 hover:text-red-600 transition duration-200 font-medium">FAQ</a>

                @if ($isAuthenticated)
                    <a href="/claim-voucher" class="text-gray-700 hover:text-red-600 transition duration-200 font-medium">
                        Klaim Voucher
                        @if ($membershipTier !== 'none')
                            <span class="ml-1 inline-block px-2 py-1 text-xs font-semibold rounded-full
                                @if ($membershipTier === 'gold') bg-yellow-100 text-yellow-800
                                @elseif ($membershipTier === 'silver') bg-gray-100 text-gray-800
                                @else bg-orange-100 text-orange-800
                                @endif
                            ">
                                {{ strtoupper($membershipTier) }}
                            </span>
                        @endif
                    </a>
                @else
                    <a href="/klaim-voucher" class="text-gray-400 cursor-not-allowed font-medium">Klaim Voucher</a>
                @endif
            </div>

            <!-- Authentication Buttons / User Menu -->
            <div class="hidden md:flex items-center space-x-4">
                @if ($isAuthenticated)
                    <!-- User Menu Dropdown -->
                    <div class="relative group">
                        <button class="flex items-center space-x-2 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                            <span class="w-6 h-6 bg-white rounded-full flex items-center justify-center">
                                <span class="text-red-600 font-bold text-sm">
                                    {{ substr($user->name, 0, 1) }}
                                </span>
                            </span>
                            <span class="font-medium">{{ $user->name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 w-48 mt-0 bg-white rounded-lg shadow-xl opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none group-hover:pointer-events-auto">
                            <a href="/profile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-t-lg">
                                Profile
                            </a>
                            <a href="/my-bookings" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                My Bookings
                            </a>
                            @if ($user->role === 'garage_owner')
                                <a href="/garage/dashboard" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Garage Dashboard
                                </a>
                            @endif
                            @if ($user->role === 'admin')
                                <a href="/admin/dashboard" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Admin Dashboard
                                </a>
                            @endif
                            <button wire:click="logout" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-b-lg border-t">
                                Logout
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Guest Buttons -->
                    <a href="/login" class="text-gray-700 hover:text-red-600 font-medium transition">Login</a>
                    <a href="/register" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition font-medium">
                        Sign Up
                    </a>
                @endif
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button
                    wire:click="toggleMobileMenu"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:bg-red-50 focus:outline-none"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        @if ($mobileMenuOpen)
            <div class="md:hidden pb-4 border-t">
                <a href="/" class="block px-3 py-2 text-gray-700 hover:bg-red-50">Home</a>
                <a href="/service" class="block px-3 py-2 text-gray-700 hover:bg-red-50">Service</a>

                @if ($isAuthenticated)
                    <a href="/booking" class="block px-3 py-2 text-gray-700 hover:bg-red-50">Booking</a>
                @else
                    <span class="block px-3 py-2 text-gray-400 cursor-not-allowed">Booking (Login required)</span>
                @endif

                <a href="/garage" class="block px-3 py-2 text-gray-700 hover:bg-red-50">Garage</a>
                <a href="/testimoni" class="block px-3 py-2 text-gray-700 hover:bg-red-50">Testimoni</a>
                <a href="/faq" class="block px-3 py-2 text-gray-700 hover:bg-red-50">FAQ</a>

                @if ($isAuthenticated)
                    <a href="/claim-voucher" class="block px-3 py-2 text-gray-700 hover:bg-red-50">Klaim Voucher</a>
                    <div class="px-3 py-2 border-t">
                        <a href="/profile" class="block py-2 text-gray-700 hover:text-red-600">Profile</a>
                        <a href="/my-bookings" class="block py-2 text-gray-700 hover:text-red-600">My Bookings</a>
                        <button wire:click="logout" class="w-full text-left py-2 text-gray-700 hover:text-red-600">
                            Logout
                        </button>
                    </div>
                @else
                    <div class="px-3 py-4 space-y-2 border-t">
                        <a href="/login" class="block w-full text-center py-2 text-gray-700 border-2 border-gray-300 rounded-lg hover:bg-gray-50">
                            Login
                        </a>
                        <a href="/register" class="block w-full text-center py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Sign Up
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</nav>
