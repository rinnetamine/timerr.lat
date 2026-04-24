<x-layout>
    <x-slot:heading>Laipni lūdzam Timerr</x-slot:heading>

    <div class="space-y-8">
        <!-- Hero Section -->
        <div class="relative overflow-hidden rounded-2xl p-8" aria-hidden="false">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/40 via-sky-800/20 to-transparent pointer-events-none blur-lg"></div>
            <div class="relative text-center space-y-4">
                <svg class="absolute right-6 top-4 w-48 h-48 opacity-10 transform rotate-12" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="48" stroke="url(#g)" stroke-width="2" />
                    <defs>
                        <linearGradient id="g" x1="0" x2="1">
                            <stop offset="0" stop-color="#7c3aed" stop-opacity="0.7" />
                            <stop offset="1" stop-color="#06b6d4" stop-opacity="0.2" />
                        </linearGradient>
                    </defs>
                </svg>

                <h2 class="text-5xl md:text-6xl font-extrabold text-white/95 leading-tight">Maini prasmes pret laiku</h2>
                <p class="text-lg md:text-xl text-gray-300 max-w-2xl mx-auto">Dalies ar savu pieredzi, nopelnī laika kredītus un saņem vajadzīgos pakalpojumus — veidots ap cilvēkiem un uzticību.</p>

                <form action="/jobs" method="GET" class="mt-6 max-w-3xl mx-auto flex items-center gap-3 bg-gray-900/30 border border-gray-800 rounded-lg p-1 shadow-lg">
                    <label for="search" class="sr-only">Meklēt</label>
                    <div class="flex items-center gap-2 flex-1 px-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"></path></svg>
                        <input id="search" name="search" type="search" placeholder="Meklē pakalpojumus, prasmes vai cilvēkus" class="w-full bg-transparent px-2 py-3 rounded-md text-gray-100 placeholder-gray-400 focus:outline-none" />
                    </div>

                    <select name="category" class="hidden sm:block px-3 py-2 rounded-md bg-gray-900/25 border-l border-gray-800 text-gray-100">
                        <option value="">Visas kategorijas</option>
                        @foreach(($categories ?? []) as $key => $group)
                            <option value="{{ $key }}">{{ $group['label'] }}</option>
                            @if(!empty($group['children']) && is_array($group['children']))
                                @foreach($group['children'] as $slug => $label)
                                    <option value="{{ $slug }}">&nbsp;&nbsp;{{ $label }}</option>
                                @endforeach
                            @endif
                        @endforeach
                    </select>

                    <button class="ml-1 mr-1 px-4 py-2 rounded-md bg-neon-accent text-black font-semibold">Meklēt</button>
                </form>
            </div>
        </div>

        <!-- How It Works -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 hover:border-neon-accent/50 transition-all duration-300 hover-card">
                <div class="text-neon-accent text-2xl mb-3">1</div>
                <h3 class="text-xl font-semibold text-white/90 mb-2">Piedāvā pakalpojumus</h3>
                <p class="text-gray-300">Izveido sarakstus ar savām prasmēm un talantām</p>
            </div>
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 hover:border-neon-accent/50 transition-all duration-300 hover-card">
                <div class="text-neon-accent text-2xl mb-3">2</div>
                <h3 class="text-xl font-semibold text-white/90 mb-2">Savienojies</h3>
                <p class="text-gray-300">Apspried prasības ar interesentiem lietotājiem</p>
            </div>
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 hover:border-neon-accent/50 transition-all duration-300 hover-card">
                <div class="text-neon-accent text-2xl mb-3">3</div>
                <h3 class="text-xl font-semibold text-white/90 mb-2">Saņem apstiprinājumu</h3>
                <p class="text-gray-300">Pabeidz pakalpojumus un saņem verifikāciju</p>
            </div>
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 hover:border-neon-accent/50 transition-all duration-300 hover-card">
                <div class="text-neon-accent text-2xl mb-3">4</div>
                <h3 class="text-xl font-semibold text-white/90 mb-2">Maini laiku</h3>
                <p class="text-gray-300">Izmanto nopelnītos kredītus citiem pakalpojumiem</p>
            </div>
        </div>

        <!-- Popular Categories -->
        <div class="mt-8">
            <h3 class="text-2xl font-semibold text-white/90 mb-4">Populārās kategorijas</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach(($categories ?? []) as $key => $group)
                    <a href="/jobs?category={{ urlencode($key) }}" class="block bg-gray-800/40 p-4 rounded-lg border border-gray-700 hover:border-neon-accent transition-all duration-200">
                        <div class="font-semibold text-white/90">{{ $group['label'] }}</div>
                        @if(!empty($group['children']))
                            <div class="text-gray-400 text-sm mt-1">{{ implode(', ', array_values($group['children'])) }}</div>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Featured Jobs -->
<div class="mt-8">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-2xl font-semibold text-white/90">Izceltie pakalpojumi</h3>
        <a href="/jobs" class="text-neon-accent">Skatīt visus</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @if(isset($featuredJobs) && $featuredJobs->count() > 0)
            @foreach($featuredJobs as $job)

                <div 
                    onclick="window.location='/jobs/{{ $job->id }}'" 
                    class="cursor-pointer h-full flex flex-col justify-between p-4 bg-gray-800/40 rounded-lg border border-gray-700 hover:border-neon-accent transition-all duration-300 hover-card"
                >

                    <!-- User -->
                    <div class="text-sm text-neon-accent font-semibold">
                        <a 
                            href="{{ route('people.show', $job->user->id) }}" 
                            onclick="event.stopPropagation()" 
                            class="hover:text-neon-accent/80 transition-colors duration-200"
                        >
                            {{ $job->user->first_name }} {{ $job->user->last_name }}
                        </a>
                    </div>

                    <!-- Title -->
                    <div class="mt-2 font-semibold text-white/90">
                        {{ Str::limit($job->title, 60) }}
                    </div>

                    <!-- Description -->
                    <div class="mt-2 text-gray-400 text-sm">
                        {{ Str::limit($job->description, 80) }}
                    </div>

                    <!-- Bottom row -->
                    <div class="mt-3 flex items-center justify-between">
                        <span class="bg-neon-accent/20 text-neon-accent px-3 py-1 rounded-full text-xs font-semibold">
                            {{ $job->time_credits }} kredīti
                        </span>
                        <span class="text-gray-400 text-xs">
                            {{ $job->category }}
                        </span>
                    </div>

                </div>

            @endforeach
        @else
            <div class="col-span-full text-center text-gray-400 py-8">
                <p>Pašlaik nav izcelto pakalpojumu</p>
            </div>
        @endif
    </div>
</div>
        <!-- Call to Action -->
        <div class="text-center mt-12 space-y-4">
            <x-button href="/jobs/create" class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300">
                Piedāvā savus pakalpojumus
            </x-button>
            <p class="text-gray-300">vai</p>
            <x-button href="/jobs" class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300">
                Pārlūkot pakalpojumus
            </x-button>
        </div>
    </div>
</x-layout>
