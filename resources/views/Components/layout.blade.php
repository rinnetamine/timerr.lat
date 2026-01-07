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

<body class="min-h-screen text-gray-100">
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
    </div>
</body>
</html>
