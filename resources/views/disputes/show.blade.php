<x-layout>
    <x-slot name="heading">Resolve Dispute</x-slot>

    <div class="max-w-4xl mx-auto">
        <!-- Job Information -->
        <div class="bg-gray-900/60 border border-gray-700 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4">Job Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-white mb-3">Job Information</h3>
                    <div class="space-y-2 text-sm">
                        <div><span class="text-gray-400">Title:</span> <span class="text-white ml-2">{{ $submission->jobListing->title }}</span></div>
                        <div><span class="text-gray-400">Credits:</span> <span class="text-neon-accent ml-2">{{ $submission->jobListing->time_credits }}</span></div>
                        <div><span class="text-gray-400">Category:</span> <span class="text-white ml-2">{{ $submission->jobListing->category }}</span></div>
                        <div><span class="text-gray-400">Status:</span> <span class="text-white ml-2">{{ ucfirst($submission->status) }}</span></div>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white mb-3">Involved Parties</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-400">Job Poster:</span>
                            @if($submission->jobListing->user)
                                <a href="{{ route('people.show', $submission->jobListing->user->id) }}" class="text-white ml-2 hover:text-neon-accent transition-colors duration-200">
                                    {{ $submission->jobListing->user->first_name }} {{ $submission->jobListing->user->last_name }}
                                </a>
                            @else
                                <span class="text-white ml-2">N/A</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-400">Worker:</span>
                            @if($submission->user)
                                <a href="{{ route('people.show', $submission->user->id) }}" class="text-white ml-2 hover:text-neon-accent transition-colors duration-200">
                                    {{ $submission->user->first_name }} {{ $submission->user->last_name }}
                                </a>
                            @else
                                <span class="text-white ml-2">N/A</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-400">Disputed by:</span>
                            @if($submission->disputeInitiator)
                                <a href="{{ route('people.show', $submission->disputeInitiator->id) }}" class="text-white ml-2 hover:text-neon-accent transition-colors duration-200">
                                    {{ $submission->disputeInitiator->first_name }} {{ $submission->disputeInitiator->last_name }}
                                </a>
                            @else
                                <span class="text-white ml-2">N/A</span>
                            @endif
                        </div>
                        <div><span class="text-gray-400">Frozen:</span> <span class="text-red-400 ml-2">{{ $submission->is_frozen ? 'Yes' : 'No' }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dispute Information -->
        <div class="bg-gray-900/60 border border-gray-700 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4">Dispute Details</h2>
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-white mb-2">Dispute Reason</h3>
                <p class="text-gray-200 bg-gray-800/40 p-4 rounded border border-gray-600">
                    {{ $submission->dispute_reason }}
                </p>
            </div>

            @if($submission->freeze_reason)
                <div>
                    <h3 class="text-lg font-semibold text-white mb-2">Freeze Reason</h3>
                    <p class="text-gray-200 bg-gray-800/40 p-4 rounded border border-gray-600">
                        {{ $submission->freeze_reason }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Submission Files (if any) -->
        @if($submission->files->count() > 0)
            <div class="bg-gray-900/60 border border-gray-700 rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4">Submitted Files</h2>
                <div class="space-y-2">
                    @foreach($submission->files as $file)
                        <div class="flex items-center justify-between p-3 bg-gray-800/40 rounded border border-gray-600">
                            <span class="text-gray-200">{{ $file->filename }}</span>
                            <a href="{{ route('files.download', $file) }}" 
                               class="px-3 py-1 bg-neon-accent text-black text-sm rounded hover:bg-neon-accent/80 transition-colors duration-200">
                                Download
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Resolution Form -->
        @if($submission->dispute_status !== \App\Models\JobSubmission::DISPUTE_RESOLVED)
            <form action="{{ route('disputes.resolve', $submission) }}" method="POST" class="bg-gray-900/60 border border-gray-700 rounded-lg p-6">
                @csrf
                <h2 class="text-xl font-bold text-white mb-4">Resolve Dispute</h2>
                
                <div class="mb-6">
                    <x-form-field>
                        <x-form-label for="resolution">Resolution Details</x-form-label>
                        <textarea 
                            id="resolution" 
                            name="resolution" 
                            rows="4" 
                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-neon-accent focus:border-transparent"
                            placeholder="Explain your decision and the reasoning behind it..."
                            required>{{ old('resolution') }}</textarea>
                        <x-form-error name="resolution" />
                    </x-form-field>
                </div>

                <div class="mb-6">
                    <x-form-field>
                        <x-form-label for="action">Action to Take</x-form-label>
                        <select 
                            id="action" 
                            name="action" 
                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-neon-accent focus:border-transparent"
                            required>
                            <option value="">Choose an action...</option>
                            <option value="approve">Approve Submission (Transfer Credits)</option>
                            <option value="decline">Decline Submission (No Credit Transfer)</option>
                            <option value="unfreeze">Unfreeze Only (Allow Parties to Resolve)</option>
                        </select>
                        <x-form-error name="action" />
                    </x-form-field>
                </div>

                <div class="bg-yellow-500/10 border border-yellow-500 rounded-md p-4 mb-6">
                    <p class="text-yellow-200 text-sm">
                        <strong>Actions Explained:</strong><br>
                        • <strong>Approve:</strong> Credits will be transferred from job poster to worker<br>
                        • <strong>Decline:</strong> No credit transfer, submission marked as declined<br>
                        • <strong>Unfreeze:</strong> Removes freeze, allows parties to continue negotiation
                    </p>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('disputes.index') }}" 
                       class="px-4 py-2 text-gray-300 hover:text-white border border-gray-600 rounded-md hover:bg-gray-800 transition-colors duration-200">
                        Cancel
                    </a>
                    <x-form-button type="submit" class="bg-green-600 hover:bg-green-700 text-white">
                        Resolve Dispute
                    </x-form-button>
                </div>
            </form>
        @else
            <div class="bg-green-900/20 border border-green-700 rounded-lg p-6">
                <h2 class="text-xl font-bold text-green-200 mb-4">Dispute Resolved</h2>
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-green-200 mb-2">Resolution</h3>
                    <p class="text-green-100 bg-green-900/30 p-4 rounded border border-green-600">
                        {{ $submission->dispute_resolution }}
                    </p>
                </div>
                <div class="text-sm text-gray-300">
                    Resolved by {{ $submission->disputeResolver->first_name }} {{ $submission->disputeResolver->last_name }} 
                    @if($submission->dispute_resolved_at)
                        on {{ $submission->dispute_resolved_at->format('M j, Y \a\t g:i A') }}
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-layout>
