<!doctype html>
<html lang="en" class="min-h-screen bg-fixed">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>My Website</title>
    <link rel="icon" type="image/x-icon" href="https://raw.githubusercontent.com/rinnetamine/Timerr/refs/heads/main/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        neon: {
                            accent: '#00ff9d',
                            glow: '#00ff9d40'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: linear-gradient(135deg, 
                rgba(17, 24, 39, 1) 0%,
                rgba(31, 41, 55, 1) 50%,
                rgba(17, 24, 39, 1) 100%
            );
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>

<body class="min-h-screen text-gray-100" x-data="{ mobileMenuOpen: false }">
    <div class="min-h-screen px-4 sm:px-6 lg:px-8">
        <nav class="sticky top-0 z-50 backdrop-blur-md bg-gray-900/60 border-b border-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between" @click.away="mobileMenuOpen = false" @keydown.escape="mobileMenuOpen = false">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <img class="h-8 w-8" src="https://raw.githubusercontent.com/rinnetamine/Timerr/refs/heads/main/favicon.ico">
                        </div>
                        <div class="hidden md:block">
                            <div class="ml-10 flex items-baseline space-x-4">
                                <x-nav-link href="/" :active="request()->is('/')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Home</x-nav-link>
                                <x-nav-link href="/jobs" :active="request()->is('jobs')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Jobs</x-nav-link>
                                <x-nav-link href="/contact" :active="request()->is('contact')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Contact</x-nav-link>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-4 flex items-center md:ml-6">
                            @guest
                                <x-nav-link href="/login" :active="request()->is('login')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Log In</x-nav-link>
                                <x-nav-link href="/register" :active="request()->is('register')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Register</x-nav-link>
                            @endguest

                            @auth
                                <x-nav-link href="/submissions" :active="request()->is('submissions')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">
                                    Submissions
                                </x-nav-link>
                                <a href="/profile" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">
                                    Profile
                                    <span class="ml-2 text-sm text-neon-accent">{{ auth()->user()->time_credits }} credits</span>
                                </a>
                                <form method="POST" action="/logout" class="w-full">
                                    @csrf
                                    <x-form-button class="w-full text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300">Log Out</x-form-button>
                                </form>
                            @endauth
                        </div>
                    </div>
                    <div class="-mr-2 flex md:hidden">
                        <!-- Mobile menu button -->
                        <button type="button"
                                class="relative inline-flex items-center justify-center rounded-md bg-gray-800 p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                                aria-controls="mobile-menu" 
                                :aria-expanded="mobileMenuOpen"
                                @click="mobileMenuOpen = !mobileMenuOpen">
                            <span class="absolute -inset-0.5"></span>
                            <span class="sr-only">Open main menu</span>
                            <!-- Menu open: "hidden", Menu closed: "block" -->
                            <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                            </svg>
                            <!-- Menu open: "block", Menu closed: "hidden" -->
                            <svg class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu, show/hide based on menu state. -->
            <div class="md:hidden" id="mobile-menu" x-show="mobileMenuOpen" x-transition.duration.300ms
                x-cloak
                class="fixed inset-0 z-50 bg-black bg-opacity-50"
                x-on:click="mobileMenuOpen = false">
                <div class="space-y-1 px-2 pb-3 pt-2 sm:px-3"
                    x-on:click.stop
                    class="fixed inset-y-0 right-0 z-50 w-full max-w-md transform translate-x-0 bg-gray-800/95 p-6 transition-transform duration-300 ease-in-out
                    md:relative md:static md:inset-0 md:max-w-none md:translate-x-0 md:p-0">
                    <!-- Menu items -->
                    <div class="space-y-1">
                    <a href="/" class="bg-gray-900 text-white block rounded-md px-3 py-2 text-base font-medium"
                       aria-current="page">Home</a>
                    <a href="/jobs"
                       class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Jobs</a>
                    <a href="/contact"
                       class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Contact</a>
                    @guest
                        <a href="/login"
                           class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Log In</a>
                        <a href="/register"
                           class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Register</a>
                    @endguest
                    @auth
                        <a href="/submissions"
                           class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Submissions</a>
                        <a href="/profile"
                           class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Profile</a>
                        <form method="POST" action="/logout" class="w-full">
                            @csrf
                            <button type="submit" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium w-full text-left">
                                Log Out
                            </button>
                        </form>
                    @endauth
                </div>  
            </div>
        </nav>

        <header class="backdrop-blur-md bg-gray-800/40 border-b border-gray-700">
            <div class="mx-auto max-w-7xl py-6 sm:py-8">
                <h1 class="text-3xl font-bold tracking-tight text-white/90">{{ $heading }}</h1>

                @if(request()->is('jobs'))
                    <x-button href="/jobs/create" class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300">Create Job</x-button>
                @endif
            </div>
        </header>

        <main>
            <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
