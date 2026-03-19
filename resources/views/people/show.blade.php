<x-layout>
    <x-slot:heading>Profile: {{ $user->first_name }} {{ $user->last_name }}</x-slot:heading>

    <div class="max-w-3xl mx-auto">
        <div class="bg-gray-800/40 backdrop-blur-sm p-8 rounded-lg border border-gray-700">
            <div class="flex items-center space-x-6">
                <div class="flex-shrink-0">
                    <div class="w-24 h-24 rounded-full bg-gray-700 flex items-center justify-center text-3xl text-white/90">
                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h2 class="text-2xl font-semibold text-white/90">{{ $user->first_name }} {{ $user->last_name }}</h2>
                        @if($user->reviews_received_rating_avg && $user->reviews_received_rating_avg > 0)
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($user->reviews_received_rating_avg))
                                        <span class="text-yellow-400 text-lg">★</span>
                                    @elseif($i - 0.5 <= $user->reviews_received_rating_avg)
                                        <span class="text-yellow-400 text-lg">☆</span>
                                    @else
                                        <span class="text-gray-600 text-lg">★</span>
                                    @endif
                                @endfor
                                <span class="text-xs text-gray-400 ml-1">({{ number_format($user->reviews_received_rating_avg, 1) }})</span>
                            </div>
                        @else
                            <span class="text-sm text-gray-500">No rating</span>
                        @endif
                    </div>
                    <p class="text-gray-400">{{ $user->email }}</p>
                    <div class="mt-4 inline-flex items-center px-4 py-2 rounded-full bg-gray-900/60 border border-gray-700">
                        <span class="text-neon-accent font-medium">{{ $user->time_credits }}</span>
                        <span class="ml-2 text-gray-400">Time Credits</span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-400">{{ $user->jobs_count ?? $user->jobs()->count() }} help requests</div>
                    <div class="text-sm text-gray-400">{{ $user->completed_jobs_count ?? $user->completedJobsCount() }} completed jobs</div>
                </div>
            </div>

            <div class="mt-4">
                @auth
                    @if(auth()->id() !== $user->id)
                        <a href="{{ route('messages.conversation', $user->id) }}" class="bg-neon-accent text-black px-3 py-2 rounded text-sm font-medium">Message</a>
                    @endif
                    
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.users.manage', $user) }}" class="ml-2 bg-purple-600 text-white px-3 py-2 rounded text-sm font-medium hover:bg-purple-700">Manage User</a>
                    @endif
                @endauth
            </div>

            @if($user->jobs()->count() > 0)
                <div class="mt-6">
                    <h3 class="font-semibold text-white/90 mb-2">Help Requests</h3>
                    <div class="space-y-3">
                        @foreach($user->jobs as $job)
                            <a href="/jobs/{{ $job->id }}" class="block bg-gray-900/60 p-3 rounded border border-gray-700 hover:border-neon-accent">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="text-white/90 font-medium">{{ $job->title }}</div>
                                        <div class="text-gray-400 text-sm">{{ Str::limit($job->description, 80) }}</div>
                                    </div>
                                    <div class="text-sm text-gray-300">{{ $job->time_credits }} credits</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
            
            {{-- Reviews received --}}
            <div class="mt-6">
                <h3 class="font-semibold text-white/90 mb-2">Reviews</h3>
                @if($user->reviewsReceived()->count() === 0)
                    <div class="text-gray-400 text-sm">No reviews yet.</div>
                @else
                    <div class="space-y-3">
                        @foreach($user->reviewsReceived as $review)
                            <div class="bg-gray-900/60 p-3 rounded border border-gray-700">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="text-sm text-gray-300">
                                        <a href="{{ route('people.show', $review->reviewer->id) }}" class="hover:text-neon-accent transition-colors duration-200">
                                            {{ $review->reviewer->first_name }} {{ $review->reviewer->last_name }}
                                        </a> — <span class="text-neon-accent">{{ $review->rating }}/5</span>
                                    </div>
                                        @if($review->comment)
                                            <div class="mt-2 text-gray-300 whitespace-pre-line">{{ $review->comment }}</div>
                                        @endif
                                        <div class="text-xs text-gray-500 mt-2">{{ $review->created_at->format('M j, Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>
