<x-layout>
    <x-slot:heading>
        Your Applications
    </x-slot:heading>

    <div class="max-w-4xl mx-auto space-y-10">
        <!-- Received Applications (for help requests you posted) -->
        <div>
            <h2 class="text-xl font-semibold text-white/90 mb-4">Applications to Your Help Requests</h2>
            
            @if($receivedSubmissions->isEmpty())
                <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 text-gray-400">
                    You haven't received any applications yet. When someone applies to help with your requests, they'll appear here.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($receivedSubmissions as $submission)
                        <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-white/90">{{ $submission->jobListing->title }}</h3>
                                    <p class="text-neon-accent text-sm mt-1">{{ $submission->user->first_name }} {{ $submission->user->last_name }} wants to help</p>
                                </div>
                                <div>
                                    @if($submission->status === 'pending')
                                        <span class="bg-yellow-500/20 text-yellow-300 px-3 py-1 rounded-full text-xs font-semibold">Pending</span>
                                    @elseif($submission->status === 'approved')
                                        <span class="bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-xs font-semibold">Approved</span>
                                    @elseif($submission->status === 'declined')
                                        <span class="bg-red-500/20 text-red-300 px-3 py-1 rounded-full text-xs font-semibold">Declined</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mt-3 text-gray-300 text-sm">
                                <p>{{ Str::limit($submission->message, 100) }}</p>
                            </div>
                            
                            <div class="mt-4 flex justify-between items-center">
                                <a href="/submissions/{{ $submission->id }}" class="text-neon-accent hover:text-neon-accent/80 transition-colors duration-200 text-sm font-medium">
                                    View full application
                                </a>
                                
                                @if($submission->status === 'pending')
                                    <div class="flex space-x-2">
                                        <form method="POST" action="/submissions/{{ $submission->id }}/approve">
                                            @csrf
                                            <button type="submit" class="bg-green-700 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200">
                                                Approve
                                            </button>
                                        </form>
                                        
                                        <form method="POST" action="/submissions/{{ $submission->id }}/decline">
                                            @csrf
                                            <button type="submit" class="bg-red-700 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200">
                                                Decline
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        <!-- Your Applications (that you sent to others) -->
        <div>
            <h2 class="text-xl font-semibold text-white/90 mb-4">Your Applications to Help Others</h2>
            
            @if($sentSubmissions->isEmpty())
                <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 text-gray-400">
                    You haven't applied to any help requests yet. Browse the help requests and offer your assistance!
                </div>
            @else
                <div class="space-y-4">
                    @foreach($sentSubmissions as $submission)
                        <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-white/90">{{ $submission->jobListing->title }}</h3>
                                    <p class="text-gray-400 text-sm mt-1">Posted by {{ $submission->jobListing->user->first_name }} {{ $submission->jobListing->user->last_name }}</p>
                                </div>
                                <div>
                                    @if($submission->status === 'pending')
                                        <span class="bg-yellow-500/20 text-yellow-300 px-3 py-1 rounded-full text-xs font-semibold">Pending</span>
                                    @elseif($submission->status === 'approved')
                                        <span class="bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-xs font-semibold">Approved</span>
                                    @elseif($submission->status === 'declined')
                                        <span class="bg-red-500/20 text-red-300 px-3 py-1 rounded-full text-xs font-semibold">Declined</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mt-3 text-gray-300 text-sm">
                                <p>{{ Str::limit($submission->message, 100) }}</p>
                            </div>
                            
                            <div class="mt-4">
                                <a href="/submissions/{{ $submission->id }}" class="text-neon-accent hover:text-neon-accent/80 transition-colors duration-200 text-sm font-medium">
                                    View details
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layout>
