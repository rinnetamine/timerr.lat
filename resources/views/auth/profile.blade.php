<x-layout>
    <x-slot:heading>
        Mans profils
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

	        $paginationKeys = ['vacancies_page', 'received_page', 'sent_page', 'reviews_page', 'transactions_page'];
	        $preserveExcept = function (array $except) use ($paginationKeys) {
	            $except = array_merge($except, $paginationKeys);
	            return collect(request()->except($except))->filter(fn ($value) => is_scalar($value) && $value !== '');
	        };

        $averageRating = (float) ($user->reviews_received_rating_avg ?? 0);
    @endphp

    <div class="space-y-8">
        @if (session('profile_success') || session('password_success'))
            <div class="rounded-lg border border-green-500/50 bg-green-500/10 p-4 text-sm text-green-300">
                {{ session('profile_success') ?? session('password_success') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-lg border border-gray-700 bg-gray-800/40 backdrop-blur-sm">
            <div class="grid gap-0 lg:grid-cols-[1.5fr_1fr]">
                <div class="p-6 sm:p-8">
	                    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
	                        <div class="flex flex-col items-start gap-3">
	                            <x-avatar :user="$user" size="lg" class="shadow-lg shadow-neon-glow/10" />
	                            <span class="rounded-full border border-gray-700 bg-gray-900/60 px-3 py-1 text-xs text-gray-400">Pašreizējais</span>
	                        </div>
	                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="text-2xl font-semibold text-white/90">{{ $user->first_name }} {{ $user->last_name }}</h2>
                                <span class="rounded-full border border-gray-700 bg-gray-900/60 px-3 py-1 text-sm text-gray-300">{{ $user->email }}</span>
                            </div>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                @if($averageRating > 0)
                                    <div class="flex items-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="{{ $i <= round($averageRating) ? 'text-yellow-400' : 'text-gray-600' }}">★</span>
                                        @endfor
                                        <span class="ml-1 text-sm text-gray-400">{{ number_format($averageRating, 1) }} no 5</span>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500">Nav vērtējuma</span>
                                @endif
                                <button onclick="openPasswordModal()" class="rounded-md border border-orange-500/40 bg-orange-600/20 px-3 py-2 text-sm font-medium text-orange-200 transition-colors hover:bg-orange-600/30">
                                    Mainīt paroli
                                </button>
                            </div>
	                            <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" class="mt-5 rounded-lg border border-gray-700 bg-gray-900/35 p-4">
	                                @csrf
	                                <div class="mb-3 flex items-center justify-between gap-3">
	                                    <div>
	                                        <div class="text-sm font-medium text-white/90">Izvēlieties profila attēlu</div>
	                                        <div class="text-xs text-gray-400">Noklusējuma attēls vai jūsu augšupielādēts fails</div>
	                                    </div>
	                                    <button type="submit" class="shrink-0 rounded-md bg-neon-accent px-3 py-2 text-sm font-medium text-black transition-colors hover:bg-neon-accent/80">Saglabāt</button>
	                                </div>
		                                <div class="-mx-1 max-w-2xl overflow-x-auto px-2 pb-2 pt-1">
		                                    <div class="flex w-max items-center gap-2">
		                                    @foreach($defaultAvatars as $avatar)
		                                        <label class="group relative h-10 w-10 cursor-pointer">
		                                            <input type="radio" name="default_avatar" value="{{ $avatar }}" class="peer sr-only" @checked($user->avatar_path === $avatar)>
		                                            <span class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-700 bg-gray-950/60 p-0.5 transition peer-checked:border-neon-accent peer-checked:bg-neon-accent/10 peer-checked:ring-2 peer-checked:ring-neon-accent/30 group-hover:border-neon-accent/60">
		                                                <img src="/{{ ltrim($avatar, '/') }}" alt="Noklusējuma profila attēls" class="h-full w-full rounded-full object-cover">
		                                            </span>
		                                            <span class="pointer-events-none absolute -right-1 -top-1 hidden h-4 w-4 items-center justify-center rounded-full bg-neon-accent text-[10px] font-bold text-black peer-checked:flex">✓</span>
		                                        </label>
		                                    @endforeach
		                                    </div>
		                                </div>
	                                <div class="mt-4 border-t border-gray-700 pt-4">
	                                    <label class="block text-xs font-medium uppercase tracking-wide text-gray-500">Augšupielādēt savu</label>
	                                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full rounded-md border border-gray-700 bg-gray-950/60 px-3 py-2 text-sm text-gray-300 file:mr-3 file:rounded-md file:border-0 file:bg-gray-700 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-gray-100 hover:file:bg-gray-600">
	                                </div>
	                            </form>
                            @error('avatar')
                                <div class="mt-2 text-sm text-red-300">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

	                <div class="border-t border-gray-700 bg-gray-900/40 p-5 lg:border-l lg:border-t-0">
	                    <div class="flex items-start justify-between gap-4">
	                        <div>
	                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Konts</div>
	                            <div class="mt-2 text-3xl font-semibold text-neon-accent">{{ $user->time_credits }}</div>
	                            <div class="text-sm text-gray-400">laika kredīti</div>
	                        </div>
	                        <div class="rounded-full border border-gray-700 bg-gray-950/60 px-3 py-1 text-sm {{ ($profileStats['credit_movement_30_days'] ?? 0) >= 0 ? 'text-green-300' : 'text-red-300' }}">
	                            {{ ($profileStats['credit_movement_30_days'] ?? 0) >= 0 ? '+' : '' }}{{ $profileStats['credit_movement_30_days'] ?? 0 }} pēd. 30d
	                        </div>
	                    </div>

	                    <div class="mt-5 grid grid-cols-2 gap-3">
	                        <div class="rounded-lg border border-gray-700 bg-gray-950/45 p-4">
	                            <div class="text-xl font-semibold text-white/90">{{ $user->jobs_count ?? $user->jobs()->count() }}</div>
	                            <div class="mt-1 text-xs text-gray-400">Kopā vakances</div>
	                        </div>
	                        <div class="rounded-lg border border-gray-700 bg-gray-950/45 p-4">
	                            <div class="text-xl font-semibold text-white/90">{{ $profileStats['active_vacancies'] ?? 0 }}</div>
	                            <div class="mt-1 text-xs text-gray-400">Atvērtas</div>
	                        </div>
	                        <div class="rounded-lg border border-gray-700 bg-gray-950/45 p-4">
	                            <div class="text-xl font-semibold text-yellow-300">{{ $profileStats['pending_received'] ?? 0 }}</div>
	                            <div class="mt-1 text-xs text-gray-400">Gaida lēmumu</div>
	                        </div>
	                        <div class="rounded-lg border border-gray-700 bg-gray-950/45 p-4">
	                            <div class="text-xl font-semibold text-white/90">{{ $user->reviews_received_count ?? $user->reviewsReceived()->count() }}</div>
	                            <div class="mt-1 text-xs text-gray-400">Atsauksmes</div>
	                        </div>
	                    </div>

	                    <div class="mt-5 grid grid-cols-2 gap-3">
	                        <div class="rounded-lg border border-gray-700 bg-gray-950/45 p-4">
	                            <div class="text-xl font-semibold text-white/90">{{ number_format($averageRating, 1) }}</div>
	                            <div class="mt-1 text-xs text-gray-400">Vidējais vērtējums</div>
	                        </div>
	                        <div class="rounded-lg border border-gray-700 bg-gray-950/45 p-4">
	                            <div class="text-xl font-semibold text-green-300">{{ $profileStats['approved_sent'] ?? 0 }}</div>
	                            <div class="mt-1 text-xs text-gray-400">Apstiprināti pieteikumi</div>
	                        </div>
	                    </div>
	                </div>
            </div>
        </section>

        <div id="passwordModal" class="hidden fixed inset-0 z-50 h-full w-full overflow-y-auto bg-gray-950/70">
            <div class="relative top-20 mx-auto w-full max-w-md rounded-lg border border-gray-700 bg-gray-800 p-5 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white/90">Mainīt paroli</h3>
                    <button onclick="closePasswordModal()" class="text-gray-400 transition-colors hover:text-gray-200" aria-label="Aizvērt">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('profile.change-password') }}" method="POST" class="space-y-4">
                    @csrf
                    @if ($errors->any())
                        <div class="rounded-md border border-red-500 bg-red-500/10 p-3">
                            @foreach ($errors->all() as $error)
                                <div class="text-sm text-red-300">{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if (session('password_success'))
                        <div class="rounded-md border border-green-500 bg-green-500/10 p-3 text-sm text-green-300">{{ session('password_success') }}</div>
                    @endif

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">Esošā parole</label>
                        <input type="password" name="current_password" class="w-full rounded-md border border-gray-600 bg-gray-900 px-3 py-2 text-white placeholder-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-neon-accent" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">Jaunā parole</label>
                        <input type="password" name="password" minlength="6" class="w-full rounded-md border border-gray-600 bg-gray-900 px-3 py-2 text-white placeholder-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-neon-accent" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">Apstipriniet jauno paroli</label>
                        <input type="password" name="password_confirmation" minlength="6" class="w-full rounded-md border border-gray-600 bg-gray-900 px-3 py-2 text-white placeholder-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-neon-accent" required>
                    </div>

                    <div class="rounded-md border border-blue-500/60 bg-blue-500/10 p-3 text-sm text-blue-200">
                        Parolei vajag vismaz 6 rakstzīmes, vienu ciparu un vienu speciālo rakstzīmi.
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="button" onclick="closePasswordModal()" class="flex-1 rounded-md bg-gray-700 px-4 py-2 font-medium text-white transition-colors hover:bg-gray-600">Atcelt</button>
                        <button type="submit" class="flex-1 rounded-md bg-neon-accent px-4 py-2 font-medium text-black transition-colors hover:bg-neon-accent/80">Saglabāt</button>
                    </div>
                </form>
            </div>
        </div>

        <section class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-xl font-semibold text-white/90">Manas vakances</h3>
                    <p class="mt-1 text-sm text-gray-400">Pārvaldiet savus publicētos palīdzības pieprasījumus.</p>
                </div>
                <x-button href="/jobs/create">Izveidot vakanci</x-button>
            </div>

            <form action="{{ route('profile') }}" method="GET" class="grid gap-3 rounded-lg border border-gray-700 bg-gray-800/30 p-4 md:grid-cols-[1fr_180px_180px_auto]">
                @foreach($preserveExcept(['vacancy_search', 'vacancy_status', 'vacancy_sort'])->all() as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <input type="text" name="vacancy_search" value="{{ request('vacancy_search') }}" placeholder="Meklēt manās vakancēs..." class="rounded-md border border-gray-700 bg-gray-900/60 px-3 py-2 text-white placeholder-gray-500 focus:border-neon-accent focus:outline-none focus:ring-2 focus:ring-neon-accent/30">
                <select name="vacancy_status" class="rounded-md border border-gray-700 bg-gray-900/60 px-3 py-2 text-white">
                    <option value="">Visas vakances</option>
                    <option value="open" @selected(request('vacancy_status') === 'open')>Bez pieteikumiem</option>
                    <option value="has_submissions" @selected(request('vacancy_status') === 'has_submissions')>Ar pieteikumiem</option>
                </select>
                <select name="vacancy_sort" class="rounded-md border border-gray-700 bg-gray-900/60 px-3 py-2 text-white">
                    <option value="latest" @selected(request('vacancy_sort', 'latest') === 'latest')>Jaunākās</option>
                    <option value="oldest" @selected(request('vacancy_sort') === 'oldest')>Vecākās</option>
                    <option value="credits_desc" @selected(request('vacancy_sort') === 'credits_desc')>Vairāk kredītu</option>
                    <option value="credits_asc" @selected(request('vacancy_sort') === 'credits_asc')>Mazāk kredītu</option>
                    <option value="title" @selected(request('vacancy_sort') === 'title')>Nosaukums</option>
                </select>
                <button type="submit" class="rounded-md bg-neon-accent px-4 py-2 font-medium text-black transition-colors hover:bg-neon-accent/80">Filtrēt</button>
            </form>

            @if($services->count() > 0)
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($services as $service)
                        <a href="/jobs/{{ $service->id }}" class="group block overflow-hidden rounded-lg border border-gray-700 bg-gray-800/40 backdrop-blur-sm transition-colors hover:border-neon-accent hover:bg-gray-800/70">
                            <x-job-image :job="$service" class="rounded-none border-0 border-b border-gray-700" />
                            <div class="p-5">
                            <div class="flex min-w-0 items-start justify-between gap-4">
                                <h4 class="min-w-0 truncate text-lg font-semibold text-white/90 transition-colors group-hover:text-neon-accent">{{ $service->title }}</h4>
                                <span class="shrink-0 rounded-full bg-neon-accent/20 px-3 py-1 text-xs font-semibold text-neon-accent">{{ $service->time_credits }} kredīti</span>
                            </div>
                            <p class="mt-3 line-clamp-3 text-sm text-gray-400">{{ $service->description }}</p>
                            <div class="mt-5 flex items-center justify-between text-sm">
                                <span class="text-gray-500">{{ $service->created_at->diffForHumans() }}</span>
                                <span class="text-gray-300">{{ $service->submissions_count ?? $service->submissions()->count() }} pieteikumi</span>
                            </div>
                            </div>
                        </a>
	                    @endforeach
	                </div>
	                <div class="mt-4">
	                    {{ $services->links() }}
	                </div>
	            @else
	                <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-8 text-center text-gray-400">
                    Nav atrasta neviena vakance ar šiem filtriem.
                </div>
            @endif
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="space-y-4">
                <div>
                    <h3 class="text-xl font-semibold text-white/90">Pieteikumi manām vakancēm</h3>
                    <p class="mt-1 text-sm text-gray-400">Pieteikumi, ko citi ir iesnieguši jūsu pieprasījumiem.</p>
                </div>

                <form action="{{ route('profile') }}" method="GET" class="grid gap-3 rounded-lg border border-gray-700 bg-gray-800/30 p-4 sm:grid-cols-[1fr_170px_auto]">
                    @foreach($preserveExcept(['received_search', 'received_status'])->all() as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <input type="text" name="received_search" value="{{ request('received_search') }}" placeholder="Meklēt pieteikumos..." class="rounded-md border border-gray-700 bg-gray-900/60 px-3 py-2 text-white placeholder-gray-500 focus:border-neon-accent focus:outline-none focus:ring-2 focus:ring-neon-accent/30">
                    <select name="received_status" class="rounded-md border border-gray-700 bg-gray-900/60 px-3 py-2 text-white">
                        <option value="">Visi statusi</option>
                        @foreach($submissionStatuses as $status)
                            <option value="{{ $status }}" @selected(request('received_status') === $status)>{{ $statusLabels[$status] ?? $status }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-md bg-neon-accent px-4 py-2 font-medium text-black transition-colors hover:bg-neon-accent/80">Filtrēt</button>
                </form>

                <div class="space-y-3">
                    @forelse($receivedSubmissions as $submission)
                        <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $submission->jobListing->imageUrl() }}" alt="{{ $submission->jobListing->title }}" class="h-12 w-16 rounded-md border border-gray-700 object-cover">
                                        <h4 class="font-semibold text-white/90">{{ $submission->jobListing->title }}</h4>
                                    </div>
                                    <p class="mt-1 text-sm text-neon-accent">{{ $submission->user->first_name }} {{ $submission->user->last_name }} vēlas palīdzēt</p>
                                </div>
                                <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$submission->status] ?? 'bg-gray-700 text-gray-300' }}">{{ $statusLabels[$submission->status] ?? $submission->status }}</span>
                            </div>
                            <p class="mt-3 text-sm text-gray-300">{{ $submission->status === 'claimed' ? $submission->user->first_name . ' vēl nav pabeidzis pieteikumu.' : Str::limit($submission->message, 120) }}</p>
                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <a href="/submissions/{{ $submission->id }}" class="text-sm font-medium text-neon-accent transition-colors hover:text-neon-accent/80">Skatīt pieteikumu</a>
                                @if($submission->status === 'pending')
                                    <form method="POST" action="/submissions/{{ $submission->id }}/approve">@csrf<button type="submit" class="rounded bg-green-700 px-3 py-1 text-xs font-medium text-white transition-colors hover:bg-green-600">Apstiprināt</button></form>
                                    <button type="button" onclick="document.getElementById('declineForm-{{ $submission->id }}').classList.remove('hidden')" class="rounded bg-red-700 px-3 py-1 text-xs font-medium text-white transition-colors hover:bg-red-600">Noraidīt</button>
                                    <form method="POST" action="/submissions/{{ $submission->id }}/decline" id="declineForm-{{ $submission->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
                                        @csrf
                                        <div class="w-full max-w-md rounded-lg bg-gray-800 p-6">
                                            <h3 class="mb-4 text-lg font-semibold text-white">Noraidīt pieteikumu</h3>
                                            <p class="mb-4 text-gray-300">Šis pieteikums tiks nosūtīts admin pārskatīšanai. Lūdzu, norādiet iemeslu.</p>
                                            <textarea name="admin_notes" rows="4" required class="w-full rounded-md border border-gray-700 bg-gray-900/60 px-3 py-2 text-gray-100 placeholder-gray-500 focus:border-neon-accent focus:outline-none focus:ring-2 focus:ring-neon-accent/50"></textarea>
                                            <div class="mt-4 flex justify-end gap-3">
                                                <button type="button" onclick="document.getElementById('declineForm-{{ $submission->id }}').classList.add('hidden')" class="rounded bg-gray-700 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-600">Atcelt</button>
                                                <button type="submit" class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-600">Iesniegt</button>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-6 text-gray-400">Nav atrastu saņemto pieteikumu.</div>
	                    @endforelse
	                </div>
	                @if($receivedSubmissions->hasPages())
	                    <div class="mt-4">
	                        {{ $receivedSubmissions->links() }}
	                    </div>
	                @endif
	            </section>

            <section class="space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-white/90">Mani iesniegtie pieteikumi</h3>
                        <p class="mt-1 text-sm text-gray-400">Pieteikumi, ko esat nosūtījis citiem.</p>
                    </div>
                    <a href="/jobs" class="rounded-md border border-gray-700 px-4 py-2 text-sm font-medium text-gray-300 transition-colors hover:bg-gray-800/80 hover:text-neon-accent">Pārlūkot vakances</a>
                </div>

                <form action="{{ route('profile') }}" method="GET" class="grid gap-3 rounded-lg border border-gray-700 bg-gray-800/30 p-4 sm:grid-cols-[1fr_170px_auto]">
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

                <div class="space-y-3">
                    @forelse($sentSubmissions as $submission)
                        <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $submission->jobListing->imageUrl() }}" alt="{{ $submission->jobListing->title }}" class="h-12 w-16 rounded-md border border-gray-700 object-cover">
                                        <h4 class="font-semibold text-white/90">{{ $submission->jobListing->title }}</h4>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-400">Publicēja {{ $submission->jobListing->user->first_name }} {{ $submission->jobListing->user->last_name }}</p>
                                </div>
                                <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$submission->status] ?? 'bg-gray-700 text-gray-300' }}">{{ $statusLabels[$submission->status] ?? $submission->status }}</span>
                            </div>
                            <p class="mt-3 text-sm text-gray-300">{{ $submission->status === 'claimed' ? 'Jūs esat saņēmis šo palīdzības pieprasījumu. Lūdzu, pabeidziet pieteikumu.' : Str::limit($submission->message, 120) }}</p>
                            <a href="{{ $submission->status === 'claimed' ? '/jobs/' . $submission->job_listing_id : '/submissions/' . $submission->id }}" class="mt-4 inline-block text-sm font-medium text-neon-accent transition-colors hover:text-neon-accent/80">
                                {{ $submission->status === 'claimed' ? 'Pabeigt pieteikumu' : 'Skatīt detaļas' }}
                            </a>
                        </div>
                    @empty
                        <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-6 text-gray-400">Nav atrastu iesniegto pieteikumu.</div>
	                    @endforelse
	                </div>
	                @if($sentSubmissions->hasPages())
	                    <div class="mt-4">
	                        {{ $sentSubmissions->links() }}
	                    </div>
	                @endif
	            </section>
        </div>

	        <section class="space-y-4">
	            <h3 class="text-xl font-semibold text-white/90">Saņemtās atsauksmes</h3>
	            @if($reviewsReceived->isEmpty())
	                <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-6 text-gray-400">Vēl nav atsauksmju.</div>
	            @else
	                <div class="grid gap-4 md:grid-cols-2">
	                    @foreach($reviewsReceived as $review)
	                        <div class="rounded-lg border border-gray-700 bg-gray-800/40 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-sm text-gray-300">{{ $review->reviewer->first_name }} {{ $review->reviewer->last_name }}</div>
                                    @if($review->comment)
                                        <div class="mt-2 whitespace-pre-line text-gray-300">{{ $review->comment }}</div>
                                    @endif
                                    <div class="mt-2 text-xs text-gray-500">{{ $review->created_at->translatedFormat('j. M Y') }}</div>
                                </div>
                                <div class="whitespace-nowrap text-sm text-yellow-400">{{ str_repeat('★', $review->rating) }}<span class="text-gray-600">{{ str_repeat('★', 5 - $review->rating) }}</span></div>
                            </div>
                        </div>
	                    @endforeach
	                </div>
	                @if($reviewsReceived->hasPages())
	                    <div class="mt-4">
	                        {{ $reviewsReceived->links() }}
	                    </div>
	                @endif
	            @endif
	        </section>

        <section class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-xl font-semibold text-white/90">Transakciju vēsture</h3>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('transactions.export') }}" class="rounded bg-green-600/90 px-3 py-1 text-sm font-medium text-white transition-colors hover:bg-green-700/90">HTML</a>
                    <a href="{{ route('transactions.download') }}" class="rounded bg-green-600/90 px-3 py-1 text-sm font-medium text-white transition-colors hover:bg-green-700/90">PDF</a>
                    <a href="{{ route('transactions.csv') }}" class="rounded bg-green-600/90 px-3 py-1 text-sm font-medium text-white transition-colors hover:bg-green-700/90">CSV</a>
                    <a href="{{ route('transactions.excel') }}" class="rounded bg-green-600/90 px-3 py-1 text-sm font-medium text-white transition-colors hover:bg-green-700/90">Excel</a>
                </div>
            </div>
            <div class="overflow-hidden rounded-lg border border-gray-700 bg-gray-800/40">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-900/60">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Datums</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Apraksts</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-400">Kredīti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @forelse($transactions ?? [] as $transaction)
                                @if(!is_object($transaction)) @continue @endif
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-400">{{ $transaction->created_at?->translatedFormat('j. M Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-400">{{ $transaction->description }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm"><span class="{{ $transaction->amount > 0 ? 'text-green-400' : 'text-red-400' }}">{{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-400">Vēl nav transakciju</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($transactions->hasPages())
                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </section>
    </div>
</x-layout>

<script>
function openPasswordModal() {
    document.getElementById('passwordModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

document.getElementById('passwordModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closePasswordModal();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closePasswordModal();
    }
});
</script>
