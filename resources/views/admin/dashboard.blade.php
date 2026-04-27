<x-layout>
    <x-slot:heading>Admin panelis</x-slot:heading>

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-white/90">Pārskats</h2>
                <p class="mt-1 text-sm text-gray-400">Platformas aktivitāte, lietotāji, kredīti un jaunākie notikumi.</p>
            </div>
            <a href="{{ route('disputes.index') }}" class="inline-flex items-center justify-center rounded-md border border-neon-accent/50 px-4 py-2 text-sm font-medium text-neon-accent transition-colors hover:bg-neon-accent hover:text-black">
                Atvērt strīdus
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-4">
                <div class="text-sm text-gray-400">Lietotāji</div>
                <div class="mt-1 text-3xl font-semibold text-white/90">{{ number_format($totalUsers) }}</div>
                <div class="mt-2 text-xs text-green-300">+{{ $newUsersWeek }} pēdējās 7 dienās</div>
            </div>
            <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-4">
                <div class="text-sm text-gray-400">Vakances</div>
                <div class="mt-1 text-3xl font-semibold text-white/90">{{ number_format($totalJobs) }}</div>
                <div class="mt-2 text-xs text-green-300">+{{ $newJobsWeek }} pēdējās 7 dienās</div>
            </div>
            <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-4">
                <div class="text-sm text-gray-400">Pieteikumi</div>
                <div class="mt-1 text-3xl font-semibold text-white/90">{{ number_format($totalSubmissions) }}</div>
                <div class="mt-2 text-xs text-gray-400">{{ number_format($completionRate, 1) }}% apstiprināti</div>
            </div>
            <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-4">
                <div class="text-sm text-gray-400">Laika kredīti sistēmā</div>
                <div class="mt-1 text-3xl font-semibold text-neon-accent">{{ number_format($totalCredits) }}</div>
                <div class="mt-2 text-xs {{ $creditMovement30Days >= 0 ? 'text-green-300' : 'text-red-300' }}">{{ $creditMovement30Days >= 0 ? '+' : '' }}{{ $creditMovement30Days }} pēd. 30d transakcijās</div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('disputes.index') }}" class="rounded-lg border border-yellow-500/30 bg-yellow-500/10 p-4 transition-colors hover:border-yellow-400">
                <div class="text-sm text-yellow-200">Aktīvi strīdi</div>
                <div class="mt-1 text-2xl font-semibold text-white/90">{{ $activeDisputes }}</div>
                <div class="mt-2 text-xs text-gray-400">{{ $resolvedDisputes }} atrisināti kopā</div>
            </a>
            <a href="{{ route('disputes.index') }}" class="rounded-lg border border-purple-500/30 bg-purple-500/10 p-4 transition-colors hover:border-purple-400">
                <div class="text-sm text-purple-200">Admin pārskatīšana</div>
                <div class="mt-1 text-2xl font-semibold text-white/90">{{ $pendingAdmin }}</div>
                <div class="mt-2 text-xs text-gray-400">Atsevišķi strīdu lapā</div>
            </a>
            <a href="{{ route('admin.contact') }}" class="rounded-lg border border-gray-700 bg-gray-800/40 p-4 transition-colors hover:border-neon-accent">
                <div class="text-sm text-gray-400">Kontaktziņojumi</div>
                <div class="mt-1 text-2xl font-semibold text-white/90">{{ $contactMessagesCount }}</div>
                <div class="mt-2 text-xs text-neon-accent">Skatīt kontaktziņas</div>
            </a>
            <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-4">
                <div class="text-sm text-gray-400">Neizlasīti ziņojumi</div>
                <div class="mt-1 text-2xl font-semibold text-white/90">{{ $unreadMessagesCount }}</div>
                <div class="mt-2 text-xs text-gray-400">Visās sarunās</div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <section class="rounded-lg border border-gray-700 bg-gray-800/40 p-6 lg:col-span-2">
                <h3 class="text-lg font-semibold text-white/90">Pieteikumu statuss</h3>
                <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-4">
                    <div class="rounded-lg border border-blue-500/20 bg-blue-500/10 p-4">
                        <div class="text-2xl font-semibold text-blue-200">{{ $claimedSubmissions }}</div>
                        <div class="mt-1 text-xs text-gray-400">Saņemti</div>
                    </div>
                    <div class="rounded-lg border border-yellow-500/20 bg-yellow-500/10 p-4">
                        <div class="text-2xl font-semibold text-yellow-200">{{ $pendingSubmissions }}</div>
                        <div class="mt-1 text-xs text-gray-400">Gaida lēmumu</div>
                    </div>
                    <div class="rounded-lg border border-green-500/20 bg-green-500/10 p-4">
                        <div class="text-2xl font-semibold text-green-200">{{ $approvedSubmissions }}</div>
                        <div class="mt-1 text-xs text-gray-400">Apstiprināti</div>
                    </div>
                    <div class="rounded-lg border border-red-500/20 bg-red-500/10 p-4">
                        <div class="text-2xl font-semibold text-red-200">{{ $declinedSubmissions }}</div>
                        <div class="mt-1 text-xs text-gray-400">Noraidīti</div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg border border-gray-700 bg-gray-950/35 p-4 text-center">
                        <div class="text-2xl font-semibold text-neon-accent">{{ number_format($jobsPerUser, 1) }}</div>
                        <div class="mt-1 text-xs text-gray-400">Vakances uz lietotāju</div>
                    </div>
                    <div class="rounded-lg border border-gray-700 bg-gray-950/35 p-4 text-center">
                        <div class="text-2xl font-semibold text-neon-accent">{{ number_format($submissionsPerJob, 1) }}</div>
                        <div class="mt-1 text-xs text-gray-400">Pieteikumi uz vakanci</div>
                    </div>
                    <div class="rounded-lg border border-gray-700 bg-gray-950/35 p-4 text-center">
                        <div class="text-2xl font-semibold text-neon-accent">{{ number_format($averageRating, 1) }}</div>
                        <div class="mt-1 text-xs text-gray-400">Vidējais vērtējums no {{ $reviewsCount }} atsauksmēm</div>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-gray-700 bg-gray-800/40 p-6">
                <h3 class="text-lg font-semibold text-white/90">Populārās kategorijas</h3>
                <div class="mt-5 space-y-3">
                    @forelse($topCategories as $category)
                        <div>
                            <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                                <span class="truncate text-gray-300">{{ $category->category }}</span>
                                <span class="text-gray-400">{{ $category->total }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-gray-900">
                                <div class="h-full rounded-full bg-neon-accent" style="width: {{ min(100, ($category->total / max($totalJobs, 1)) * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-400">Vēl nav kategoriju datu.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <section class="rounded-lg border border-gray-700 bg-gray-800/40 p-6">
                <h3 class="text-lg font-semibold text-white/90">Jaunākie lietotāji</h3>
                <div class="mt-4 space-y-3">
                    @foreach($recentSignups as $user)
                        <a href="{{ route('people.show', $user) }}" class="flex items-center gap-3 rounded-md border border-gray-700 bg-gray-950/30 p-3 transition-colors hover:border-neon-accent">
                            <x-avatar :user="$user" size="sm" />
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-medium text-white/90">{{ $user->first_name }} {{ $user->last_name }}</div>
                                <div class="truncate text-xs text-gray-400">{{ $user->email }}</div>
                            </div>
                            <div class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</div>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg border border-gray-700 bg-gray-800/40 p-6">
                <h3 class="text-lg font-semibold text-white/90">Jaunākās transakcijas</h3>
                <div class="mt-4 space-y-3">
                    @forelse($recentTransactions as $transaction)
                        <div class="rounded-md border border-gray-700 bg-gray-950/30 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate text-sm text-white/90">{{ $transaction->description }}</div>
                                    <div class="mt-1 text-xs text-gray-400">{{ $transaction->user?->first_name }} {{ $transaction->user?->last_name }} · {{ $transaction->created_at->translatedFormat('j. M Y, H:i') }}</div>
                                </div>
                                <div class="shrink-0 text-sm font-semibold {{ $transaction->amount >= 0 ? 'text-green-300' : 'text-red-300' }}">
                                    {{ $transaction->amount >= 0 ? '+' : '' }}{{ $transaction->amount }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-400">Transakciju vēl nav.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-layout>
