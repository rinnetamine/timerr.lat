<x-layout>
    <x-slot name="heading">Atrisināt strīdu</x-slot>

    @php
        $job = $submission->jobListing;
        $owner = $job?->user;
        $worker = $submission->user;
        $statusLabels = [
            'claimed' => 'Saņemts',
            'pending' => 'Gaida',
            'approved' => 'Apstiprināts',
            'declined' => 'Noraidīts',
            'admin_review' => 'Administratora pārskatīšana',
        ];
    @endphp

    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-white/90">{{ $job?->title ?? 'Vakance nav pieejama' }}</h2>
                <p class="mt-1 text-sm text-gray-400">Pilns pārskats par vakanci, pieteikumu, failiem un strīda detaļām.</p>
            </div>
            <a href="{{ route('disputes.index') }}" class="rounded-md border border-gray-700 px-4 py-2 text-sm font-medium text-gray-300 transition-colors hover:border-neon-accent hover:text-neon-accent">
                Atpakaļ uz strīdiem
            </a>
        </div>

        <section class="overflow-hidden rounded-lg border border-gray-700 bg-gray-900/60">
            <div class="grid gap-0 lg:grid-cols-[360px_1fr]">
                <div class="border-b border-gray-700 bg-gray-950/35 lg:border-b-0 lg:border-r">
                    @if($job)
                        <x-job-image :job="$job" class="rounded-none border-0" />
                    @else
                        <div class="flex aspect-[4/3] items-center justify-center bg-gray-800 text-gray-500">Vakance nav pieejama</div>
                    @endif
                    <div class="space-y-3 p-5">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full border border-neon-accent/40 bg-neon-accent/10 px-3 py-1 text-xs font-medium text-neon-accent">{{ $job?->time_credits ?? 0 }} kredīti</span>
                            <span class="rounded-full border border-gray-700 bg-gray-900/60 px-3 py-1 text-xs text-gray-300">{{ $job?->category ?? 'Bez kategorijas' }}</span>
                            <span class="rounded-full border border-gray-700 bg-gray-900/60 px-3 py-1 text-xs text-gray-300">{{ $statusLabels[$submission->status] ?? $submission->status }}</span>
                        </div>
                        @if($job)
                            <a href="{{ url('/jobs/' . $job->id) }}" class="inline-flex text-sm font-medium text-neon-accent hover:text-neon-accent/80">
                                Skatīt publisko vakanci
                            </a>
                        @endif
                    </div>
                </div>

                <div class="space-y-6 p-6">
                    <div>
                        <h3 class="text-lg font-semibold text-white/90">Vakances apraksts</h3>
                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-300">{{ $job?->description ?? 'Vakances apraksts nav pieejams.' }}</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-lg border border-gray-700 bg-gray-950/35 p-4">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Darba publicētājs</div>
                            @if($owner)
                                <a href="{{ route('people.show', $owner->id) }}" class="mt-3 inline-flex items-center gap-3 text-white hover:text-neon-accent">
                                    <x-avatar :user="$owner" size="md" />
                                    <span>
                                        <span class="block font-medium">{{ $owner->first_name }} {{ $owner->last_name }}</span>
                                        <span class="block text-xs text-gray-500">{{ $owner->email }}</span>
                                    </span>
                                </a>
                            @else
                                <div class="mt-3 text-sm text-gray-400">Nav pieejams</div>
                            @endif
                        </div>

                        <div class="rounded-lg border border-gray-700 bg-gray-950/35 p-4">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Pieteicējs</div>
                            @if($worker)
                                <a href="{{ route('people.show', $worker->id) }}" class="mt-3 inline-flex items-center gap-3 text-white hover:text-neon-accent">
                                    <x-avatar :user="$worker" size="md" />
                                    <span>
                                        <span class="block font-medium">{{ $worker->first_name }} {{ $worker->last_name }}</span>
                                        <span class="block text-xs text-gray-500">{{ $worker->email }}</span>
                                    </span>
                                </a>
                            @else
                                <div class="mt-3 text-sm text-gray-400">Nav pieejams</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-lg border border-gray-700 bg-gray-900/60 p-6">
                <h3 class="text-lg font-semibold text-white/90">Pieteikuma apraksts</h3>
                <p class="mt-3 whitespace-pre-line rounded-lg border border-gray-700 bg-gray-950/35 p-4 text-sm leading-6 text-gray-300">
                    {{ $submission->message ?: 'Pieteikumam nav apraksta.' }}
                </p>
            </section>

            <section class="rounded-lg border border-gray-700 bg-gray-900/60 p-6">
                <h3 class="text-lg font-semibold text-white/90">{{ $submission->status === \App\Models\JobSubmission::STATUS_ADMIN_REVIEW ? 'Admin pārskatīšanas piezīmes' : 'Strīda informācija' }}</h3>
                <div class="mt-3 space-y-3">
                    <div class="rounded-lg border border-gray-700 bg-gray-950/35 p-4">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ $submission->status === \App\Models\JobSubmission::STATUS_ADMIN_REVIEW ? 'Admin piezīmes' : 'Strīda iemesls' }}</div>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-300">{{ $submission->status === \App\Models\JobSubmission::STATUS_ADMIN_REVIEW ? ($submission->admin_notes ?: 'Nav piezīmju.') : ($submission->dispute_reason ?: 'Nav norādīts iemesls.') }}</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-lg border border-gray-700 bg-gray-950/35 p-4">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Strīdu iesūtīja</div>
                            <div class="mt-2 text-sm text-gray-300">
                                @if($submission->disputeInitiator)
                                    {{ $submission->disputeInitiator->first_name }} {{ $submission->disputeInitiator->last_name }}
                                @else
                                    Nav pieejams
                                @endif
                            </div>
                        </div>
                        <div class="rounded-lg border border-gray-700 bg-gray-950/35 p-4">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Sasaldēts</div>
                            <div class="mt-2 text-sm {{ $submission->is_frozen ? 'text-red-300' : 'text-green-300' }}">{{ $submission->is_frozen ? 'Jā' : 'Nē' }}</div>
                        </div>
                    </div>
                    @if($submission->freeze_reason)
                        <div class="rounded-lg border border-red-500/30 bg-red-500/10 p-4">
                            <div class="text-xs uppercase tracking-wide text-red-200">Sasaldēšanas iemesls</div>
                            <p class="mt-2 whitespace-pre-line text-sm text-red-100">{{ $submission->freeze_reason }}</p>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <section class="rounded-lg border border-gray-700 bg-gray-900/60 p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white/90">Pieteikumam pievienotie faili</h3>
                <span class="rounded-full border border-gray-700 bg-gray-950/50 px-3 py-1 text-xs text-gray-400">{{ $submission->files->count() }} faili</span>
            </div>

            @if($submission->files->isNotEmpty())
                <div class="grid gap-3 md:grid-cols-2">
                    @foreach($submission->files as $file)
                        <a href="{{ route('files.download', $file) }}" class="flex items-center justify-between gap-4 rounded-lg border border-gray-700 bg-gray-950/35 p-4 transition-colors hover:border-neon-accent">
                            <div class="min-w-0">
                                <div class="truncate text-sm font-medium text-white/90">{{ $file->file_name }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $file->mime_type ?: 'Fails' }} · {{ $file->file_size ? number_format($file->file_size / 1024, 1) . ' KB' : 'Nezināms izmērs' }}</div>
                            </div>
                            <span class="shrink-0 rounded-md bg-neon-accent px-3 py-1.5 text-xs font-medium text-black">Lejupielādēt</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-lg border border-gray-700 bg-gray-950/35 p-6 text-sm text-gray-400">Pieteikumam nav pievienotu failu.</div>
            @endif
        </section>

        @if($submission->dispute_status !== \App\Models\JobSubmission::DISPUTE_RESOLVED)
            <form action="{{ route('disputes.resolve', $submission) }}" method="POST" class="rounded-lg border border-gray-700 bg-gray-900/60 p-6">
                @csrf
                <h3 class="text-lg font-semibold text-white/90">Atrisināt strīdu</h3>
                
                <div class="mt-4">
                    <x-form-field>
                        <x-form-label for="resolution">Atrisinājuma detaļas</x-form-label>
                        <textarea id="resolution" name="resolution" rows="4" class="w-full rounded-md border border-gray-600 bg-gray-800 px-3 py-2 text-white placeholder-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-neon-accent" placeholder="Paskaidrojiet savu lēmumu un tā pamatojumu..." required>{{ old('resolution') }}</textarea>
                        <x-form-error name="resolution" />
                    </x-form-field>
                </div>

                <div class="mt-4">
                    <x-form-field>
                        <x-form-label for="action">Darbība, ko jāveic</x-form-label>
                        <select id="action" name="action" class="w-full rounded-md border border-gray-600 bg-gray-800 px-3 py-2 text-white focus:border-transparent focus:outline-none focus:ring-2 focus:ring-neon-accent" required>
                            <option value="">Izvēlieties darbību...</option>
                            <option value="approve">Apstiprināt pieteikumu un pārsūtīt kredītus</option>
                            <option value="decline">Noraidīt pieteikumu bez kredītu pārsūtīšanas</option>
                            <option value="unfreeze">Atkausēt tikai un atļaut pusēm turpināt</option>
                        </select>
                        <x-form-error name="action" />
                    </x-form-field>
                </div>

                <div class="mt-5 rounded-md border border-yellow-500/50 bg-yellow-500/10 p-4 text-sm text-yellow-100">
                    Apstiprinot, kredīti tiks pārsūtīti pieteicējam. Noraidot, pieteikums paliek bez kredītu pārsūtīšanas. Atkausēšana noņem tikai sasaldēšanu.
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('disputes.index') }}" class="rounded-md border border-gray-600 px-4 py-2 text-gray-300 transition-colors hover:bg-gray-800 hover:text-white">Atcelt</a>
                    <x-form-button type="submit" class="bg-green-600 text-white hover:bg-green-700">Atrisināt strīdu</x-form-button>
                </div>
            </form>
        @else
            <section class="rounded-lg border border-green-700 bg-green-900/20 p-6">
                <h3 class="text-lg font-semibold text-green-200">Strīds atrisināts</h3>
                <p class="mt-3 whitespace-pre-line rounded border border-green-600 bg-green-900/30 p-4 text-green-100">{{ $submission->dispute_resolution }}</p>
                <div class="mt-4 text-sm text-gray-300">
                    Atrisināja {{ $submission->disputeResolver?->first_name }} {{ $submission->disputeResolver?->last_name }}
                    @if($submission->dispute_resolved_at)
                        {{ $submission->dispute_resolved_at->translatedFormat('j. M Y, H:i') }}
                    @endif
                </div>
            </section>
        @endif
    </div>
</x-layout>
