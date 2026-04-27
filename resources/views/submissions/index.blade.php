<x-layout>
    <x-slot:heading>
        Mani pieteikumi
    </x-slot:heading>

    @php
        $statusLabels = [
            'claimed' => 'Saņemts',
            'pending' => 'Gaida',
            'approved' => 'Apstiprināts',
            'declined' => 'Noraidīts',
            'admin_review' => 'Admin pārskatīšana',
        ];

        $statusClasses = [
            'claimed' => 'bg-blue-500/20 text-blue-300',
            'pending' => 'bg-yellow-500/20 text-yellow-300',
            'approved' => 'bg-green-500/20 text-green-300',
            'declined' => 'bg-red-500/20 text-red-300',
            'admin_review' => 'bg-purple-500/20 text-purple-300',
        ];

        $preserveExcept = function (array $except) {
            return collect(request()->except($except))->filter(fn ($value) => is_scalar($value) && $value !== '');
        };
    @endphp

    <div class="mx-auto max-w-6xl space-y-8">
        <section class="rounded-lg border border-gray-700 bg-gray-800/40 p-5 backdrop-blur-sm">
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <div class="text-2xl font-semibold text-white/90">{{ $receivedSubmissions->count() }}</div>
                    <div class="mt-1 text-sm text-gray-400">Saņemtie pieteikumi</div>
                </div>
                <div>
                    <div class="text-2xl font-semibold text-white/90">{{ $sentSubmissions->count() }}</div>
                    <div class="mt-1 text-sm text-gray-400">Mani iesniegtie pieteikumi</div>
                </div>
                <div>
                    <div class="text-2xl font-semibold text-neon-accent">{{ $receivedSubmissions->where('status', 'pending')->count() + $sentSubmissions->where('status', 'pending')->count() }}</div>
                    <div class="mt-1 text-sm text-gray-400">Gaida apstiprinājumu</div>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div>
                <h2 class="text-xl font-semibold text-white/90">Pieteikumi jūsu palīdzības pieprasījumiem</h2>
                <p class="mt-1 text-sm text-gray-400">Filtrējiet pēc statusa vai meklējiet pēc vakances, ziņas un lietotāja.</p>
            </div>

            <form action="/submissions" method="GET" class="grid gap-3 rounded-lg border border-gray-700 bg-gray-800/30 p-4 sm:grid-cols-[1fr_180px_auto]">
                @foreach($preserveExcept(['received_search', 'received_status'])->all() as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <input type="text" name="received_search" value="{{ request('received_search') }}" placeholder="Meklēt saņemtajos pieteikumos..." class="rounded-md border border-gray-700 bg-gray-900/60 px-3 py-2 text-white placeholder-gray-500 focus:border-neon-accent focus:outline-none focus:ring-2 focus:ring-neon-accent/30">
                <select name="received_status" class="rounded-md border border-gray-700 bg-gray-900/60 px-3 py-2 text-white">
                    <option value="">Visi statusi</option>
                    @foreach($submissionStatuses as $status)
                        <option value="{{ $status }}" @selected(request('received_status') === $status)>{{ $statusLabels[$status] ?? $status }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-md bg-neon-accent px-4 py-2 font-medium text-black transition-colors hover:bg-neon-accent/80">Filtrēt</button>
            </form>

            <div class="space-y-4">
                @forelse($receivedSubmissions as $submission)
                    <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-6 backdrop-blur-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
	                            <div>
	                                <div class="flex items-center gap-3">
	                                    <img src="{{ $submission->jobListing->imageUrl() }}" alt="{{ $submission->jobListing->title }}" class="h-12 w-16 rounded-md border border-gray-700 object-cover">
	                                    <h3 class="font-semibold text-white/90">{{ $submission->jobListing->title }}</h3>
	                                </div>
	                                <p class="mt-2 flex items-center gap-2 text-sm text-neon-accent">
	                                    <x-avatar :user="$submission->user" size="sm" />
	                                    <a href="{{ route('people.show', $submission->user->id) }}" class="transition-colors hover:text-neon-accent/80">
	                                        {{ $submission->user->first_name }} {{ $submission->user->last_name }}
	                                    </a> vēlas palīdzēt
	                                </p>
	                            </div>
                            <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$submission->status] ?? 'bg-gray-700 text-gray-300' }}">{{ $statusLabels[$submission->status] ?? $submission->status }}</span>
                        </div>

                        <p class="mt-3 text-sm text-gray-300">{{ $submission->status === 'claimed' ? 'Pieteicējs vēl nav pabeidzis pieteikumu.' : Str::limit($submission->message, 120) }}</p>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <a href="/submissions/{{ $submission->id }}" class="text-sm font-medium text-neon-accent transition-colors hover:text-neon-accent/80">Skatīt pilnu pieteikumu</a>

                            @if($submission->status === 'pending')
                                <form method="POST" action="/submissions/{{ $submission->id }}/approve">
                                    @csrf
                                    <button type="submit" class="rounded bg-green-700 px-3 py-1 text-xs font-medium text-white transition-colors hover:bg-green-600">Apstiprināt</button>
                                </form>
                                <form method="POST" action="/submissions/{{ $submission->id }}/decline">
                                    @csrf
                                    <button type="submit" class="rounded bg-red-700 px-3 py-1 text-xs font-medium text-white transition-colors hover:bg-red-600">Noraidīt</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-6 text-gray-400">
                        Nav atrastu saņemto pieteikumu.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-white/90">Mani pieteikumi, lai palīdzētu citiem</h2>
                    <p class="mt-1 text-sm text-gray-400">Sekojiet saviem pieteikumiem un ātri atrodiet vajadzīgo.</p>
                </div>
                <a href="/jobs" class="rounded-md border border-gray-700 px-4 py-2 text-sm font-medium text-gray-300 transition-colors hover:bg-gray-800/80 hover:text-neon-accent">Pārlūkot vakances</a>
            </div>

            <form action="/submissions" method="GET" class="grid gap-3 rounded-lg border border-gray-700 bg-gray-800/30 p-4 sm:grid-cols-[1fr_180px_auto]">
                @foreach($preserveExcept(['sent_search', 'sent_status'])->all() as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <input type="text" name="sent_search" value="{{ request('sent_search') }}" placeholder="Meklēt manos pieteikumos..." class="rounded-md border border-gray-700 bg-gray-900/60 px-3 py-2 text-white placeholder-gray-500 focus:border-neon-accent focus:outline-none focus:ring-2 focus:ring-neon-accent/30">
                <select name="sent_status" class="rounded-md border border-gray-700 bg-gray-900/60 px-3 py-2 text-white">
                    <option value="">Visi statusi</option>
                    @foreach($submissionStatuses as $status)
                        <option value="{{ $status }}" @selected(request('sent_status') === $status)>{{ $statusLabels[$status] ?? $status }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-md bg-neon-accent px-4 py-2 font-medium text-black transition-colors hover:bg-neon-accent/80">Filtrēt</button>
            </form>

            <div class="space-y-4">
                @forelse($sentSubmissions as $submission)
                    <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-6 backdrop-blur-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
	                            <div>
	                                <div class="flex items-center gap-3">
	                                    <img src="{{ $submission->jobListing->imageUrl() }}" alt="{{ $submission->jobListing->title }}" class="h-12 w-16 rounded-md border border-gray-700 object-cover">
	                                    <h3 class="font-semibold text-white/90">{{ $submission->jobListing->title }}</h3>
	                                </div>
	                                <p class="mt-1 text-sm text-gray-400">Publicēja
	                                    <a href="{{ route('people.show', $submission->jobListing->user->id) }}" class="inline-flex items-center gap-2 transition-colors hover:text-neon-accent">
	                                        <x-avatar :user="$submission->jobListing->user" size="sm" />
	                                        {{ $submission->jobListing->user->first_name }} {{ $submission->jobListing->user->last_name }}
	                                    </a>
	                                </p>
                            </div>
                            <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$submission->status] ?? 'bg-gray-700 text-gray-300' }}">{{ $statusLabels[$submission->status] ?? $submission->status }}</span>
                        </div>

                        <p class="mt-3 text-sm text-gray-300">{{ $submission->status === 'claimed' ? 'Jūs esat saņēmis šo palīdzības pieprasījumu. Lūdzu, pabeidziet pieteikumu.' : Str::limit($submission->message, 120) }}</p>

                        <a href="{{ $submission->status === 'claimed' ? '/jobs/' . $submission->job_listing_id : '/submissions/' . $submission->id }}" class="mt-4 inline-block text-sm font-medium text-neon-accent transition-colors hover:text-neon-accent/80">
                            {{ $submission->status === 'claimed' ? 'Pabeigt pieteikumu' : 'Skatīt detaļas' }}
                        </a>
                    </div>
                @empty
                    <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-6 text-gray-400">
                        Nav atrastu iesniegto pieteikumu.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-layout>
