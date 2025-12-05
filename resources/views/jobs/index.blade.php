<x-layout>
    <x-slot:heading>
        Help Requests
    </x-slot:heading>

    <div class="space-y-4">
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
                    <select name="sort" onchange="this.form.submit()" class="px-4 py-2 border border-gray-700 rounded-lg bg-gray-800/40 text-white">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest First</option>
                        <option value="created_asc" {{ request('sort') == 'created_asc' ? 'selected' : '' }}>Oldest First</option>
                        <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                        <option value="title_desc" {{ request('sort') == 'title_desc' ? 'selected' : '' }}>Title (Z-A)</option>
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
                    
                    <div class="mt-1 text-gray-300 text-sm flex items-center justify-between">
                        <span>Category: <span class="capitalize">{{ $job['category'] }}</span></span>
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
