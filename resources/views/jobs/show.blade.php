<x-layout>
    <x-slot:heading>
        Help Request
    </x-slot:heading>

    <div class="max-w-3xl mx-auto">
        <div class="bg-gray-800/40 backdrop-blur-sm p-8 rounded-lg border border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="text-neon-accent text-sm font-medium">{{ $job->user->first_name }} {{ $job->user->last_name }} needs help</div>
                    <h2 class="font-bold text-xl text-white/90 mt-1">{{ $job->title }}</h2>
                </div>
                <div class="bg-neon-accent/20 text-neon-accent px-4 py-2 rounded-full text-sm font-semibold">
                    {{ $job->time_credits }} time credits
                </div>
            </div>
            
            <div class="mb-4">
                <span class="text-gray-400 text-sm">Category: </span>
                <span class="text-white/80 text-sm capitalize">{{ $job->category }}</span>
            </div>
            
            <div class="border-t border-gray-700 pt-4 mt-4">
                <h3 class="font-semibold text-white/90 mb-2">Description</h3>
                <div class="text-gray-300 whitespace-pre-line">{{ $job->description }}</div>
            </div>
            
            @if(auth()->check() && auth()->id() !== $job->user_id)
                <div class="border-t border-gray-700 pt-6 mt-6">
                    <h3 class="font-semibold text-white/90 mb-4">Want to help?</h3>
                    
                    @php
                        $userSubmission = \App\Models\JobSubmission::where('job_listing_id', $job->id)
                            ->where('user_id', auth()->id())
                            ->first();
                            
                        $jobClaimed = \App\Models\JobSubmission::where('job_listing_id', $job->id)
                            ->whereIn('status', ['claimed', 'pending', 'approved'])
                            ->exists();
                    @endphp
                    
                    @if($jobClaimed && !$userSubmission)
                        <div class="bg-yellow-500/20 text-yellow-300 p-4 rounded-md mb-4">
                            <p>This help request has already been claimed by another user.</p>
                        </div>
                    @elseif(!$userSubmission)
                        <!-- Step 1: Claim the job -->
                        <form method="POST" action="{{ route('job-submissions.claim') }}">
                            @csrf
                            <input type="hidden" name="job_id" value="{{ $job->id }}">

                            <p class="text-gray-300 mb-4">To apply for this help request, you need to claim it first. This reserves the request for you while you prepare your application.</p>

                            <button type="submit" class="rounded-md px-4 py-2 text-sm font-medium text-gray-300 border border-gray-700 hover:text-neon-accent hover:bg-gray-800/80 transition-all duration-300">
                                Claim This Help Request
                            </button>
                        </form>
                    @elseif($userSubmission->status === 'claimed')
                        <!-- Step 2: Complete the application -->
                        <div class="bg-green-500/20 text-green-300 p-4 rounded-md mb-4">
                            <p>You have claimed this help request. Please complete your application below.</p>
                        </div>
                        
                        <div class="space-y-6">
                            <form method="POST" action="/job-submissions/{{ $userSubmission->id }}/cancel" id="cancel-form-{{ $userSubmission->id }}">
                                @csrf
                            </form>
                            
                            <form method="POST" action="/job-submissions/complete" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="submission_id" value="{{ $userSubmission->id }}">
                                
                                <div class="mb-4">
                                    <x-form-label for="message">Your message</x-form-label>
                                    <div class="mt-2">
                                        <textarea name="message" id="message" rows="4" required
                                            class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500"
                                            placeholder="Explain how you can help with this request">{{ old('message') }}</textarea>
                                        @error('message')
                                            <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <x-form-label for="files">Attach files (optional)</x-form-label>
                                    <div class="mt-2">
                                        <input type="file" name="files[]" id="files" multiple
                                            class="mt-1 block w-full text-gray-300"
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip,.gif,.mp4,.mp3,.avi,.psd,.ai,.sketch,.xd,.fig">
                                        <p class="mt-1 text-sm text-gray-400">Upload relevant files to support your application (max 50MB each)</p>
                                        @error('files.*')
                                            <div class="mt-1 text-red-500 text-sm">{{ $message ?? 'Invalid file' }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="flex justify-between mt-6">
                                    <button type="button" onclick="document.getElementById('cancel-form-{{ $userSubmission->id }}').submit();" class="bg-red-700 hover:bg-red-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200 inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Cancel Claim
                                    </button>
                                    
                                    <x-form-button>
                                        Submit Application
                                    </x-form-button>
                                </div>
                            </form>
                        </div>
                    @elseif($userSubmission->status === 'pending')
                        <div class="bg-yellow-500/20 text-yellow-300 p-4 rounded-md">
                            <p>Your application has been submitted and is pending review.</p>
                        </div>
                    @elseif($userSubmission->status === 'approved')
                        <div class="bg-green-500/20 text-green-300 p-4 rounded-md">
                            <p>Congratulations! Your application has been approved.</p>
                        </div>
                    @elseif($userSubmission->status === 'declined')
                        <div class="bg-red-500/20 text-red-300 p-4 rounded-md mb-4">
                            <p>Your application was declined. You can claim this help request again if you'd like to try again.</p>
                        </div>
                        
                        <form method="POST" action="{{ route('job-submissions.claim') }}">
                            @csrf
                            <input type="hidden" name="job_id" value="{{ $job->id }}">

                            <button type="submit" class="rounded-md px-4 py-2 text-sm font-medium text-gray-300 border border-gray-700 hover:text-neon-accent hover:bg-gray-800/80 transition-all duration-300">
                                Claim Again
                            </button>
                        </form>
                    @endif
                </div>
            @endif
            
            @can('edit-job', $job)
                <div class="border-t border-gray-700 pt-6 mt-6 flex justify-between">
                    <x-button href="/jobs/{{ $job->id }}/edit" class="bg-gray-700 hover:bg-gray-600">Edit Request</x-button>
                    
                    <form method="POST" action="/jobs/{{ $job->id }}" onsubmit="return confirm('Are you sure you want to delete this help request?')">
                        @csrf
                        @method('DELETE')
                        <x-form-button class="bg-red-800 hover:bg-red-700">Delete Request</x-form-button>
                    </form>
                </div>
            @endcan
        </div>
    </div>
</x-layout>
