<x-layout>
    <x-slot:heading>Laipni lūdzam Timerr</x-slot:heading>

    @php
        $stats = $homeStats ?? [];
        $insights = $homeInsights ?? [];
        $primaryStat = $stats[0] ?? ['value' => 0, 'label' => 'dalībnieki'];
        $secondaryStat = $stats[1] ?? ['value' => 0, 'label' => 'pakalpojumi'];
    @endphp

    <div class="space-y-10">
        <!-- Hero Section -->
        <section class="overflow-hidden rounded-lg border border-gray-700 bg-gray-900/50">
            <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="p-6 sm:p-8 lg:p-10">
                    <div class="inline-flex items-center gap-2 rounded-md border border-neon-accent/30 bg-neon-accent/10 px-3 py-2 text-sm font-semibold text-neon-accent">
                        <span class="h-2 w-2 rounded-full bg-neon-accent"></span>
                        Prasmes kļūst par laiku
                    </div>

                    <div class="mt-6 max-w-3xl space-y-5">
                        <h2 class="text-4xl font-extrabold leading-tight text-white/95 sm:text-5xl lg:text-6xl">
                            Maini prasmes, nevis naudu ar
                            <span class="timerr-hero-wordmark relative inline-block text-neon-accent">
                                Timerr
                            </span>
                        </h2>
                        <p class="text-lg leading-8 text-gray-300 sm:text-xl">
                            Timerr palīdz cilvēkiem atrast pakalpojumus, piedāvāt savas prasmes un norēķināties ar laika kredītiem. Viena stunda tava darba kļūst par vienu kredītu, ko vari izmantot citai palīdzībai.
                        </p>
                    </div>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="/jobs" class="hero-primary-button inline-flex min-h-11 items-center justify-center rounded-lg border border-neon-accent bg-neon-accent px-5 py-2.5 text-sm font-semibold text-black shadow-lg shadow-neon-glow/10 transition hover:bg-neon-accent/90 focus:outline-none focus:ring-2 focus:ring-neon-accent/50">
                            Pārlūkot pakalpojumus
                        </a>
                        <x-button href="/jobs/create" class="justify-center text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700">
                            Piedāvāt prasmi
                        </x-button>
                    </div>

                    <form action="/jobs" method="GET" class="mt-8 max-w-3xl rounded-lg border border-gray-800 bg-gray-950/40 p-2 shadow-lg">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center">
                            <label for="search" class="sr-only">Meklēt</label>
                            <div class="flex min-w-0 flex-1 items-center gap-2 px-3">
                                <svg class="h-5 w-5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"></path>
                                </svg>
                                <input id="search" name="search" type="search" placeholder="Meklē prasmes, pakalpojumus vai cilvēkus" class="w-full bg-transparent px-2 py-3 text-gray-100 placeholder-gray-400 focus:outline-none" />
                            </div>

                            <select name="category" class="rounded-md border border-gray-800 bg-gray-900/60 px-3 py-3 text-gray-100 md:max-w-56">
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

                            <button class="rounded-md bg-neon-accent px-5 py-3 font-semibold text-black transition hover:bg-neon-accent/90">Meklēt</button>
                        </div>
                    </form>
                </div>

                <div class="border-t border-gray-800 bg-gray-950/40 p-6 sm:p-8 lg:border-l lg:border-t-0 lg:p-10">
                    <div class="grid h-full content-between gap-6">
                        <div>
                            <div class="flex items-end justify-between gap-4 border-b border-gray-800 pb-5">
                                <div>
                                    <div class="text-sm uppercase tracking-[0.18em] text-gray-400">šobrīd Timerr</div>
                                    <div class="mt-2 text-5xl font-extrabold text-neon-accent">{{ number_format($primaryStat['value']) }}</div>
                                    <div class="mt-1 text-gray-300">{{ $primaryStat['label'] }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-3xl font-bold text-white/90">{{ number_format($secondaryStat['value']) }}</div>
                                    <div class="text-sm text-gray-400">{{ $secondaryStat['label'] }}</div>
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-2 gap-3">
                                @foreach($stats as $stat)
                                    <div class="rounded-lg border border-gray-800 bg-gray-900/60 p-4">
                                        <div class="text-2xl font-bold text-white/95">{{ number_format($stat['value']) }}</div>
                                        <div class="mt-1 text-sm font-semibold text-neon-accent">{{ $stat['label'] }}</div>
                                        <p class="mt-2 text-xs leading-5 text-gray-400">{{ $stat['detail'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-400">Kopienas cikls</span>
                                <span class="text-neon-accent">darbs -> palīdzība -> prasme</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="h-2 rounded-full bg-neon-accent"></div>
                                <div class="h-2 rounded-full bg-cyan-400"></div>
                                <div class="h-2 rounded-full bg-violet-400"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-xs text-gray-400">
                                <span>Nopelni</span>
                                <span class="text-center">Saņem</span>
                                <span class="text-right">Piedāvā</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Product Insights -->
        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach($insights as $insight)
                <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-5">
                    <div class="text-sm font-semibold uppercase tracking-[0.16em] text-gray-400">{{ $insight['label'] }}</div>
                    <div class="mt-3 text-2xl font-bold text-white/95">{{ $insight['value'] }}</div>
                    <p class="mt-2 text-sm leading-6 text-gray-300">{{ $insight['detail'] }}</p>
                </div>
            @endforeach
        </section>

        <!-- How It Works -->
        <section>
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-2xl font-semibold text-white/90">Kā Timerr pārvērš laiku vērtībā</h3>
                    <p class="mt-2 text-gray-400">Īss ceļš no prasmes līdz uzticamai apmaiņai.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                <div class="hover-card rounded-lg border border-gray-700 bg-gray-800/40 p-6 transition-all duration-300 hover:border-neon-accent/50">
                    <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-md bg-neon-accent/15 text-lg font-bold text-neon-accent">1</div>
                    <h4 class="mb-2 text-xl font-semibold text-white/90">Piedāvā prasmi</h4>
                    <p class="text-gray-300">Publicē to, ko proti palīdzēt izdarīt, un norādi laika kredītu vērtību.</p>
                </div>
                <div class="hover-card rounded-lg border border-gray-700 bg-gray-800/40 p-6 transition-all duration-300 hover:border-neon-accent/50">
                    <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-md bg-cyan-400/15 text-lg font-bold text-cyan-300">2</div>
                    <h4 class="mb-2 text-xl font-semibold text-white/90">Vienojies</h4>
                    <p class="text-gray-300">Sazinies ar cilvēkiem, precizē rezultātu un vienojies par darba apjomu.</p>
                </div>
                <div class="hover-card rounded-lg border border-gray-700 bg-gray-800/40 p-6 transition-all duration-300 hover:border-neon-accent/50">
                    <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-md bg-violet-400/15 text-lg font-bold text-violet-300">3</div>
                    <h4 class="mb-2 text-xl font-semibold text-white/90">Pabeidz darbu</h4>
                    <p class="text-gray-300">Iesniedz paveikto, saņem apstiprinājumu un nopelni laika kredītus.</p>
                </div>
                <div class="hover-card rounded-lg border border-gray-700 bg-gray-800/40 p-6 transition-all duration-300 hover:border-neon-accent/50">
                    <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-md bg-emerald-400/15 text-lg font-bold text-emerald-300">4</div>
                    <h4 class="mb-2 text-xl font-semibold text-white/90">Izmanto laiku</h4>
                    <p class="text-gray-300">Tērē kredītus citiem pakalpojumiem un turpini kopienas apmaiņas ciklu.</p>
                </div>
            </div>
        </section>

        <!-- Popular Categories -->
        <section>
            <h3 class="mb-4 text-2xl font-semibold text-white/90">Populārās kategorijas</h3>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                @foreach(($categories ?? []) as $key => $group)
                    <a href="/jobs?category={{ urlencode($key) }}" class="hover-card block rounded-lg border border-gray-700 bg-gray-800/40 p-4 transition-all duration-200 hover:border-neon-accent">
                        <div class="font-semibold text-white/90">{{ $group['label'] }}</div>
                        @if(!empty($group['children']))
                            <div class="mt-2 text-sm leading-5 text-gray-400">{{ count($group['children']) }} apakškategorijas</div>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>

        <!-- Featured Jobs -->
        <section>
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-2xl font-semibold text-white/90">Jaunākie pakalpojumi Timerr</h3>
                <a href="/jobs" class="text-neon-accent hover:text-neon-accent/80">Skatīt visus</a>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @if(isset($featuredJobs) && $featuredJobs->count() > 0)
                    @foreach($featuredJobs as $job)
                        <div
                            onclick="window.location='/jobs/{{ $job->id }}'"
                            class="hover-card flex h-full cursor-pointer flex-col justify-between overflow-hidden rounded-lg border border-gray-700 bg-gray-800/40 transition-all duration-300 hover:border-neon-accent"
                        >
                            <x-job-image :job="$job" class="rounded-none border-0 border-b border-gray-700" />

                            <div class="flex flex-1 flex-col p-4">
                                <div class="flex min-w-0 items-center gap-2 text-sm font-semibold text-neon-accent">
                                    <x-avatar :user="$job->user" size="sm" />
                                    <a
                                        href="{{ route('people.show', $job->user->id) }}"
                                        onclick="event.stopPropagation()"
                                        class="min-w-0 truncate transition-colors duration-200 hover:text-neon-accent/80"
                                    >
                                        {{ $job->user->first_name }} {{ $job->user->last_name }}
                                    </a>
                                </div>

                                <div class="mt-2 min-w-0 truncate font-semibold text-white/90">
                                    {{ Str::limit($job->title, 60) }}
                                </div>

                                <div class="mt-2 flex-1 text-sm text-gray-400">
                                    {{ Str::limit($job->description, 80) }}
                                </div>

                                <div class="mt-3 flex min-w-0 items-center justify-between gap-3">
                                    <span class="shrink-0 rounded-md bg-neon-accent/20 px-3 py-1 text-xs font-semibold text-neon-accent">
                                        {{ $job->time_credits }} kredīti
                                    </span>
                                    <span class="min-w-0 truncate text-xs text-gray-400">
                                        {{ $job->category }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-span-full rounded-lg border border-gray-700 bg-gray-800/40 py-8 text-center text-gray-400">
                        <p>Pašlaik nav izcelto pakalpojumu</p>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-layout>
