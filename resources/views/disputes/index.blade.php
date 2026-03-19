<x-layout>
    <x-slot name="heading">Dispute Management</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-white mb-2">Dispute & Admin Review Management</h2>
            <p class="text-gray-300">Review and resolve disputes between users, and handle admin reviews from declined jobs</p>
        </div>

        @if($disputes->count() === 0)
            <div class="bg-gray-900/60 border border-gray-700 rounded-lg p-8 text-center">
                <div class="text-gray-400 text-lg mb-2">No active disputes</div>
                <p class="text-gray-500">All submissions are currently running smoothly!</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($disputes as $dispute)
                    <div class="bg-gray-900/60 border border-gray-700 rounded-lg p-6 hover-card">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-white mb-2">
                                    {{ $dispute->jobListing->title }}
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-400">Job Poster:</span>
                                        <a href="{{ route('people.show', $dispute->jobListing->user->id) }}" class="text-white ml-2 hover:text-neon-accent transition-colors duration-200">
                                            {{ $dispute->jobListing->user->first_name }} {{ $dispute->jobListing->user->last_name }}
                                        </a>
                                    </div>
                                    <div>
                                        <span class="text-gray-400">Worker:</span>
                                        <a href="{{ route('people.show', $dispute->user->id) }}" class="text-white ml-2 hover:text-neon-accent transition-colors duration-200">
                                            {{ $dispute->user->first_name }} {{ $dispute->user->last_name }}
                                        </a>
                                    </div>
                                    <div>
                                        <span class="text-gray-400">Type:</span>
                                        @if($dispute->status === \App\Models\JobSubmission::STATUS_ADMIN_REVIEW)
                                            <span class="text-white ml-2">Admin Review (Decline)</span>
                                        @else
                                            <span class="text-white ml-2">Manual Dispute</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="ml-4">
                                <span class="inline-flex px-3 py-1 text-xs font-medium rounded-full
                                    @if($dispute->status === \App\Models\JobSubmission::STATUS_ADMIN_REVIEW)
                                        bg-purple-500/20 text-purple-200 border border-purple-500
                                    @elseif($dispute->dispute_status === \App\Models\JobSubmission::DISPUTE_REQUESTED)
                                        bg-yellow-500/20 text-yellow-200 border border-yellow-500
                                    @elseif($dispute->dispute_status === \App\Models\JobSubmission::DISPUTE_UNDER_REVIEW)
                                        bg-blue-500/20 text-blue-200 border border-blue-500
                                    @elseif($dispute->dispute_status === \App\Models\JobSubmission::DISPUTE_RESOLVED)
                                        bg-green-500/20 text-green-200 border border-green-500
                                    @endif
                                ">
                                    @if($dispute->status === \App\Models\JobSubmission::STATUS_ADMIN_REVIEW)
                                        Admin Review
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $dispute->dispute_status)) }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                        @if($dispute->status === \App\Models\JobSubmission::STATUS_ADMIN_REVIEW)
                            <p class="text-gray-300 text-sm mb-2"><strong>Admin Review Details:</strong></p>
                            <p class="text-purple-200 bg-purple-900/20 p-3 rounded border border-purple-600">
                                {{ $dispute->admin_notes }}
                            </p>
                        @else
                            <p class="text-gray-300 text-sm mb-2"><strong>Dispute Reason:</strong></p>
                            <p class="text-gray-200 bg-gray-800/40 p-3 rounded border border-gray-600">
                                {{ $dispute->dispute_reason }}
                            </p>
                        @endif
                    </div>

                        @if($dispute->dispute_status !== \App\Models\JobSubmission::DISPUTE_RESOLVED)
                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('disputes.show', $dispute) }}" 
                                   class="px-4 py-2 bg-neon-accent text-black font-medium rounded hover:bg-neon-accent/80 transition-colors duration-200">
                                    Review & Resolve
                                </a>
                            </div>
                        @else
                            <div class="mb-4">
                                <p class="text-gray-300 text-sm mb-2"><strong>Resolution:</strong></p>
                                <p class="text-green-200 bg-green-900/20 p-3 rounded border border-green-700">
                                    {{ $dispute->dispute_resolution }}
                                </p>
                            </div>
                            <div class="text-sm text-gray-400">
                                Resolved by {{ $dispute->disputeResolver->first_name }} {{ $dispute->disputeResolver->last_name }} 
                                @if($dispute->dispute_resolved_at)
                                    on {{ $dispute->dispute_resolved_at->format('M j, Y \a\t g:i A') }}
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $disputes->links() }}
            </div>
        @endif
    </div>
</x-layout>
