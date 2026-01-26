<x-layout>
    <x-slot:heading>Admin Dashboard</x-slot:heading>

    <div class="space-y-6">

        <!-- Admin stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="p-4 bg-gray-800/40 rounded-lg border border-gray-700">
                <div class="text-sm text-gray-400">Total users</div>
                <div class="text-2xl font-bold text-white/90">{{ number_format($totalUsers) }}</div>
                <div class="text-xs text-gray-400">New last 7d: {{ $newUsersWeek }}</div>
            </div>

            <div class="p-4 bg-gray-800/40 rounded-lg border border-gray-700">
                <div class="text-sm text-gray-400">Total jobs</div>
                <div class="text-2xl font-bold text-white/90">{{ number_format($totalJobs) }}</div>
                <div class="text-xs text-gray-400">Recent: {{ $recentJobs->count() }}</div>
            </div>

            <div class="p-4 bg-gray-800/40 rounded-lg border border-gray-700">
                <div class="text-sm text-gray-400">Submissions</div>
                <div class="text-2xl font-bold text-white/90">{{ number_format($totalSubmissions) }}</div>
                <div class="text-xs text-gray-400">Admin review: {{ $pendingAdmin }}</div>
            </div>

            <div class="p-4 bg-gray-800/40 rounded-lg border border-gray-700">
                <div class="text-sm text-gray-400">Contact messages</div>
                <div class="text-2xl font-bold text-white/90">{{ number_format($contactMessagesCount) }}</div>
                <div class="text-xs text-gray-400">View <a href="/admin/contact" class="text-neon-accent">contact</a></div>
            </div>

            <div class="p-4 bg-gray-800/40 rounded-lg border border-gray-700">
                <div class="text-sm text-gray-400">Recent signups</div>
                <div class="text-2xl font-bold text-white/90">{{ $recentSignups->count() }}</div>
                <div class="text-xs text-gray-400">Latest users</div>
            </div>

            <div class="p-4 bg-gray-800/40 rounded-lg border border-gray-700">
                <div class="text-sm text-gray-400">Recent transactions</div>
                <div class="text-2xl font-bold text-white/90">{{ $recentTransactions->count() }}</div>
                <div class="text-xs text-gray-400">Last {{ $recentTransactions->count() }}</div>
            </div>
        </div>

        <div class="flex justify-between items-center">
            <h3 class="text-xl font-semibold text-white/90">Submissions Requiring Admin Review</h3>
            <a href="/admin/contact" class="text-sm text-gray-400 hover:text-neon-accent">Contact Messages</a>
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
</x-layout>
