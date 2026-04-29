<x-layout>
    <x-slot:heading>
        Palīdzības pieprasījumi
    </x-slot:heading>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- categories sidebar -->
        <aside class="lg:col-span-1 bg-gray-800/30 rounded-lg border border-gray-700 p-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-semibold text-white/90">Kategorijas</h3>
                @if(request('category'))
                    <a href="/jobs" class="text-xs text-gray-400 hover:text-neon-accent transition-colors">
                        Notīrīt
                    </a>
                @endif
            </div>
            <div class="space-y-2">
                @foreach(($categories ?? []) as $topKey => $group)
                    <div>
                        <div class="text-sm font-medium text-white/90 mb-2">{{ $group['label'] }}</div>
                        @if(!empty($group['children']) && is_array($group['children']))
                            <ul class="pl-4 text-sm text-gray-300 space-y-1">
                                @foreach($group['children'] as $slug => $label)
                                    <li>
                                        <a href="/jobs?category={{ urlencode($slug) }}{{ request('search') ? '&search=' . urlencode(request('search')) : '' }}" 
                                           class="block px-2 py-1 rounded hover:bg-gray-700/50 transition-colors {{ request('category') == $slug ? 'bg-green-500/20 text-green-300 border-l-2 border-green-500' : 'hover:text-neon-accent' }}">
                                            {{ $label }}
                                        </a>
                                    </li>
                                @endforeach
                                <li class="mt-2 pt-1 border-t border-gray-700">
                                    <a href="/jobs?category={{ urlencode($topKey) }}{{ request('search') ? '&search=' . urlencode(request('search')) : '' }}" 
                                       class="block px-2 py-1 rounded text-xs {{ request('category') == $topKey ? 'bg-green-500/20 text-green-300 border-l-2 border-green-500' : 'text-gray-400 hover:text-neon-accent' }} transition-colors">
                                        Visi {{ $group['label'] }}
                                    </a>
                                </li>
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </aside>

        <div class="lg:col-span-3 space-y-4">
        <!-- search and sort controls -->
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <!-- search -->
            <div class="flex-1">
                <form action="/jobs" method="GET" class="flex gap-2">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="flex-1 px-4 py-2 border border-gray-700 rounded-lg bg-gray-800/40 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-neon-accent"
                           placeholder="Meklēt darbus...">
                    <button type="submit" class="px-4 py-2 bg-neon-accent text-gray-900 rounded-lg hover:bg-neon-accent/90 transition-colors">
                        Meklēt
                    </button>
                </form>
            </div>

            <!-- sort by -->
            <div class="flex gap-2">
                <form action="/jobs" method="GET" class="flex gap-2">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <input type="number" name="min_credits" min="0" placeholder="Min kredīti" value="{{ request('min_credits') }}" onchange="this.form.submit()" class="px-3 py-2 border border-gray-700 rounded bg-gray-800/40 text-white w-32" />
                    <input type="number" name="max_credits" min="0" placeholder="Max kredīti" value="{{ request('max_credits') }}" onchange="this.form.submit()" class="px-3 py-2 border border-gray-700 rounded bg-gray-800/40 text-white w-32" />
                    <select name="sort" onchange="this.form.submit()" class="px-4 py-2 border border-gray-700 rounded-lg bg-gray-800/40 text-white">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Jaunākie vispirms</option>
                        <option value="created_asc" {{ request('sort') == 'created_asc' ? 'selected' : '' }}>Vecākie vispirms</option>
                        <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>Nosaukums (A-Ž)</option>
                        <option value="title_desc" {{ request('sort') == 'title_desc' ? 'selected' : '' }}>Nosaukums (Ž-A)</option>
                        <option value="cheapest" {{ request('sort') == 'cheapest' ? 'selected' : '' }}>Lētākie</option>
                        <option value="expensive" {{ request('sort') == 'expensive' ? 'selected' : '' }}>Dārgākie</option>
                        <option value="seller_most_credits" {{ request('sort') == 'seller_most_credits' ? 'selected' : '' }}>Pārdevēji ar visvairāk kredītu</option>
                    </select>
                </form>
            </div>
        </div>

    <!-- jobs list -->
    <div class="space-y-4">
            @foreach ($jobs as $job)
                <div class="overflow-hidden border border-gray-700 rounded-lg bg-gray-800/40 backdrop-blur-sm hover:bg-gray-700/40 transition-colors duration-200 hover-card">
                    <div class="grid gap-0 md:grid-cols-[220px_1fr]">
                        <a href="/jobs/{{ $job->id }}" class="block">
                            <x-job-image :job="$job" ratio="aspect-[4/3]" class="h-full rounded-none border-0 border-r border-gray-700" />
                        </a>

                        <div class="p-5">
                    <div class="flex min-w-0 items-center gap-2 text-sm font-bold text-neon-accent">
                        <x-avatar :user="$job->user" size="sm" />
                        <span class="min-w-0 truncate">
                            <a href="{{ route('people.show', $job->user->id) }}" class="transition-colors duration-200 hover:text-neon-accent/80">
                                {{ $job->user->first_name }} {{ $job->user->last_name }}
                            </a> vajag palīdzību
                        </span>
                    </div>

                    <div class="mt-2 min-w-0 truncate font-semibold text-white/90">
                        <a href="/jobs/{{ $job->id }}" class="transition-colors duration-200 hover:text-neon-accent">
                            {{ $job['title'] }}
                        </a>
                    </div>
                    
                    @php
                        $catLabel = $job->category;
                        foreach (($categories ?? []) as $topKey => $group) {
                            if ($topKey === $job->category) {
                                $catLabel = $group['label'];
                                break;
                            }
                            if (!empty($group['children']) && is_array($group['children'])) {
                                foreach ($group['children'] as $slug => $label) {
                                    if ($slug === $job->category) {
                                        $catLabel = $label;
                                        break 2;
                                    }
                                }
                            }
                        }
                    @endphp
                    <div class="mt-1 flex min-w-0 items-center justify-between gap-3 text-sm text-gray-300">
                        <span class="min-w-0 truncate">Kategorija: <span class="capitalize">{{ $catLabel }}</span></span>
                        <span class="shrink-0 rounded-full bg-neon-accent/20 px-3 py-1 text-xs font-semibold text-neon-accent">
                            {{ $job['time_credits'] }} laika kredīti
                        </span>
                    </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div>
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</x-layout>
