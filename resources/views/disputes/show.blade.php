<x-layout>
    <x-slot name="heading">Atrisināt strīdu</x-slot>

    <div class="max-w-4xl mx-auto">
        <!-- Job Information -->
        <div class="bg-gray-900/60 border border-gray-700 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4">Darba detaļas</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-white mb-3">Informācija par darbu</h3>
                    <div class="space-y-2 text-sm">
                        <div><span class="text-gray-400">Nosaukums:</span> <span class="text-white ml-2">{{ $submission->jobListing->title }}</span></div>
                        <div><span class="text-gray-400">Kredīti:</span> <span class="text-neon-accent ml-2">{{ $submission->jobListing->time_credits }}</span></div>
                        <div><span class="text-gray-400">Kategorija:</span> <span class="text-white ml-2">{{ $submission->jobListing->category }}</span></div>
                        <div>
                            <span class="text-gray-400">Statuss:</span>
                            <span class="text-white ml-2">
                                @if($submission->status === 'claimed')
                                    Saņemts
                                @elseif($submission->status === 'pending')
                                    Gaida
                                @elseif($submission->status === 'approved')
                                    Apstiprināts
                                @elseif($submission->status === 'declined')
                                    Noraidīts
                                @elseif($submission->status === 'admin_review')
                                    Administratora pārskatīšana
                                @else
                                    {{ $submission->status }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white mb-3">Iesaistītās puses</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-400">Darba publicētājs:</span>
                            @if($submission->jobListing->user)
                                <a href="{{ route('people.show', $submission->jobListing->user->id) }}" class="text-white ml-2 hover:text-neon-accent transition-colors duration-200">
                                    {{ $submission->jobListing->user->first_name }} {{ $submission->jobListing->user->last_name }}
                                </a>
                            @else
                                <span class="text-white ml-2">Nav pieejams</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-400">Darbinieks:</span>
                            @if($submission->user)
                                <a href="{{ route('people.show', $submission->user->id) }}" class="text-white ml-2 hover:text-neon-accent transition-colors duration-200">
                                    {{ $submission->user->first_name }} {{ $submission->user->last_name }}
                                </a>
                            @else
                                <span class="text-white ml-2">Nav pieejams</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-400">Strīdu iesūtīja:</span>
                            @if($submission->disputeInitiator)
                                <a href="{{ route('people.show', $submission->disputeInitiator->id) }}" class="text-white ml-2 hover:text-neon-accent transition-colors duration-200">
                                    {{ $submission->disputeInitiator->first_name }} {{ $submission->disputeInitiator->last_name }}
                                </a>
                            @else
                                <span class="text-white ml-2">Nav pieejams</span>
                            @endif
                        </div>
                        <div><span class="text-gray-400">Sasaldēts:</span> <span class="text-red-400 ml-2">{{ $submission->is_frozen ? 'Jā' : 'Nē' }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dispute Information -->
        <div class="bg-gray-900/60 border border-gray-700 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4">Strīda detaļas</h2>
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-white mb-2">Strīda iemesls</h3>
                <p class="text-gray-200 bg-gray-800/40 p-4 rounded border border-gray-600">
                    {{ $submission->dispute_reason }}
                </p>
            </div>

            @if($submission->freeze_reason)
                <div>
                    <h3 class="text-lg font-semibold text-white mb-2">Saldēšanas iemesls</h3>
                    <p class="text-gray-200 bg-gray-800/40 p-4 rounded border border-gray-600">
                        {{ $submission->freeze_reason }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Submission Files (if any) -->
        @if($submission->files->count() > 0)
            <div class="bg-gray-900/60 border border-gray-700 rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4">Iesniegtie faili</h2>
                <div class="space-y-2">
                    @foreach($submission->files as $file)
                        <div class="flex items-center justify-between p-3 bg-gray-800/40 rounded border border-gray-600">
                            <span class="text-gray-200">{{ $file->filename }}</span>
                            <a href="{{ route('files.download', $file) }}" 
                               class="px-3 py-1 bg-neon-accent text-black text-sm rounded hover:bg-neon-accent/80 transition-colors duration-200">
                                Lejupielādēt
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Resolution Form -->
        @if($submission->dispute_status !== \App\Models\JobSubmission::DISPUTE_RESOLVED)
            <form action="{{ route('disputes.resolve', $submission) }}" method="POST" class="bg-gray-900/60 border border-gray-700 rounded-lg p-6">
                @csrf
                <h2 class="text-xl font-bold text-white mb-4">Atrisināt strīdu</h2>
                
                <div class="mb-6">
                    <x-form-field>
                        <x-form-label for="resolution">Atrisinājuma detaļas</x-form-label>
                        <textarea 
                            id="resolution" 
                            name="resolution" 
                            rows="4" 
                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-neon-accent focus:border-transparent"
                            placeholder="Paskaidrojiet savu lēmumu un tā pamatojumu..."
                            required>{{ old('resolution') }}</textarea>
                        <x-form-error name="resolution" />
                    </x-form-field>
                </div>

                <div class="mb-6">
                    <x-form-field>
                        <x-form-label for="action">Darbība, ko jāveic</x-form-label>
                        <select 
                            id="action" 
                            name="action" 
                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-neon-accent focus:border-transparent"
                            required>
                            <option value="">Izvēlieties darbību...</option>
                            <option value="approve">Apstiprināt iesniegumu (Pārsūtīt kredītus)</option>
                            <option value="decline">Noraidīt iesniegumu (Bez kredītu pārsūtīšanas)</option>
                            <option value="unfreeze">Atkausēt tikai (Atļaut pusēm atrisināt)</option>
                        </select>
                        <x-form-error name="action" />
                    </x-form-field>
                </div>

                <div class="bg-yellow-500/10 border border-yellow-500 rounded-md p-4 mb-6">
                    <p class="text-yellow-200 text-sm">
                        <strong>Darbību izskaidrojums:</strong><br>
                        • <strong>Apstiprināt:</strong> Kredīti tiks pārsūtīti no darba publicētāja uz darbinieku<br>
                        • <strong>Noraidīt:</strong> Bez kredītu pārsūtīšanas, iesniegums atzīmēts kā noraidīts<br>
                        • <strong>Atkausēt:</strong> Noņem sasaldēšanu, atļauj pusēm turpināt sarunas
                    </p>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('disputes.index') }}" 
                       class="px-4 py-2 text-gray-300 hover:text-white border border-gray-600 rounded-md hover:bg-gray-800 transition-colors duration-200">
                        Atcelt
                    </a>
                    <x-form-button type="submit" class="bg-green-600 hover:bg-green-700 text-white">
                        Atrisināt strīdu
                    </x-form-button>
                </div>
            </form>
        @else
            <div class="bg-green-900/20 border border-green-700 rounded-lg p-6">
                <h2 class="text-xl font-bold text-green-200 mb-4">Strīds atrisināts</h2>
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-green-200 mb-2">Atrisinājums</h3>
                    <p class="text-green-100 bg-green-900/30 p-4 rounded border border-green-600">
                        {{ $submission->dispute_resolution }}
                    </p>
                </div>
                <div class="text-sm text-gray-300">
                    Atrisināja {{ $submission->disputeResolver->first_name }} {{ $submission->disputeResolver->last_name }} 
                    @if($submission->dispute_resolved_at)
                        {{ $submission->dispute_resolved_at->translatedFormat('j. M Y, H:i') }}
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-layout>
