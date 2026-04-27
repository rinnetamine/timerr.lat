<x-layout>
    <x-slot name="heading">Strīdu pārvaldība</x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Strīdi un admin pārskatīšana</h2>
                <p class="mt-1 text-gray-300">Pārskatiet vakances, pieteikumu tekstus, iesaistītos lietotājus un pievienotos failus.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="rounded-md border border-gray-700 px-4 py-2 text-sm font-medium text-gray-300 transition-colors hover:border-neon-accent hover:text-neon-accent">
                Atpakaļ uz paneli
            </a>
        </div>

        @if($disputes->count() === 0)
            <div class="rounded-lg border border-gray-700 bg-gray-900/60 p-8 text-center">
                <div class="text-lg text-gray-400">Nav aktīvu strīdu</div>
                <p class="mt-2 text-gray-500">Visi pieteikumi šobrīd darbojas gludi.</p>
            </div>
        @else
            <div class="space-y-5">
                @foreach($disputes as $dispute)
                    @php
                        $job = $dispute->jobListing;
                        $owner = $job?->user;
                        $worker = $dispute->user;
                        $typeLabel = $dispute->status === \App\Models\JobSubmission::STATUS_ADMIN_REVIEW
                            ? 'Admin pārskatīšana'
                            : match ($dispute->dispute_status) {
                                \App\Models\JobSubmission::DISPUTE_REQUESTED => 'Strīds pieteikts',
                                \App\Models\JobSubmission::DISPUTE_UNDER_REVIEW => 'Pārskatīšanā',
                                \App\Models\JobSubmission::DISPUTE_RESOLVED => 'Atrisināts',
                                default => $dispute->dispute_status,
                            };
                    @endphp

                    <article class="overflow-hidden rounded-lg border border-gray-700 bg-gray-900/60">
                        <div class="grid gap-0 lg:grid-cols-[260px_1fr]">
                            <div class="border-b border-gray-700 bg-gray-950/35 lg:border-b-0 lg:border-r">
                                @if($job)
                                    <img src="{{ $job->imageUrl() }}" alt="{{ $job->title }}" class="h-44 w-full object-cover">
                                @else
                                    <div class="flex h-44 items-center justify-center bg-gray-800 text-gray-500">Vakance nav pieejama</div>
                                @endif
                                <div class="p-4">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-medium
                                        @if($dispute->status === \App\Models\JobSubmission::STATUS_ADMIN_REVIEW)
                                            border-purple-500 bg-purple-500/20 text-purple-200
                                        @elseif($dispute->dispute_status === \App\Models\JobSubmission::DISPUTE_REQUESTED)
                                            border-yellow-500 bg-yellow-500/20 text-yellow-200
                                        @elseif($dispute->dispute_status === \App\Models\JobSubmission::DISPUTE_UNDER_REVIEW)
                                            border-blue-500 bg-blue-500/20 text-blue-200
                                        @else
                                            border-green-500 bg-green-500/20 text-green-200
                                        @endif">
                                        {{ $typeLabel }}
                                    </span>
                                    <div class="mt-3 text-sm text-gray-400">{{ $job?->time_credits ?? 0 }} kredīti · {{ $job?->category ?? 'Bez kategorijas' }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ $dispute->created_at->translatedFormat('j. M Y, H:i') }}</div>
                                </div>
                            </div>

                            <div class="space-y-5 p-5">
                                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                    <div>
                                        <h3 class="text-xl font-semibold text-white/90">{{ $job?->title ?? 'Vakance nav pieejama' }}</h3>
                                        <p class="mt-2 line-clamp-3 text-sm text-gray-300">{{ $job?->description ?? 'Vakances apraksts nav pieejams.' }}</p>
                                    </div>
                                    @if($job)
                                        <a href="{{ url('/jobs/' . $job->id) }}" class="shrink-0 rounded-md border border-gray-700 px-3 py-2 text-sm font-medium text-gray-300 transition-colors hover:border-neon-accent hover:text-neon-accent">
                                            Skatīt vakanci
                                        </a>
                                    @endif
                                </div>

                                <div class="grid gap-3 md:grid-cols-2">
                                    <div class="rounded-lg border border-gray-700 bg-gray-950/30 p-4">
                                        <div class="text-xs uppercase tracking-wide text-gray-500">Darba publicētājs</div>
                                        @if($owner)
                                            <a href="{{ route('people.show', $owner->id) }}" class="mt-2 inline-flex items-center gap-2 text-white hover:text-neon-accent">
                                                <x-avatar :user="$owner" size="sm" />
                                                <span>{{ $owner->first_name }} {{ $owner->last_name }}</span>
                                            </a>
                                            <div class="mt-1 text-xs text-gray-500">{{ $owner->email }}</div>
                                        @else
                                            <div class="mt-2 text-sm text-gray-400">Nav pieejams</div>
                                        @endif
                                    </div>
                                    <div class="rounded-lg border border-gray-700 bg-gray-950/30 p-4">
                                        <div class="text-xs uppercase tracking-wide text-gray-500">Pieteicējs</div>
                                        @if($worker)
                                            <a href="{{ route('people.show', $worker->id) }}" class="mt-2 inline-flex items-center gap-2 text-white hover:text-neon-accent">
                                                <x-avatar :user="$worker" size="sm" />
                                                <span>{{ $worker->first_name }} {{ $worker->last_name }}</span>
                                            </a>
                                            <div class="mt-1 text-xs text-gray-500">{{ $worker->email }}</div>
                                        @else
                                            <div class="mt-2 text-sm text-gray-400">Nav pieejams</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="grid gap-3 lg:grid-cols-2">
                                    <div class="rounded-lg border border-gray-700 bg-gray-950/30 p-4">
                                        <div class="text-sm font-medium text-white/90">Pieteikuma ziņojums</div>
                                        <p class="mt-2 whitespace-pre-line text-sm text-gray-300">{{ $dispute->message ?: 'Pieteikumam nav apraksta.' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-700 bg-gray-950/30 p-4">
                                        <div class="text-sm font-medium text-white/90">{{ $dispute->status === \App\Models\JobSubmission::STATUS_ADMIN_REVIEW ? 'Admin piezīmes' : 'Strīda iemesls' }}</div>
                                        <p class="mt-2 whitespace-pre-line text-sm text-gray-300">{{ $dispute->status === \App\Models\JobSubmission::STATUS_ADMIN_REVIEW ? ($dispute->admin_notes ?: 'Nav piezīmju.') : ($dispute->dispute_reason ?: 'Nav norādīts iemesls.') }}</p>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-gray-700 bg-gray-950/30 p-4">
                                    <div class="mb-3 flex items-center justify-between">
                                        <div class="text-sm font-medium text-white/90">Pievienotie faili</div>
                                        <div class="text-xs text-gray-500">{{ $dispute->files->count() }} faili</div>
                                    </div>
                                    @if($dispute->files->isNotEmpty())
                                        <div class="grid gap-2 md:grid-cols-2">
                                            @foreach($dispute->files as $file)
                                                <a href="{{ route('files.download', $file) }}" class="flex items-center justify-between gap-3 rounded-md border border-gray-700 bg-gray-900/60 p-3 text-sm transition-colors hover:border-neon-accent">
                                                    <span class="min-w-0 truncate text-gray-300">{{ $file->file_name }}</span>
                                                    <span class="shrink-0 text-xs text-gray-500">{{ $file->file_size ? number_format($file->file_size / 1024, 1) . ' KB' : 'Fails' }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-500">Pieteikumam nav pievienotu failu.</div>
                                    @endif
                                </div>

                                <div class="flex justify-end">
                                    <a href="{{ route('disputes.show', $dispute) }}" class="rounded-md bg-neon-accent px-4 py-2 text-sm font-medium text-black transition-colors hover:bg-neon-accent/80">
                                        Atvērt pilnu pārskatu
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $disputes->links() }}
            </div>
        @endif
    </div>
</x-layout>
