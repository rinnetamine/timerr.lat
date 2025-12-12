<x-layout>
    <x-slot:heading>
        Application Details
    </x-slot:heading>

    <div class="max-w-3xl mx-auto">
        <div class="bg-gray-800/40 backdrop-blur-sm p-8 rounded-lg border border-gray-700">
            <!-- help request info-->
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-white/90">{{ $submission->jobListing->title }}</h2>
                <p class="text-gray-400 text-sm mt-1">
                    Help request by {{ $submission->jobListing->user->first_name }} {{ $submission->jobListing->user->last_name }}
                </p>
                <div class="mt-2 flex items-center">
                    <span class="bg-neon-accent/20 text-neon-accent px-3 py-1 rounded-full text-xs font-semibold">
                        {{ $submission->jobListing->time_credits }} time credits
                    </span>
                    <span class="ml-3 text-gray-400 text-sm capitalize">
                        Category: {{ $submission->jobListing->category }}
                    </span>
                </div>
            </div>

            <!-- application Status -->
            <div class="border-t border-gray-700 pt-4 mb-6">
                <div class="flex items-center">
                    <span class="text-gray-400 text-sm">Status: </span>
                    @if($submission->status === 'claimed')
                        <span class="ml-2 bg-blue-500/20 text-blue-300 px-3 py-1 rounded-full text-xs font-semibold">Claimed</span>
                    @elseif($submission->status === 'pending')
                        <span class="ml-2 bg-yellow-500/20 text-yellow-300 px-3 py-1 rounded-full text-xs font-semibold">Pending</span>
                    @elseif($submission->status === 'approved')
                        <span class="ml-2 bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-xs font-semibold">Approved</span>
                    @elseif($submission->status === 'declined')
                        <span class="ml-2 bg-red-500/20 text-red-300 px-3 py-1 rounded-full text-xs font-semibold">Declined</span>
                    @elseif($submission->status === 'admin_review')
                        <span class="ml-2 bg-purple-500/20 text-purple-300 px-3 py-1 rounded-full text-xs font-semibold">Admin Review</span>
                    @endif
                </div>
            </div>

            <!-- Applicant Info -->
            <div class="border-t border-gray-700 pt-4 mb-6">
                <h3 class="font-semibold text-white/90 mb-2">Applicant</h3>
                <p class="text-gray-300">{{ $submission->user->first_name }} {{ $submission->user->last_name }}</p>
                <p class="text-gray-400 text-sm">{{ $submission->user->email }}</p>
            </div>

            <div class="border-t border-gray-700 pt-4 mb-6">
                <h3 class="font-semibold text-white/90 mb-2">Message</h3>
                <div class="text-gray-300 whitespace-pre-line">{{ $submission->message }}</div>
            </div>

            @if(auth()->check() && auth()->id() === $submission->user_id && $submission->status === 'claimed')
                <div class="border-t border-gray-700 pt-4 mb-6">
                    <h3 class="font-semibold text-white/90 mb-2">Complete Your Application</h3>

                    @if($errors->any())
                        <div class="bg-red-900/40 border border-red-700 p-3 rounded mb-4">
                            <ul class="text-sm text-red-200 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="/job-submissions/complete" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="submission_id" value="{{ $submission->id }}">

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Message to the job owner</label>
                            <textarea name="message" rows="6" required placeholder="Describe what you will do, timeframe, or attach any proof files..." class="w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent">{{ old('message') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Attach files (optional)</label>
                            <input type="file" name="files[]" multiple class="text-sm text-gray-300">
                            <p class="text-xs text-gray-500 mt-1">You can attach files to support your application. Max 50MB per file.</p>
                        </div>

                        <div class="flex justify-end">
                            <a href="/submissions" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium mr-3">Cancel</a>
                            <button type="submit" class="bg-neon-accent text-black px-4 py-2 rounded text-sm font-medium">Submit Application</button>
                        </div>
                    </form>
                </div>
            @endif
            
            <!-- admin notes -->
            @if($submission->status === 'admin_review' && $submission->admin_notes)
                <div class="border-t border-gray-700 pt-4 mb-6">
                    <h3 class="font-semibold text-white/90 mb-2">Admin Notes</h3>
                    <div class="bg-purple-500/10 border border-purple-500/30 p-4 rounded-md text-gray-300 whitespace-pre-line">
                        {{ $submission->admin_notes }}
                    </div>
                    <p class="mt-2 text-sm text-gray-400">An administrator will review this application and decide if the credits should be returned to the job owner or awarded to applicant.</p>
                </div>
            @endif

            <!-- attached files -->
            @if($submission->files->count() > 0)
                <div class="border-t border-gray-700 pt-4 mb-6">
                    <h3 class="font-semibold text-white/90 mb-2">Attached Files</h3>
                    <div class="space-y-2">
                        @foreach($submission->files as $file)
                            <div class="flex items-center justify-between bg-gray-900/60 p-3 rounded">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-gray-300 text-sm">{{ $file->file_name }}</span>
                                </div>
                                <a href="{{ route('file.download', $file->id) }}" class="text-neon-accent hover:text-neon-accent/80 text-sm font-medium">
                                    Download
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- actions -->
            @if(auth()->id() === $submission->jobListing->user_id && $submission->status === 'pending')
                <div class="border-t border-gray-700 pt-6 mt-6">
                    <div class="flex justify-between">
                        <a href="/submissions" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                            Back to Applications
                        </a>
                        <a href="{{ route('submissions.export', $submission->id) }}" class="ml-3 bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">Download HTML</a>
                        <div class="flex space-x-3">
                            <form method="POST" action="/submissions/{{ $submission->id }}/decline" id="declineForm" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
                                @csrf
                                <div class="bg-gray-800 p-6 rounded-lg max-w-md w-full">
                                    <h3 class="text-lg font-semibold text-white mb-4">Decline Application</h3>
                                    
                                    <p class="text-gray-300 mb-4">
                                        This application will be sent for admin review. Please provide a reason for declining:
                                    </p>
                                    
                                    <textarea name="admin_notes" rows="4" required
                                        class="w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500"
                                        placeholder="Explain why you're declining this application..."></textarea>
                                    
                                    <div class="mt-4 flex justify-end space-x-3">
                                        <button type="button" onclick="document.getElementById('declineForm').classList.add('hidden')" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                                            Cancel
                                        </button>
                                        <button type="submit" class="bg-red-700 hover:bg-red-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                                            Submit & Decline
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <button type="button" onclick="document.getElementById('declineForm').classList.remove('hidden')" class="bg-red-700 hover:bg-red-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                                Decline
                            </button>
                            
                            <form method="POST" action="/submissions/{{ $submission->id }}/approve">
                                @csrf
                                <button type="submit" class="bg-green-700 hover:bg-green-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                                    Approve and Transfer Credits
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <div class="border-t border-gray-700 pt-6 mt-6">
                    <div class="flex items-center space-x-3">
                        <a href="/submissions" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                            Back to Applications
                        </a>
                        <a href="{{ route('submissions.export', $submission->id) }}" class="ml-2 bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">Download HTML</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layout>
