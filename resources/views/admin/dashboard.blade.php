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

        <!-- Site Activity & Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">Recent Activity</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">New users (7 days)</span>
                        <span class="text-white font-medium">{{ $newUsersWeek }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Active disputes</span>
                        <span class="text-yellow-200 font-medium">{{ \App\Models\JobSubmission::where('dispute_status', '!=', \App\Models\JobSubmission::DISPUTE_NONE)->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Pending reviews</span>
                        <span class="text-blue-200 font-medium">{{ \App\Models\JobSubmission::where('status', \App\Models\JobSubmission::STATUS_ADMIN_REVIEW)->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Unread messages</span>
                        <span class="text-green-200 font-medium">{{ \App\Models\Message::whereNull('read_at')->count() }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">Platform Health</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-neon-accent">{{ number_format($totalJobs / max($totalUsers, 1), 1) }}</div>
                        <div class="text-sm text-gray-400">Jobs per User</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-neon-accent">{{ number_format($totalSubmissions / max($totalJobs, 1), 1) }}</div>
                        <div class="text-sm text-gray-400">Submissions per Job</div>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-600">
                    <div class="text-sm text-gray-400">
                        <div class="mb-2"><strong>Quick Stats:</strong></div>
                        <div>• Total credits in system: {{ \App\Models\User::sum('time_credits') }}</div>
                        @php
    $earliestJobDate = \App\Models\Job::min('created_at');
    $daysSinceFirstJob = $earliestJobDate ? now()->diffInDays(\Carbon\Carbon::parse($earliestJobDate)) + 1 : 1;
    $avgSubmissionsPerDay = $totalSubmissions / max(1, $daysSinceFirstJob);
    $completionRate = $totalSubmissions > 0 ? (\App\Models\JobSubmission::where('status', 'approved')->count() / $totalSubmissions) * 100 : 0;
@endphp
                        <div>• Average submissions per day: {{ number_format($avgSubmissionsPerDay, 1) }}</div>
                        <div>• Completion rate: {{ number_format($completionRate, 1) }}%</div>
                    </div>
                </div>
            </div>
        </div>

        @if($adminAttentionItems->count() > 0)
            <div class="space-y-4">
                @foreach($adminAttentionItems as $item)
                    <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <h4 class="text-lg font-medium text-white/90 mb-2">
                                    {{ $item->jobListing->title }}
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-400">Job Poster:</span>
                                        <a href="{{ route('people.show', $item->jobListing->user->id) }}" class="text-white ml-2 hover:text-neon-accent transition-colors duration-200">
                                            {{ $item->jobListing->user->first_name }} {{ $item->jobListing->user->last_name }}
                                        </a>
                                    </div>
                                    <div>
                                        <span class="text-gray-400">Worker:</span>
                                        <a href="{{ route('people.show', $item->user->id) }}" class="text-white ml-2 hover:text-neon-accent transition-colors duration-200">
                                            {{ $item->user->first_name }} {{ $item->user->last_name }}
                                        </a>
                                    </div>
                                    <div>
                                        <span class="text-gray-400">Type:</span>
                                        @if($item->status === \App\Models\JobSubmission::STATUS_ADMIN_REVIEW)
                                            <span class="text-white ml-2">Admin Review (Decline)</span>
                                        @elseif($item->dispute_status === \App\Models\JobSubmission::DISPUTE_REQUESTED)
                                            <span class="text-white ml-2">Manual Dispute</span>
                                        @elseif($item->dispute_status === \App\Models\JobSubmission::DISPUTE_UNDER_REVIEW)
                                            <span class="text-white ml-2">Under Review</span>
                                        @elseif($item->dispute_status === \App\Models\JobSubmission::DISPUTE_RESOLVED)
                                            <span class="text-white ml-2">Resolved Dispute</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="ml-4">
                                <span class="inline-flex px-3 py-1 text-xs font-medium rounded-full
                                    @if($item->status === \App\Models\JobSubmission::STATUS_ADMIN_REVIEW)
                                        bg-purple-500/20 text-purple-200 border border-purple-500
                                    @elseif($item->dispute_status === \App\Models\JobSubmission::DISPUTE_REQUESTED)
                                        bg-yellow-500/20 text-yellow-200 border border-yellow-500
                                    @elseif($item->dispute_status === \App\Models\JobSubmission::DISPUTE_UNDER_REVIEW)
                                        bg-blue-500/20 text-blue-200 border border-blue-500
                                    @elseif($item->dispute_status === \App\Models\JobSubmission::DISPUTE_RESOLVED)
                                        bg-green-500/20 text-green-200 border border-green-500
                                    @endif
                                ">
                                    @if($item->status === \App\Models\JobSubmission::STATUS_ADMIN_REVIEW)
                                        Admin Review
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $item->dispute_status)) }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            @if($item->status === \App\Models\JobSubmission::STATUS_ADMIN_REVIEW)
                                <p class="text-gray-300 text-sm mb-2"><strong>Admin Review Details:</strong></p>
                                <p class="text-purple-200 bg-purple-900/20 p-3 rounded border border-purple-600">
                                    {{ $item->admin_notes }}
                                </p>
                            @else
                                <p class="text-gray-300 text-sm mb-2"><strong>Dispute Reason:</strong></p>
                                <p class="text-gray-200 bg-gray-800/40 p-3 rounded border border-gray-600">
                                    {{ $item->dispute_reason }}
                                </p>
                            @endif
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('disputes.show', $item) }}" 
                               class="px-4 py-2 bg-neon-accent text-black font-medium rounded hover:bg-neon-accent/80 transition-colors duration-200">
                                Review & Resolve
                            </a>
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
