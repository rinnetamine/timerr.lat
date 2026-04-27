<x-layout :hidePageHeader="true" :stretchMain="true">
    <x-slot:heading>Ziņojumi</x-slot:heading>

    <div class="mx-auto max-w-7xl grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- SIDEBAR -->
        <aside class="lg:col-span-1 bg-gray-800/30 rounded-lg border border-gray-700 p-4 h-[82vh] overflow-y-auto">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-white/90">Iesūtne</h3>
                <a href="{{ route('messages.create') }}" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-neon-accent/40 bg-neon-accent/10 text-neon-accent transition-colors hover:bg-neon-accent hover:text-black" title="Jauna saruna" aria-label="Jauna saruna">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"></path>
                    </svg>
                </a>
            </div>

            <div class="space-y-3">
                @forelse($conversations as $c)
                    <div 
                        onclick="window.location='{{ route('messages.conversation', $c['other']->id) }}'" 
                        class="cursor-pointer flex items-center gap-3 p-3 rounded border transition hover:bg-gray-900/60 hover:border-neon-accent border-transparent">

                        <!-- Avatar -->
                        <x-avatar :user="$c['other']" size="sm" />

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center gap-2">

                                <!-- Name -->
                                <div class="truncate font-medium text-white/90 flex items-center gap-2">
                                    <a 
                                        href="{{ route('people.show', $c['other']->id) }}" 
                                        onclick="event.stopPropagation()" 
                                        class="hover:text-neon-accent transition-colors duration-200"
                                    >
                                        {{ $c['other']->first_name }} {{ $c['other']->last_name }}
                                    </a>
                                    @if(isset($c['job_relationship']))
                                        @if($c['job_relationship'] === 'worker')
                                            <span class="text-xs bg-blue-500/20 text-blue-300 px-1.5 py-0.5 rounded">Strādnieks</span>
                                        @else
                                            <span class="text-xs bg-green-500/20 text-green-300 px-1.5 py-0.5 rounded">Klients</span>
                                        @endif
                                    @endif
                                </div>

                                <!-- Time -->
                                <div class="text-xs text-gray-400 whitespace-nowrap">
                                    {{ $c['latest']->created_at->diffForHumans() }}
                                </div>
                            </div>

                            <!-- Last message -->
                            <div class="text-sm text-gray-400 truncate">
                                @if($c['latest']->body)
                                    {{ $c['latest']->body }}
                                @else
                                    <span class="text-gray-500 italic">Nav ziņojumu</span>
                                @endif
                            </div>
                        </div>

                        <!-- Unread -->
                        @if($c['unread'] > 0)
                            <span class="inline-block w-2 h-2 rounded-full bg-neon-accent"></span>
                        @endif

                    </div>
                @empty
                    <div class="text-gray-400">Vēl nav sarunu</div>
                @endforelse
            </div>
        </aside>

        <!-- CHAT PANEL -->
        <div class="lg:col-span-2 bg-gray-800/30 rounded-lg border border-gray-700 flex flex-col h-[82vh] overflow-hidden">

            <!-- HEADER -->
            <header class="px-6 py-4 flex items-center justify-between border-b border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center text-white text-lg font-semibold">M</div>
                    <div>
                        <div class="font-semibold text-white/90">Tavi ziņojumi</div>
                        <div class="text-sm text-gray-400">Izvēlies sarunu pa kreisai, lai skatītu un atbildētu</div>
                    </div>
                </div>

                <a href="{{ route('messages.create') }}" class="inline-flex items-center px-4 py-2 bg-neon-accent text-black rounded-lg hover:bg-neon-accent/90 transition-colors duration-200 font-medium text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Sūtīt ziņojumu kādam
                </a>
            </header>

            <!-- WELCOME CONTENT -->
            <div class="flex-1 p-6 flex items-center justify-center">
                <div class="text-center max-w-md">
                    <div class="w-20 h-20 rounded-full bg-gray-700 flex items-center justify-center text-white text-2xl font-semibold mx-auto mb-4">M</div>
                    <h3 class="text-xl font-semibold text-white/90 mb-2">Sveicināti ziņojumos</h3>
                    <p class="text-gray-400">Izvēlies sarunu no saraksta pa kreisai, lai sāktu tērzēšanu. Ziņojumi ir šifrēti no galas līdz galam un parādīsies šeit, kad atvērsi tērzēšanu.</p>
                    <a href="{{ route('messages.create') }}" class="mt-5 inline-flex items-center gap-2 rounded-full bg-neon-accent px-4 py-2 text-sm font-medium text-black transition-colors hover:bg-neon-accent/90">
                        Sākt jaunu sarunu
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-layout>
