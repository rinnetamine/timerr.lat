{{-- Šis skats rāda publisko cilvēku katalogu ar meklēšanu un kārtošanu. --}}
<x-layout>
    <x-slot:heading>Cilvēki</x-slot:heading>

    <div class="max-w-3xl mx-auto">
        <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 mb-6">
            <form method="GET" action="{{ route('people.index') }}" class="flex gap-2 flex-wrap">
                <input type="search" name="q" value="{{ old('q', $q) }}" placeholder="Meklēt cilvēkus pēc vārda vai e-pasta..." class="flex-1 rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none" />

                <select name="sort" class="rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2">
                    <option value="name_asc" {{ (isset($sort) && $sort === 'name_asc') ? 'selected' : '' }}>Vārds (A-Ž)</option>
                    <option value="name_desc" {{ (isset($sort) && $sort === 'name_desc') ? 'selected' : '' }}>Vārds (Ž-A)</option>
                    <option value="newest" {{ (isset($sort) && $sort === 'newest') ? 'selected' : '' }}>Jaunākie</option>
                    <option value="oldest" {{ (isset($sort) && $sort === 'oldest') ? 'selected' : '' }}>Vecākie</option>
                    <option value="most_credits" {{ (isset($sort) && $sort === 'most_credits') ? 'selected' : '' }}>Visvairāk kredītu</option>
                    <option value="most_jobs" {{ (isset($sort) && $sort === 'most_jobs') ? 'selected' : '' }}>Visvairāk palīdzības pieprasījumu</option>
                    <option value="most_completed" {{ (isset($sort) && $sort === 'most_completed') ? 'selected' : '' }}>Visvairāk pabeigto darbu</option>
                    <option value="top_rated" {{ (isset($sort) && $sort === 'top_rated') ? 'selected' : '' }}>Augstāk novērtētie</option>
                </select>

                <button type="submit" class="bg-neon-accent text-black px-4 py-2 rounded-md">Meklēt</button>
            </form>
        </div>

        <div class="space-y-4">
            @foreach($users as $u)
                @php $isMe = auth()->check() && auth()->id() === $u->id; @endphp
                @if($isMe)
                    <a href="/profile" class="block hover-card bg-gray-900/60 p-4 rounded-lg border border-neon-accent">
                        <div class="flex justify-between items-center gap-4">
                            <x-avatar :user="$u" size="md" />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-lg font-semibold text-white/90">{{ $u->first_name }} {{ $u->last_name }} <span class="ml-2 inline-block text-xs bg-neon-accent text-black px-2 py-0.5 rounded">Es</span></h4>
                                    @if($u->reviews_received_rating_avg && $u->reviews_received_rating_avg > 0)
                                        <div class="flex items-center gap-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= floor($u->reviews_received_rating_avg))
                                                    <span class="text-yellow-400">★</span>
                                                @elseif($i - 0.5 <= $u->reviews_received_rating_avg)
                                                    <span class="text-yellow-400">☆</span>
                                                @else
                                                    <span class="text-gray-600">★</span>
                                                @endif
                                            @endfor
                                            <span class="text-xs text-gray-400 ml-1">({{ number_format($u->reviews_received_rating_avg, 1) }})</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500">Nav vērtējuma</span>
                                    @endif
                                </div>
                                <p class="text-gray-400 text-sm">{{ $u->email }}</p>
                            </div>
                            <div class="text-sm text-gray-300">
                                <div>{{ $u->jobs_count ?? $u->jobs()->count() }} palīdzības pieprasījumi</div>
                                <div>{{ $u->completed_jobs_count ?? $u->completedJobsCount() }} pabeigti darbi</div>
                                <div>{{ $u->time_credits }} kredīti</div>
                            </div>
                        </div>
                    </a>
                @else
                    <a href="{{ route('people.show', $u->id) }}" class="block hover-card bg-gray-800/40 p-4 rounded-lg border border-gray-700">
                        <div class="flex justify-between items-center gap-4">
                            <x-avatar :user="$u" size="md" />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-lg font-semibold text-white/90">{{ $u->first_name }} {{ $u->last_name }}</h4>
                                    @if($u->reviews_received_rating_avg && $u->reviews_received_rating_avg > 0)
                                        <div class="flex items-center gap-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= floor($u->reviews_received_rating_avg))
                                                    <span class="text-yellow-400">★</span>
                                                @elseif($i - 0.5 <= $u->reviews_received_rating_avg)
                                                    <span class="text-yellow-400">☆</span>
                                                @else
                                                    <span class="text-gray-600">★</span>
                                                @endif
                                            @endfor
                                            <span class="text-xs text-gray-400 ml-1">({{ number_format($u->reviews_received_rating_avg, 1) }})</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500">Nav vērtējuma</span>
                                    @endif
                                </div>
                                <p class="text-gray-400 text-sm">{{ $u->email }}</p>
                            </div>
                            <div class="text-sm text-gray-300">
                                <div>{{ $u->jobs_count ?? $u->jobs()->count() }} palīdzības pieprasījumi</div>
                                <div>{{ $u->completed_jobs_count ?? $u->completedJobsCount() }} pabeigti darbi</div>
                                <div>{{ $u->time_credits }} kredīti</div>
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
