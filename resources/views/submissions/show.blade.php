<x-layout>
    <x-slot:heading>
        Pieteikuma detaļas
    </x-slot:heading>

    <div class="max-w-3xl mx-auto">
        <div class="bg-gray-800/40 backdrop-blur-sm p-8 rounded-lg border border-gray-700">
            <!-- help request info-->
            <div class="mb-6">
                <x-job-image :job="$submission->jobListing" class="mb-5" />
                <h2 class="text-xl font-semibold text-white/90">{{ $submission->jobListing->title }}</h2>
                <p class="text-gray-400 text-sm mt-1">
                    Palīdzības pieprasījumu no 
                    <a href="{{ route('messages.conversation', $submission->jobListing->user->id) }}" class="inline-flex items-center gap-2 hover:text-neon-accent transition-colors duration-200">
                        <x-avatar :user="$submission->jobListing->user" size="sm" />
                        {{ $submission->jobListing->user->first_name }} {{ $submission->jobListing->user->last_name }}
                    </a>
                </p>
                <div class="mt-2 flex items-center">
                    <span class="bg-neon-accent/20 text-neon-accent px-3 py-1 rounded-full text-xs font-semibold">
                        {{ $submission->jobListing->time_credits }} laika kredīti
                    </span>
                    <span class="ml-3 text-gray-400 text-sm capitalize">
                        Kategorija: {{ $submission->jobListing->category }}
                    </span>
                </div>
            </div>

            <!-- application Status -->
            <div class="border-t border-gray-700 pt-4 mb-6">
                <div class="flex items-center">
                    <span class="text-gray-400 text-sm">Statuss: </span>
                    @if($submission->status === 'claimed')
                        <span class="ml-2 bg-blue-500/20 text-blue-300 px-3 py-1 rounded-full text-xs font-semibold">Saņēmts</span>
                    @elseif($submission->status === 'pending')
                        <span class="ml-2 bg-yellow-500/20 text-yellow-300 px-3 py-1 rounded-full text-xs font-semibold">Gaida</span>
                    @elseif($submission->status === 'approved')
                        <span class="ml-2 bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-xs font-semibold">Apstiprināts</span>
                    @elseif($submission->status === 'declined')
                        <span class="ml-2 bg-red-500/20 text-red-300 px-3 py-1 rounded-full text-xs font-semibold">Noraidīts</span>
                    @elseif($submission->status === 'admin_review')
                        <span class="ml-2 bg-purple-500/20 text-purple-300 px-3 py-1 rounded-full text-xs font-semibold">Admin pārskatīšana</span>
                    @endif
                </div>
            </div>

            <!-- Applicant Info -->
            <div class="border-t border-gray-700 pt-4 mb-6">
                <h3 class="font-semibold text-white/90 mb-2">Pieteicējs</h3>
                <p class="text-gray-300">
                    <a href="{{ route('messages.conversation', $submission->user->id) }}" class="inline-flex items-center gap-2 hover:text-neon-accent transition-colors duration-200">
                        <x-avatar :user="$submission->user" size="sm" />
                        {{ $submission->user->first_name }} {{ $submission->user->last_name }}
                    </a>
                </p>
                <p class="text-gray-400 text-sm">{{ $submission->user->email }}</p>
            </div>

            <div class="border-t border-gray-700 pt-4 mb-6">
                <h3 class="font-semibold text-white/90 mb-2">Ziņojums</h3>
                <div class="text-gray-300 whitespace-pre-line">{{ $submission->message }}</div>
            </div>

            @if(auth()->check() && auth()->id() === $submission->user_id && $submission->status === 'claimed')
                <div class="border-t border-gray-700 pt-4 mb-6">
                    <h3 class="font-semibold text-white/90 mb-2">Pabeidziet savu pieteikumu</h3>

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
                            <label class="block text-sm font-medium text-gray-300 mb-1">Ziņojums darba īpašniekam</label>
                            <textarea name="message" rows="6" required placeholder="Aprakstiet, ko jūs darīsiet, laika posmu, vai pievienojiet jebkurus pierādījumu failus..." class="w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent">{{ old('message') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Pielikumi (neobligāti)</label>
                            <input type="file" name="files[]" multiple class="text-sm text-gray-300">
                            <p class="text-xs text-gray-500 mt-1">Jūs varat pievienot failus, lai atbalstītu savu pieteikumu. Maks. 50MB failā.</p>
                        </div>

                        <div class="flex justify-end">
                            <a href="/submissions" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium mr-3">Atcelt</a>
                            <button type="submit" class="bg-neon-accent text-black px-4 py-2 rounded text-sm font-medium">Iesniegt pieteikumu</button>
                        </div>
                    </form>
                </div>
            @endif
            
            <!-- admin notes -->
            @if($submission->status === 'admin_review' && $submission->admin_notes)
                <div class="border-t border-gray-700 pt-4 mb-6">
                    <h3 class="font-semibold text-white/90 mb-2">Admin piezīmes</h3>
                    <div class="bg-purple-500/10 border border-purple-500/30 p-4 rounded-md text-gray-300 whitespace-pre-line">
                        {{ $submission->admin_notes }}
                    </div>
                    <p class="mt-2 text-sm text-gray-400">Administrators pārskatīs šo pieteikumu un izlems, vai kredīti jāatgriež darba īpašniekam vai jāpiešķir pieteicējam.</p>
                </div>
            @endif

            <!-- attached files -->
            @if($submission->files->count() > 0)
                <div class="border-t border-gray-700 pt-4 mb-6">
                    <h3 class="font-semibold text-white/90 mb-2">Pievienotie faili</h3>
                    <div class="space-y-2">
                        @foreach($submission->files as $file)
                            <div class="flex items-center justify-between bg-gray-900/60 p-3 rounded">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-gray-300 text-sm">{{ $file->file_name }}</span>
                                </div>
                                <a href="{{ route('files.download', $file->id) }}" class="text-neon-accent hover:text-neon-accent/80 text-sm font-medium">
                                    Lejupielādēt
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
                            Atpakaļ uz pieteikumiem
                        </a>
                        <a href="{{ route('submissions.export', $submission->id) }}" class="ml-3 bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">Lejupielādēt PDF</a>
                        <div class="flex space-x-3">
                            <form method="POST" action="/submissions/{{ $submission->id }}/decline" id="declineForm" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
                                @csrf
                                <div class="bg-gray-800 p-6 rounded-lg max-w-md w-full">
                                    <h3 class="text-lg font-semibold text-white mb-4">Noraidīt pieteikumu</h3>
                                    
                                    <p class="text-gray-300 mb-4">
                                        Šis pieteikums tiks nosūtīts administratora pārskatīšanai. Lūdzu, norādiet iemeslu noraidīšanai:
                                    </p>
                                    
                                    <textarea name="admin_notes" rows="4" required
                                        class="w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500"
                                        placeholder="Paskaidrojiet, kāpēc noraidāt šo pieteikumu..."></textarea>
                                    
                                    <div class="mt-4 flex justify-end space-x-3">
                                        <button type="button" onclick="document.getElementById('declineForm').classList.add('hidden')" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                                            Atcelt
                                        </button>
                                        <button type="submit" class="bg-red-700 hover:bg-red-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                                            Iesniegt un noraidīt
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <button type="button" onclick="document.getElementById('declineForm').classList.remove('hidden')" class="bg-red-700 hover:bg-red-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                                Noraidīt
                            </button>
                            
                            <form method="POST" action="/submissions/{{ $submission->id }}/approve">
                                @csrf
                                <button type="submit" class="bg-green-700 hover:bg-green-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                                    Apstiprināt un pārsūtīt kredītus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <div class="border-t border-gray-700 pt-6 mt-6">
                    <div class="flex items-center space-x-3">
                        <a href="/submissions" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                            Atpakaļ uz pieteikumiem
                        </a>
                        <a href="{{ route('submissions.export', $submission->id) }}" class="ml-2 bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">Lejupielādēt PDF</a>
                    </div>
                </div>
            @endif

            {{-- Review form: allow job owner to leave a review after approval/completion --}}
            @if(auth()->check() && auth()->id() === $submission->jobListing->user_id && $submission->status === 'approved')
                <div class="border-t border-gray-700 pt-6 mt-6">
                    <h3 class="font-semibold text-white/90 mb-3">Atstājiet atsauksmi {{ $submission->user->first_name }}</h3>

                    @if(session('success'))
                        <div class="bg-green-900/30 border border-green-700 p-3 rounded mb-4 text-green-200">{{ session('success') }}</div>
                    @endif

                    @if($submission->review)
                        <div class="bg-gray-900/60 p-4 rounded border border-gray-700">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-sm text-gray-300">Vērtējums: <strong class="text-neon-accent">{{ $submission->review->rating }}/5</strong></div>
                                    <div class="mt-2 text-gray-300 whitespace-pre-line">{{ $submission->review->comment }}</div>
                                    <div class="text-xs text-gray-500 mt-2">Atstāts {{ $submission->review->created_at->translatedFormat('j. M Y') }}</div>
                                </div>
                            </div>
                        </div>
                    @else
                        @if($errors->any())
                            <div class="bg-red-900/40 border border-red-700 p-3 rounded mb-4">
                                <ul class="text-sm text-red-200 list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="/submissions/{{ $submission->id }}/reviews" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Vērtējums</label>
                                <select name="rating" required class="w-32 rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2">
                                    <option value="">Izvēlieties</option>
                                    @for($i=5; $i>=1; $i--)
                                        <option value="{{ $i }}">{{ $i }} zvaigzne{{ $i>1? 's':'' }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Komentārs (neobligāti)</label>
                                <textarea name="comment" rows="4" placeholder="Dalieties ar to, kas noritās labi, vai jebkādām piezīmēm..." class="w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none">{{ old('comment') }}</textarea>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="bg-neon-accent text-black px-4 py-2 rounded text-sm font-medium">Iesniegt atsauksmi</button>
                            </div>
                        </form>
                    @endif
                </div>
            @endif

            <!-- Dispute button for both parties -->
            @if(auth()->check() && 
               (auth()->id() === $submission->jobListing->user_id || auth()->id() === $submission->user_id))
                <div class="border-t border-gray-700 pt-6 mt-6">
                    @if($submission->canBeDisputed())
                        <div class="bg-yellow-500/10 border border-yellow-500 rounded-md p-4 mb-4">
                            <p class="text-yellow-200 text-sm">
                                <strong>Radās problēmas ar šo iesniegumu?</strong> Jūs varat iesniegt strīdu, lai sasaldētu iesniegumu un saņemtu administratora palīdzību.
                            </p>
                        </div>
                        <div class="flex justify-end">
                            <a href="{{ route('disputes.create', $submission) }}" 
                               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                                Iesniegt strīdu
                            </a>
                        </div>
                    @else
                        <div class="bg-gray-700/50 border border-gray-600 rounded-md p-4 mb-4">
                            <p class="text-gray-300 text-sm">
                                <strong>Strīds jau ir iesniegts</strong> Šim iesniegumam jau ir aktīvs strīds.
                            </p>
                            @if($submission->is_frozen)
                                <p class="text-gray-400 text-xs mt-2">
                                    Iemesls: {{ $submission->freeze_reason }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            <!-- Frozen status indicator -->
            @if($submission->is_frozen)
                <div class="border-t border-gray-700 pt-6 mt-6">
                    <div class="bg-red-500/10 border border-red-500 rounded-md p-4">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <div>
                                <p class="text-red-200 font-semibold">Iesniegums ir sasaldēts</p>
                                @if($submission->freeze_reason)
                                    <p class="text-red-300 text-sm mt-1">{{ $submission->freeze_reason }}</p>
                                @endif
                                @if($submission->dispute_status !== \App\Models\JobSubmission::DISPUTE_NONE)
                                    <p class="text-red-300 text-sm mt-1">
                                        Statuss: {{ ucfirst(str_replace('_', ' ', $submission->dispute_status)) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layout>
