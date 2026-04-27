<x-layout :hidePageHeader="true" :stretchMain="true">
    <x-slot:heading>Jauna saruna</x-slot:heading>

    <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 lg:grid-cols-3">
        <aside class="h-[82vh] overflow-y-auto rounded-lg border border-gray-700 bg-gray-800/30 p-4 lg:col-span-1">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-white/90">Iesūtne</h3>
                <a href="{{ route('messages.create') }}" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-neon-accent bg-neon-accent text-black" title="Jauna saruna" aria-label="Jauna saruna">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"></path>
                    </svg>
                </a>
            </div>

            <div class="space-y-3">
                @forelse($conversations as $c)
                    <div onclick="window.location='{{ route('messages.conversation', $c['other']->id) }}'" class="flex cursor-pointer items-center gap-3 rounded border border-transparent p-3 transition hover:border-neon-accent hover:bg-gray-900/60">
                        <x-avatar :user="$c['other']" size="sm" />
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <div class="truncate font-medium text-white/90">{{ $c['other']->first_name }} {{ $c['other']->last_name }}</div>
                                <div class="whitespace-nowrap text-xs text-gray-400">{{ $c['latest']->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="truncate text-sm text-gray-400">
                                {{ $c['latest']->body ?: 'Nav ziņojumu' }}
                            </div>
                        </div>
                        @if($c['unread'] > 0)
                            <span class="inline-block h-2 w-2 rounded-full bg-neon-accent"></span>
                        @endif
                    </div>
                @empty
                    <div class="rounded border border-gray-700 bg-gray-900/40 p-3 text-sm text-gray-400">Vēl nav sarunu</div>
                @endforelse
            </div>
        </aside>

        <section class="flex h-[82vh] flex-col overflow-hidden rounded-lg border border-gray-700 bg-gray-800/30 lg:col-span-2">
            <header class="border-b border-gray-700 px-6 py-4">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-white/90">Jauna saruna</h2>
                        <p class="text-sm text-gray-400">Izvēlies lietotāju un sāc rakstīt ziņojumu.</p>
                    </div>
                    <form action="{{ route('messages.create') }}" method="GET" class="flex w-full gap-2 md:w-auto">
                        <input type="search" name="q" value="{{ $search }}" placeholder="Meklēt lietotāju..." class="min-w-0 flex-1 rounded-md border border-gray-700 bg-gray-900/60 px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-neon-accent focus:outline-none focus:ring-2 focus:ring-neon-accent/30 md:w-72">
                        <button type="submit" class="rounded-md bg-neon-accent px-4 py-2 text-sm font-medium text-black transition-colors hover:bg-neon-accent/90">Meklēt</button>
                    </form>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6">
                <div class="grid gap-3 md:grid-cols-2">
                    @forelse($users as $user)
                        <a href="{{ route('messages.conversation', $user->id) }}" class="group flex items-center gap-3 rounded-lg border border-gray-700 bg-gray-900/35 p-4 transition-colors hover:border-neon-accent hover:bg-gray-900/60">
                            <x-avatar :user="$user" size="md" />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <div class="truncate font-medium text-white/90 group-hover:text-neon-accent">{{ $user->first_name }} {{ $user->last_name }}</div>
                                    @if($conversationUserIds->contains($user->id))
                                        <span class="shrink-0 rounded-full bg-neon-accent/15 px-2 py-0.5 text-xs text-neon-accent">Esoša</span>
                                    @endif
                                </div>
                                <div class="truncate text-sm text-gray-400">{{ $user->email }}</div>
                            </div>
                            <span class="shrink-0 rounded-full border border-gray-700 px-3 py-1 text-xs font-medium text-gray-300 transition-colors group-hover:border-neon-accent group-hover:text-neon-accent">
                                Sākt
                            </span>
                        </a>
                    @empty
                        <div class="rounded-lg border border-gray-700 bg-gray-900/35 p-6 text-gray-400 md:col-span-2">Neviens lietotājs netika atrasts.</div>
                    @endforelse
                </div>

                @if($users->hasPages())
                    <div class="mt-5">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-layout>
