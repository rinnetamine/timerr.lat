<x-layout>
    <x-slot:heading>
        Help Requests
    </x-slot:heading>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- categories sidebar -->
        <aside class="lg:col-span-1 bg-gray-800/30 rounded-lg border border-gray-700 p-4">
            <h3 class="text-lg font-semibold text-white/90 mb-3">Categories</h3>
            <div class="space-y-2">
                @foreach(($categories ?? []) as $topKey => $group)
                    <div>
                        <div class="text-sm font-medium text-white/90">{{ $group['label'] }}</div>
                        @if(!empty($group['children']) && is_array($group['children']))
                            <ul class="mt-2 pl-4 text-sm text-gray-300 space-y-1">
                                @foreach($group['children'] as $slug => $label)
                                    <li><a href="/jobs?category={{ urlencode($slug) }}" class="hover:text-neon-accent">{{ $label }}</a></li>
                                @endforeach
                                <li class="mt-1"><a href="/jobs?category={{ urlencode($topKey) }}" class="text-xs text-gray-400 hover:text-neon-accent">All {{ $group['label'] }}</a></li>
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
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="flex-1 px-4 py-2 border border-gray-700 rounded-lg bg-gray-800/40 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-neon-accent"
                           placeholder="Search jobs...">
                    <button type="submit" class="px-4 py-2 bg-neon-accent text-gray-900 rounded-lg hover:bg-neon-accent/90 transition-colors">
                        Search
                    </button>
                </form>
            </div>

            <!-- sort by -->
            <div class="flex gap-2">
                <form action="/jobs" method="GET" class="flex gap-2">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="number" name="min_credits" min="0" placeholder="min credits" value="{{ request('min_credits') }}" class="px-3 py-2 border border-gray-700 rounded bg-gray-800/40 text-white w-32" />
                    <input type="number" name="max_credits" min="0" placeholder="max credits" value="{{ request('max_credits') }}" class="px-3 py-2 border border-gray-700 rounded bg-gray-800/40 text-white w-32" />
                    <select name="sort" onchange="this.form.submit()" class="px-4 py-2 border border-gray-700 rounded-lg bg-gray-800/40 text-white">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest First</option>
                        <option value="created_asc" {{ request('sort') == 'created_asc' ? 'selected' : '' }}>Oldest First</option>
                        <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                        <option value="title_desc" {{ request('sort') == 'title_desc' ? 'selected' : '' }}>Title (Z-A)</option>
                        <option value="cheapest" {{ request('sort') == 'cheapest' ? 'selected' : '' }}>Cheapest</option>
                        <option value="expensive" {{ request('sort') == 'expensive' ? 'selected' : '' }}>Most expensive</option>
                        <option value="seller_most_credits" {{ request('sort') == 'seller_most_credits' ? 'selected' : '' }}>Sellers with most credits</option>
                    </select>
                </form>
            </div>
        </div>

    <!-- jobs list -->
    <div class="space-y-4">
            @foreach ($jobs as $job)
                <a href="/jobs/{{ $job['id'] }}" class="block px-4 py-6 border border-gray-700 rounded-lg bg-gray-800/40 backdrop-blur-sm hover:bg-gray-700/40 transition-colors duration-200 hover-card">
                    <div class="font-bold text-neon-accent text-sm">{{ $job->user->first_name }} {{ $job->user->last_name }} needs help</div>

                    <div class="mt-2 text-white/90 font-semibold">
                        {{ $job['title'] }}
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
                            <div class="mt-1 text-gray-300 text-sm flex items-center justify-between">
                                <span>Category: <span class="capitalize">{{ $catLabel }}</span></span>
                        <span class="bg-neon-accent/20 text-neon-accent px-3 py-1 rounded-full text-xs font-semibold">
                            {{ $job['time_credits'] }} time credits
                        </span>
                    </div>
                </a>
            @endforeach

            <div>
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</x-layout>
