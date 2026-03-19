<!doctype html>
<html lang="en" class="min-h-screen bg-fixed">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Timerr</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('clock.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
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

        html.light body {
            background: linear-gradient(135deg,
                rgba(255,255,255,1) 0%,
                rgba(250,250,250,1) 50%,
                rgba(255,255,255,1) 100%
            );
            color: #0f172a; 
        }

        html.light nav,
        html.light header,
        html.light .backdrop-blur-sm,
        html.light .backdrop-blur-md {
            background: rgba(255,255,255,0.85) !important;
            color: #0f172a !important;
            -webkit-backdrop-filter: blur(6px);
            backdrop-filter: blur(6px);
            border-color: #e6eef5 !important;
        }

        html.light .bg-gray-900,
        html.light .bg-gray-900\/60,
        html.light .bg-gray-800,
        html.light .bg-gray-800\/40,
        html.light .bg-gray-800\/80 {
            background-color: rgba(250,250,250,0.9) !important;
            color: #0f172a !important;
        }

        html.light .text-white,
        html.light .text-white\/90,
        html.light .text-gray-100 {
            color: #0f172a !important;
        }
        html.light .text-gray-300 {
            color: #475569 !important; 
        }

        html.light .border-gray-700,
        html.light .border-gray-800,
        html.light .border {
            border-color: #e6eef5 !important;
        }

        html.light .hover\:bg-gray-700:hover,
        html.light .hover\:bg-gray-800:hover {
            background-color: rgba(241,245,249,1) !important; 
        }

        html.light .text-neon-accent {
            color: #0da67a !important; 
        }
        html.light .text-neon-accent\/\* {
            color: #0da67a !important;
        }

        html.light .x-button,
        html.light .btn,
        html.light button {
            color: inherit !important;
            background-color: transparent !important;
        }

        html.light .text-gray-400 {
            color: #64748b !important; 
        }

        html.light .hover\:text-neon-accent:hover {
            color: #0da67a !important;
        }

    /* neon backgrounds for light theme */
        html.light .bg-neon-accent {
            background-color: rgba(13,166,122,0.14) !important;
            color: #0f172a !important;
        }

        html.light .bg-neon-accent.text-black {
            color: #0f172a !important;
        }

        html.light .border-neon-accent {
            border-color: #0da67a !important;
        }

        html.light .hover\:border-neon-accent:hover {
            border-color: #0da67a !important;
        }

    /* message panels light */
        html.light .bg-gray-800\/30,
        html.light .bg-gray-800\/40,
        html.light .bg-gray-900\/60 {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #e6eef5 !important;
        }

    /* lighten message bubble backgrounds */
        html.light .bg-gray-900 {
            background-color: #ffffff !important;
            color: #0f172a !important;
        }

    /* stronger neon bg for sent messages */
        html.light .bg-neon-accent.text-black {
            background-color: rgba(13,166,122,0.18) !important;
            color: #0f172a !important;
        }

    /* flash/alert colors for light theme */
        html.light .bg-green-700\/10,
        html.light .bg-red-700\/10,
        html.light .bg-yellow-500\/10 {
            background-color: rgba(255,255,255,0.92) !important;
            color: #0f172a !important;
            border-color: #e6eef5 !important;
        }

        html.light .text-green-200 {
            color: #065f46 !important; /* darker green for visibility */
        }

        html.light .text-red-200 {
            color: #7f1d1d !important; /* darker red */
        }

        html.light .text-yellow-200 {
            color: #92400e !important; /* darker amber */
        }

        /* additional background variants used across views */
        html.light .bg-green-900\/30,
        html.light .bg-green-900\/40,
        html.light .bg-red-900\/30,
        html.light .bg-red-900\/40,
        html.light .bg-green-500\/20,
        html.light .bg-red-500\/20 {
            background-color: rgba(255,255,255,0.96) !important;
            color: #0f172a !important;
            border-color: #e6eef5 !important;
        }

        /* additional text shades used for badges */
        html.light .text-green-300 {
            color: #065f46 !important;
        }

        html.light .text-red-300 {
            color: #7f1d1d !important;
        }

    /* tweak muted text color */
        html.light .text-gray-400 {
            color: #64748b !important;
        }

        html.light input,
        html.light textarea,
        html.light select {
            background-color: #fffefc !important;
            color: #0f172a !important;
            border-color: #e6eef5 !important;
        }

        html.light .bg-gray-800\/40,
        html.light .bg-gray-800\/50 {
            background-color: rgba(255,255,255,0.92) !important;
            border-color: #e6eef5 !important;
        }

        .hover-card {
            transition: transform 220ms cubic-bezier(.2,.8,.2,1), box-shadow 220ms, border-color 220ms;
            transform: translateY(0);
            will-change: transform, box-shadow;
        }
        .hover-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 14px 30px rgba(0,255,157,0.08); 
            border-color: rgba(0,255,157,0.28) !important;
        }

        html.light .hover-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(13,166,122,0.06);
            border-color: rgba(13,166,122,0.18) !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .hover-card,
            .hover-card:hover {
                transition: none !important;
                transform: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body class="min-h-screen text-gray-900">
    <script>
        // mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const button = document.querySelector('[aria-controls="mobile-menu"]');
            const menuIcon = button.querySelector('svg:first-child');
            const closeIcon = button.querySelector('svg:last-child');

            menu.classList.toggle('hidden');
            menuIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');

            const isExpanded = !menu.classList.contains('hidden');
            button.setAttribute('aria-expanded', isExpanded);
        }

        // theme handling
        function setTheme(theme) {
            const html = document.documentElement;
            if (theme === 'light') html.classList.add('light'); else html.classList.remove('light');
            try { localStorage.setItem('theme', theme); } catch (e) {}
            updateThemeButtonIcons(theme);
        }

        function initTheme() {
            let theme = 'dark';
            try { theme = localStorage.getItem('theme') || (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark'); } catch (e) {}
            setTheme(theme);
        }

        function toggleTheme() {
            const html = document.documentElement;
            const isLight = html.classList.contains('light');
            setTheme(isLight ? 'dark' : 'light');
        }

        function updateThemeButtonIcons(theme) {
            const btns = document.querySelectorAll('.theme-toggle');
            btns.forEach(btn => {
                const sun = btn.querySelector('.icon-sun');
                const moon = btn.querySelector('.icon-moon');
                if (!sun || !moon) return;
                if (theme === 'light') { sun.classList.remove('hidden'); moon.classList.add('hidden'); btn.setAttribute('aria-pressed', 'true'); }
                else { sun.classList.add('hidden'); moon.classList.remove('hidden'); btn.setAttribute('aria-pressed', 'false'); }
            });

            // toggle logos
            const darkLogos = document.querySelectorAll('.dark-logo');
            const lightLogos = document.querySelectorAll('.light-logo');
            if (theme === 'light') {
                darkLogos.forEach(n => n.classList.add('hidden'));
                lightLogos.forEach(n => n.classList.remove('hidden'));
            } else {
                darkLogos.forEach(n => n.classList.remove('hidden'));
                lightLogos.forEach(n => n.classList.add('hidden'));
            }
        }

        document.addEventListener('DOMContentLoaded', initTheme);
    </script>
    <div class="min-h-screen px-4 md:px-0">
        <nav class="sticky top-0 z-50 backdrop-blur-md bg-gray-900/60 border-b border-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <img class="h-8 w-8 dark-logo" src="{{ asset('clock.svg') }}" alt="Timerr logo - clock">
                            <img class="h-8 w-8 light-logo hidden" src="{{ asset('clock-light.svg') }}" alt="Timerr logo - clock light">
                        </div>
                        <div class="hidden md:block">
                            <div class="ml-10 flex items-baseline space-x-4">
                                <x-nav-link href="/" :active="request()->is('/')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Home</x-nav-link>
                                <x-nav-link href="/jobs" :active="request()->is('jobs')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Jobs</x-nav-link>
                                <x-nav-link href="/people" :active="request()->is('people*')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">People</x-nav-link>
                                <x-nav-link href="/contact" :active="request()->is('contact')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Contact</x-nav-link>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-4 flex items-center md:ml-6">
                            <!-- theme toggle button -->
                            <button type="button" onclick="toggleTheme()" class="theme-toggle ml-3 inline-flex items-center rounded-md p-2 text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-200" aria-pressed="false" title="toggle theme">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="icon-moon h-5 w-5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="icon-sun hidden h-5 w-5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36 6.36l-1.42-1.42M7.05 6.05 5.64 4.64m12.02 0-1.41 1.41M7.05 17.95l-1.41 1.41" />
                                    <circle cx="12" cy="12" r="3" stroke-width="1.5"></circle>
                                </svg>
                            </button>
                            @guest
                                <x-nav-link href="/login" :active="request()->is('login')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Log In</x-nav-link>
                                <x-nav-link href="/register" :active="request()->is('register')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Register</x-nav-link>
                            @endguest

                            @auth
                                <x-nav-link href="/messages" :active="request()->is('messages*')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">
                                    Messages
                                    @if(auth()->user()->unreadMessagesCount() > 0)
                                        <span class="ml-2 inline-block w-2 h-2 rounded-full bg-neon-accent" aria-hidden="true"></span>
                                    @endif
                                </x-nav-link>
                                <x-nav-link href="/submissions" :active="request()->is('submissions')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">
                                    Submissions
                                </x-nav-link>
                                @if(auth()->user()->isAdmin())
                                    <x-nav-link href="/admin" :active="request()->is('admin*')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">
                                        Admin
                                    </x-nav-link>
                                    <x-nav-link href="/disputes" :active="request()->is('disputes*')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">
                                        Disputes
                                    </x-nav-link>
                                @endif
                                <x-nav-link href="/profile" :active="request()->is('profile')" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">
                                    Profile
                                    <span class="ml-2 text-sm text-neon-accent">{{ auth()->user()->time_credits }} credits</span>
                                </x-nav-link>
                                <form method="POST" action="/logout" class="ml-3">
                                    @csrf
                                    <x-form-button class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300">Log Out</x-form-button>
                                </form>
                            @endauth
                        </div>
                    </div>
                    <div class="-mr-2 flex md:hidden">
                        <!-- mobile menu button -->
                        <button type="button" onclick="toggleMobileMenu()"
                                class="relative inline-flex items-center justify-center rounded-md bg-gray-800 p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                                aria-controls="mobile-menu" aria-expanded="false">
                            <span class="absolute -inset-0.5"></span>
                            <span class="sr-only">Open main menu</span>
                            <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                            </svg>
                            <svg class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- mobile menu, show/hide based on menu state -->
            <div class="md:hidden hidden" id="mobile-menu">
                <div class="space-y-1 px-2 pb-3 pt-2 sm:px-3">
                    <a href="/" class="bg-gray-900 text-white block rounded-md px-3 py-2 text-base font-medium"
                       aria-current="page">Home</a>
                    <!-- mobile theme toggle -->
                    <button type="button" onclick="toggleTheme()" class="theme-toggle w-full text-left inline-flex items-center rounded-md p-2 text-gray-300 hover:text-neon-accent hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium" aria-pressed="false" title="toggle theme">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="icon-moon h-5 w-5 mr-2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="icon-sun hidden h-5 w-5 mr-2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36 6.36l-1.42-1.42M7.05 6.05 5.64 4.64m12.02 0-1.41 1.41M7.05 17.95l-1.41 1.41" />
                            <circle cx="12" cy="12" r="3" stroke-width="1.5"></circle>
                        </svg>
                        toggle theme
                    </button>
                    <a href="/jobs"
                       class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Jobs</a>
                          <a href="/people"
                              class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">People</a>
                    <a href="/contact"
                       class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Contact</a>
                    @guest
                        <a href="/login" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Log In</a>
                        <a href="/register" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Register</a>
                    @endguest
                    @auth
                        <a href="/messages" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Messages @if(auth()->user()->unreadMessagesCount() > 0)<span class="ml-2 inline-block w-2 h-2 rounded-full bg-neon-accent" aria-hidden="true"></span>@endif</a>
                        <a href="/submissions" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Submissions</a>
                        @if(auth()->user()->isAdmin())
                            <a href="/admin" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Admin</a>
                            <a href="/disputes" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Disputes</a>
                        @endif
                        <a href="/profile" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Profile</a>
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="w-full text-left text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Log Out</button>
                        </form>
                    @endauth
                </div>  
            </div>
        </nav>

        <header class="backdrop-blur-md bg-gray-800/40 border-b border-gray-700">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 sm:flex sm:justify-between">
                <h1 class="text-3xl font-bold tracking-tight text-white/90">{{ $heading }}</h1>

                @if(request()->is('jobs'))
                    <x-button href="/jobs/create" class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300">Create Job</x-button>
                @endif
            </div>
        </header>

        <main>
            <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
                <!-- flash messages -->
                @if(session('success'))
                    <div class="mb-4 rounded-md p-3 bg-green-700/10 border border-green-700 text-green-200">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 rounded-md p-3 bg-red-700/10 border border-red-700 text-red-200">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 rounded-md p-3 bg-yellow-500/10 border border-yellow-500 text-yellow-200">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>

        <!-- Footer -->
        <footer class="mt-auto border-t border-gray-800 bg-gray-900/80 backdrop-blur-md">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Platform Info -->
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center space-x-3 mb-4">
                            <img class="h-8 w-8 dark-logo" src="{{ asset('clock.svg') }}" alt="Timerr logo">
                            <img class="h-8 w-8 light-logo hidden" src="{{ asset('clock-light.svg') }}" alt="Timerr logo light">
                            <h3 class="text-xl font-bold text-white">Timerr</h3>
                        </div>
                        <p class="text-gray-300 mb-4 max-w-md">
                            A time-banking platform where community members exchange services using time credits. 
                            Share your skills, help others, and build a stronger community together.
                        </p>
                        <div class="flex space-x-4">
                            <a href="https://github.com/rinnetamine/timerr.lat" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-neon-accent transition-colors duration-200" aria-label="GitHub Repository">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h4 class="text-white font-semibold mb-4">Quick Links</h4>
                        <ul class="space-y-2">
                            <li><a href="/jobs" class="text-gray-300 hover:text-neon-accent transition-colors duration-200">Browse Jobs</a></li>
                            <li><a href="/people" class="text-gray-300 hover:text-neon-accent transition-colors duration-200">Find People</a></li>
                            <li><a href="/contact" class="text-gray-300 hover:text-neon-accent transition-colors duration-200">Contact Us</a></li>
                            @guest
                                <li><a href="/register" class="text-gray-300 hover:text-neon-accent transition-colors duration-200">Sign Up</a></li>
                            @endguest
                        </ul>
                    </div>

                    <!-- Resources -->
                    <div>
                        <h4 class="text-white font-semibold mb-4">Resources</h4>
                        <ul class="space-y-2">
                            <li><button onclick="openModal('howItWorks')" class="text-gray-300 hover:text-neon-accent transition-colors duration-200 text-left w-full">How It Works</button></li>
                            <li><button onclick="openModal('faq')" class="text-gray-300 hover:text-neon-accent transition-colors duration-200 text-left w-full">FAQ</button></li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom Bar -->
                <div class="mt-8 pt-8 border-t border-gray-800">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <p class="text-gray-400 text-sm mb-4 md:mb-0">
                            © {{ date('Y') }} Timerr. Building communities through time banking.
                        </p>
                        <div class="flex items-center space-x-6 text-sm text-gray-400">
                            <span>Platform Stats:</span>
                            <span class="text-neon-accent">{{ App\Models\User::count() }} Users</span>
                            <span class="text-neon-accent">{{ App\Models\Job::count() }} Jobs</span>
                            <span class="text-neon-accent">{{ App\Models\JobSubmission::where('status', 'approved')->count() }} Completed</span>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Modals -->
    <div id="howItWorks" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal('howItWorks')"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-gray-900 border border-gray-700 rounded-lg max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                <div class="sticky top-0 bg-gray-900 border-b border-gray-700 p-6 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white">How Timerr Works</h3>
                    <button onclick="closeModal('howItWorks')" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-8 h-8 bg-neon-accent text-black rounded-full flex items-center justify-center font-bold">1</div>
                        <div>
                            <h4 class="text-white font-semibold mb-2">Sign Up & Get Credits</h4>
                            <p class="text-gray-300">Create your account and receive 10 free time credits to start. Everyone begins with the same amount to ensure fair exchange.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-8 h-8 bg-neon-accent text-black rounded-full flex items-center justify-center font-bold">2</div>
                        <div>
                            <h4 class="text-white font-semibold mb-2">Browse or Create Jobs</h4>
                            <p class="text-gray-300">Look for jobs that match your skills or post your own needs. Each job has a time credit value based on the work required.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-8 h-8 bg-neon-accent text-black rounded-full flex items-center justify-center font-bold">3</div>
                        <div>
                            <h4 class="text-white font-semibold mb-2">Complete the Work</h4>
                            <p class="text-gray-300">Claim a job, complete the work, and submit proof. The job poster will review your submission and approve it if satisfied.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-8 h-8 bg-neon-accent text-black rounded-full flex items-center justify-center font-bold">4</div>
                        <div>
                            <h4 class="text-white font-semibold mb-2">Exchange Credits</h4>
                            <p class="text-gray-300">Once approved, you receive the time credits. Use them to get help from others or save them for future needs.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-8 h-8 bg-neon-accent text-black rounded-full flex items-center justify-center font-bold">5</div>
                        <div>
                            <h4 class="text-white font-semibold mb-2">Build Reputation</h4>
                            <p class="text-gray-300">Leave reviews for completed jobs. Build trust in the community through positive ratings and successful exchanges.</p>
                        </div>
                    </div>
                    <div class="mt-6 p-4 bg-neon-accent/10 border border-neon-accent/30 rounded-lg">
                        <p class="text-neon-accent text-sm"><strong>Remember:</strong> 1 time credit = 1 hour of service. Everyone's time is valued equally!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="faq" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal('faq')"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-gray-900 border border-gray-700 rounded-lg max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                <div class="sticky top-0 bg-gray-900 border-b border-gray-700 p-6 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white">Frequently Asked Questions</h3>
                    <button onclick="closeModal('faq')" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="border-b border-gray-700 pb-4">
                        <h4 class="text-white font-semibold mb-2">What is time banking?</h4>
                        <p class="text-gray-300">Time banking is a system where people exchange services using time as currency. 1 hour of your time equals 1 time credit, regardless of the service provided.</p>
                    </div>
                    <div class="border-b border-gray-700 pb-4">
                        <h4 class="text-white font-semibold mb-2">How do I earn time credits?</h4>
                        <p class="text-gray-300">You earn credits by completing jobs for other users. When someone posts a job and you complete it successfully, you receive the time credits offered for that job.</p>
                    </div>
                    <div class="border-b border-gray-700 pb-4">
                        <h4 class="text-white font-semibold mb-2">What if someone doesn't complete the job?</h4>
                        <p class="text-gray-300">If a job isn't completed satisfactorily, the job poster can decline the submission. The credits remain with the poster and they can re-list the job.</p>
                    </div>
                    <div class="border-b border-gray-700 pb-4">
                        <h4 class="text-white font-semibold mb-2">Can I set my own rates?</h4>
                        <p class="text-gray-300">Rates are based on time, not money. 1 time credit always equals 1 hour of work. However, you can set how many credits a job is worth based on estimated completion time.</p>
                    </div>
                    <div class="border-b border-gray-700 pb-4">
                        <h4 class="text-white font-semibold mb-2">Is my personal information safe?</h4>
                        <p class="text-gray-300">Yes. We use encryption for messages and only share necessary information. Your contact details are only shared with users you're directly working with.</p>
                    </div>
                    <div class="border-b border-gray-700 pb-4">
                        <h4 class="text-white font-semibold mb-2">What happens if I run out of credits?</h4>
                        <p class="text-gray-300">You can earn more credits by completing jobs for others. Everyone starts with 10 credits, so there are always opportunities to earn more.</p>
                    </div>
                    <div class="pb-4">
                        <h4 class="text-white font-semibold mb-2">How do disputes work?</h4>
                        <p class="text-gray-300">If there's a disagreement about job completion, either party can contact an admin for mediation. Admins review the evidence and make fair decisions.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                ['howItWorks', 'faq'].forEach(id => {
                    if (!document.getElementById(id).classList.contains('hidden')) {
                        closeModal(id);
                    }
                });
            }
        });
    </script>
</body>
</html>
