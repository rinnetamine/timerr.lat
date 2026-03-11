<x-layout>
    <x-slot:heading>People</x-slot:heading>

    <div class="max-w-3xl mx-auto">
        <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 mb-6">
            <form method="GET" action="{{ route('people.index') }}" class="flex gap-2 flex-wrap">
                <input type="search" name="q" value="{{ old('q', $q) }}" placeholder="Search people by name or email..." class="flex-1 rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none" />

                <select name="sort" class="rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2">
                    <option value="name_asc" {{ (isset($sort) && $sort === 'name_asc') ? 'selected' : '' }}>Name (A-Z)</option>
                    <option value="name_desc" {{ (isset($sort) && $sort === 'name_desc') ? 'selected' : '' }}>Name (Z-A)</option>
                    <option value="newest" {{ (isset($sort) && $sort === 'newest') ? 'selected' : '' }}>Newest</option>
                    <option value="oldest" {{ (isset($sort) && $sort === 'oldest') ? 'selected' : '' }}>Oldest</option>
                    <option value="most_credits" {{ (isset($sort) && $sort === 'most_credits') ? 'selected' : '' }}>Most credits</option>
                    <option value="most_jobs" {{ (isset($sort) && $sort === 'most_jobs') ? 'selected' : '' }}>Most help requests</option>
                    <option value="top_rated" {{ (isset($sort) && $sort === 'top_rated') ? 'selected' : '' }}>Top rated</option>
                </select>

                <button type="submit" class="bg-neon-accent text-black px-4 py-2 rounded-md">Search</button>
            </form>
        </div>

        <div class="space-y-4">
            @foreach($users as $u)
                @php $isMe = auth()->check() && auth()->id() === $u->id; @endphp
                @if($isMe)
                    <a href="/profile" class="block hover-card bg-gray-900/60 p-4 rounded-lg border border-neon-accent">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="text-lg font-semibold text-white/90">{{ $u->first_name }} {{ $u->last_name }} <span class="ml-2 inline-block text-xs bg-neon-accent text-black px-2 py-0.5 rounded">You</span></h4>
                                <p class="text-gray-400 text-sm">{{ $u->email }}</p>
                            </div>
                            <div class="text-sm text-gray-300">
                                <div>{{ $u->jobs_count ?? $u->jobs()->count() }} help requests</div>
                                <div>{{ $u->time_credits }} credits</div>
                            </div>
                        </div>
                    </a>
                @else
                    <a href="{{ route('people.show', $u->id) }}" class="block hover-card bg-gray-800/40 p-4 rounded-lg border border-gray-700">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="text-lg font-semibold text-white/90">{{ $u->first_name }} {{ $u->last_name }}</h4>
                                <p class="text-gray-400 text-sm">{{ $u->email }}</p>
                            </div>
                            <div class="text-sm text-gray-300">
                                <div>{{ $u->jobs_count ?? $u->jobs()->count() }} help requests</div>
                                <div>{{ $u->time_credits }} credits</div>
                            </div>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
</x-layout>
