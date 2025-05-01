<x-layout>
    <x-slot:heading>
        Help Requests
    </x-slot:heading>

    <div class="space-y-4">
        @foreach ($jobs as $job)
            <a href="/jobs/{{ $job['id'] }}" class="block px-4 py-6 border border-gray-700 rounded-lg bg-gray-800/40 backdrop-blur-sm hover:bg-gray-700/40 transition-colors duration-200">
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
</x-layout>
