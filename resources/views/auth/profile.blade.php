<x-layout>
    <x-slot:heading>
        My Profile
    </x-slot:heading>

    <!-- main profile container with spacing between sections -->
    <div class="space-y-8">
        <!-- User Info Card -->
        <div class="bg-gray-800/40 backdrop-blur-sm p-8 rounded-lg border border-gray-700">
            <div class="flex items-center space-x-6">
                <div class="flex-shrink-0">
                    <!-- profile avatar showing first letter of first and last name -->
                    <div class="w-24 h-24 rounded-full bg-gray-700 flex items-center justify-center text-3xl text-white/90">
                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                    </div>
                </div>
                <div>
                    <h2 class="text-2xl font-semibold text-white/90">{{ $user->first_name }} {{ $user->last_name }}</h2>
                    <p class="text-gray-400">{{ $user->email }}</p>
                    <div class="mt-4 inline-flex items-center px-4 py-2 rounded-full bg-gray-900/60 border border-gray-700">
                        <span class="text-neon-accent font-medium">{{ $user->time_credits }}</span>
                        <span class="ml-2 text-gray-400">Time Credits</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Section -->
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-white/90">My Services</h3>
                <x-button href="/jobs/create" class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300">
                    Create New Service
                </x-button>
            </div>

            @if($services->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($services as $service)
                        <a href="/jobs/{{ $service->id }}" class="block group">
                            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 group-hover:border-neon-accent transition-colors duration-300">
                                <h4 class="text-lg font-medium text-white/90 group-hover:text-neon-accent transition-colors duration-300">{{ $service->title }}</h4>
                                <p class="mt-2 text-gray-400 line-clamp-3">{{ $service->description }}</p>
                                <div class="mt-4 flex justify-between items-center">
                                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-gray-900/60 border border-gray-700">
                                        <span class="text-neon-accent font-medium">{{ $service->time_credits }}</span>
                                        <span class="ml-2 text-gray-400 text-sm">Credits</span>
                                    </div>
                                    <span class="text-sm text-gray-500">{{ $service->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <span class="text-neon-accent/80 text-sm font-medium group-hover:text-neon-accent transition-colors duration-300 flex items-center">
                                        View Details
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-gray-800/40 backdrop-blur-sm rounded-lg border border-gray-700">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-400">No services yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new service.</p>
                    <div class="mt-6">
                        <x-button href="/jobs/create" class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300">
                            Create New Service
                        </x-button>
                    </div>
                </div>
            @endif
        </div>

        <!-- My Applications Section -->
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-white/90">My Applications</h3>
                <a href="/jobs" class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300 px-4 py-2 rounded-md text-sm font-medium">
                    Browse Help Requests
                </a>
            </div>
            
            @if($sentSubmissions->count() > 0)
                <div class="space-y-4">
                    @foreach($sentSubmissions as $submission)
                        <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-medium text-white/90">{{ $submission->jobListing->title }}</h4>
                                    <p class="text-gray-400 text-sm mt-1">Posted by {{ $submission->jobListing->user->first_name }} {{ $submission->jobListing->user->last_name }}</p>
                                </div>
                                <div>
                                    @if($submission->status === 'claimed')
                                        <span class="bg-blue-500/20 text-blue-300 px-3 py-1 rounded-full text-xs font-semibold">Claimed</span>
                                    @elseif($submission->status === 'pending')
                                        <span class="bg-yellow-500/20 text-yellow-300 px-3 py-1 rounded-full text-xs font-semibold">Pending</span>
                                    @elseif($submission->status === 'approved')
                                        <span class="bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-xs font-semibold">Approved</span>
                                    @elseif($submission->status === 'declined')
                                        <span class="bg-red-500/20 text-red-300 px-3 py-1 rounded-full text-xs font-semibold">Declined</span>
                                    @elseif($submission->status === 'admin_review')
                                        <span class="bg-purple-500/20 text-purple-300 px-3 py-1 rounded-full text-xs font-semibold">Admin Review</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($submission->status === 'claimed')
                                <div class="mt-3 text-gray-300">
                                    <p class="italic">You have claimed this help request. Please complete your application.</p>
                                </div>
                                <div class="mt-4">
                                    <a href="/jobs/{{ $submission->job_listing_id }}" class="text-neon-accent hover:text-neon-accent/80 transition-colors duration-200 text-sm font-medium">
                                        Complete Application
                                    </a>
                                </div>
                            @else
                                <div class="mt-3 text-gray-300 text-sm">
                                    <p>{{ Str::limit($submission->message, 100) }}</p>
                                </div>
                                <div class="mt-4">
                                    <a href="/submissions/{{ $submission->id }}" class="text-neon-accent hover:text-neon-accent/80 transition-colors duration-200 text-sm font-medium">
                                        View Details
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-gray-800/40 backdrop-blur-sm rounded-lg border border-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-400">No applications yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Browse help requests and offer your assistance.</p>
                    <div class="mt-6">
                        <a href="/jobs" class="inline-flex items-center px-4 py-2 border border-gray-700 rounded-md shadow-sm text-sm font-medium text-gray-300 bg-gray-900/60 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neon-accent">
                            Browse Help Requests
                        </a>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Received Applications Section -->
        <div class="space-y-6">
            <h3 class="text-xl font-semibold text-white/90">Applications to My Help Requests</h3>
            
            @if($receivedSubmissions->count() > 0)
                <div class="space-y-4">
                    @foreach($receivedSubmissions as $submission)
                        <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-medium text-white/90">{{ $submission->jobListing->title }}</h4>
                                    <p class="text-neon-accent text-sm mt-1">{{ $submission->user->first_name }} {{ $submission->user->last_name }} wants to help</p>
                                </div>
                                <div>
                                    @if($submission->status === 'claimed')
                                        <span class="bg-blue-500/20 text-blue-300 px-3 py-1 rounded-full text-xs font-semibold">Claimed</span>
                                    @elseif($submission->status === 'pending')
                                        <span class="bg-yellow-500/20 text-yellow-300 px-3 py-1 rounded-full text-xs font-semibold">Pending</span>
                                    @elseif($submission->status === 'approved')
                                        <span class="bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-xs font-semibold">Approved</span>
                                    @elseif($submission->status === 'declined')
                                        <span class="bg-red-500/20 text-red-300 px-3 py-1 rounded-full text-xs font-semibold">Declined</span>
                                    @elseif($submission->status === 'admin_review')
                                        <span class="bg-purple-500/20 text-purple-300 px-3 py-1 rounded-full text-xs font-semibold">Admin Review</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($submission->status === 'claimed')
                                <div class="mt-3 text-gray-300">
                                    <p class="italic">{{ $submission->user->first_name }} has claimed this help request but hasn't completed their application yet.</p>
                                </div>
                            @else
                                <div class="mt-3 text-gray-300 text-sm">
                                    <p>{{ Str::limit($submission->message, 100) }}</p>
                                </div>
                            @endif
                            
                            <div class="mt-4">
                                <a href="/submissions/{{ $submission->id }}" class="text-neon-accent hover:text-neon-accent/80 transition-colors duration-200 text-sm font-medium">
                                    View full application
                                </a>
                                
                                @if($submission->status === 'pending')
                                    <div class="mt-3 flex space-x-2">
                                        <form method="POST" action="/submissions/{{ $submission->id }}/approve">
                                            @csrf
                                            <button type="submit" class="bg-green-700 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200">
                                                Approve
                                            </button>
                                        </form>
                                        
                                        <button type="button" onclick="document.getElementById('declineForm-{{ $submission->id }}').classList.remove('hidden')" class="bg-red-700 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200">
                                            Decline
                                        </button>
                                        
                                        <form method="POST" action="/submissions/{{ $submission->id }}/decline" id="declineForm-{{ $submission->id }}" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
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
                                                    <button type="button" onclick="document.getElementById('declineForm-{{ $submission->id }}').classList.add('hidden')" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="bg-red-700 hover:bg-red-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                                                        Submit & Decline
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-gray-800/40 backdrop-blur-sm rounded-lg border border-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-400">No applications received yet</h3>
                    <p class="mt-1 text-sm text-gray-500">When someone applies to help with your requests, they'll appear here.</p>
                </div>
            @endif
        </div>
        
        @if(auth()->user()->isAdmin())
        <!-- Admin Review Panel -->
        <div class="space-y-6 mb-8">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-white/90">Admin Review Panel</h3>
                <span class="bg-purple-500/20 text-purple-300 px-3 py-1 rounded-full text-xs font-semibold">Admin Only</span>
            </div>
            
            @if($adminReviewSubmissions->count() > 0)
                <div class="space-y-4">
                    @foreach($adminReviewSubmissions as $submission)
                        <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-medium text-white/90">{{ $submission->jobListing->title }}</h4>
                                    <div class="flex space-x-2 mt-1">
                                        <p class="text-gray-400 text-sm">Posted by: <span class="text-gray-300">{{ $submission->jobListing->user->first_name }} {{ $submission->jobListing->user->last_name }}</span></p>
                                        <p class="text-gray-400 text-sm">Applicant: <span class="text-gray-300">{{ $submission->user->first_name }} {{ $submission->user->last_name }}</span></p>
                                    </div>
                                    <p class="text-gray-400 text-sm mt-1">Credits: <span class="text-neon-accent">{{ $submission->jobListing->credits }}</span></p>
                                </div>
                                <span class="bg-purple-500/20 text-purple-300 px-3 py-1 rounded-full text-xs font-semibold">Admin Review</span>
                            </div>
                            
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h5 class="text-sm font-semibold text-white/90 mb-2">Application Message:</h5>
                                    <div class="bg-gray-900/60 p-3 rounded text-gray-300 text-sm">
                                        {{ $submission->message }}
                                    </div>
                                </div>
                                <div>
                                    <h5 class="text-sm font-semibold text-white/90 mb-2">Decline Reason:</h5>
                                    <div class="bg-gray-900/60 p-3 rounded text-gray-300 text-sm">
                                        {{ $submission->admin_notes }}
                                    </div>
                                </div>
                            </div>
                            
                            @if($submission->files->count() > 0)
                                <div class="mt-4">
                                    <h5 class="text-sm font-semibold text-white/90 mb-2">Attached Files:</h5>
                                    <div class="space-y-2">
                                        @foreach($submission->files as $file)
                                            <div class="flex items-center justify-between bg-gray-900/60 p-3 rounded">
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            
                            <div class="mt-6 flex space-x-3">
                                <!-- admin action buttons for submission approval/rejection -->
                                <form method="POST" action="/admin/submissions/{{ $submission->id }}/approve">
                                    @csrf
                                    <button type="submit" class="bg-green-700 hover:bg-green-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                                        Approve & Transfer Credits to Applicant
                                    </button>
                                </form>
                                
                                <form method="POST" action="/admin/submissions/{{ $submission->id }}/reject">
                                    @csrf
                                    <button type="submit" class="bg-red-700 hover:bg-red-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                                        Reject & Return Credits to Poster
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-gray-800/40 backdrop-blur-sm rounded-lg border border-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-400">No submissions requiring admin review</h3>
                    <p class="mt-1 text-sm text-gray-500">When a job creator declines an application, it will appear here for your review.</p>
                </div>
            @endif
        </div>
        @endif
        
        <!-- transaction history -->
        <div class="space-y-6">
            <h3 class="text-xl font-semibold text-white/90">Transaction History</h3>
            <div class="bg-gray-800/40 backdrop-blur-sm rounded-lg border border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-900/60">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Credits</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @forelse($transactions ?? [] as $transaction)
                                @if(!is_object($transaction))
                                    @continue
                                @endif
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                        {{ $transaction->created_at?->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                        {{ $transaction->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="{{ $transaction->amount > 0 ? 'text-green-400' : 'text-red-400' }}">
                                            {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-400">
                                        No transactions yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layout>