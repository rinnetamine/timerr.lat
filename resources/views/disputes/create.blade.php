<x-layout>
    <x-slot name="heading">Iesniegt strīdu</x-slot>

    <div class="mx-auto max-w-6xl overflow-hidden rounded-lg border border-gray-700 bg-gray-900/45 backdrop-blur-sm">
        <div class="grid grid-cols-1 lg:grid-cols-[0.9fr_1.1fr]">
            <aside class="border-b border-gray-700 bg-gray-950/45 p-8 lg:border-b-0 lg:border-r">
                <div class="inline-flex rounded-md border border-yellow-500/40 bg-yellow-500/10 px-3 py-2 text-sm font-semibold text-yellow-200">
                    Strīda konteksts
                </div>
                <h2 class="mt-6 text-3xl font-bold leading-tight text-white/95">Apraksti problēmu skaidri, lai admins var pieņemt godīgu lēmumu.</h2>
                <p class="mt-4 text-gray-300">Jo konkrētāks iemesls, jo vieglāk saprast, kas notika un kā atrisināt situāciju.</p>

                <div class="mt-8 rounded-lg border border-gray-700 bg-gray-900/55 p-5">
                    <h3 class="mb-4 text-lg font-bold text-white">Darba informācija</h3>
                    <x-job-image :job="$submission->jobListing" class="mb-4" />
                    <div class="space-y-3">
                        <div>
                            <span class="text-gray-400">Darba nosaukums:</span>
                            <span class="ml-2 text-white">{{ $submission->jobListing->title }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Publicēja:</span>
                            <span class="ml-2 inline-flex items-center gap-2 text-white"><x-avatar :user="$submission->jobListing->user" size="sm" />{{ $submission->jobListing->user->first_name }} {{ $submission->jobListing->user->last_name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Iesniedza:</span>
                            <span class="ml-2 inline-flex items-center gap-2 text-white"><x-avatar :user="$submission->user" size="sm" />{{ $submission->user->first_name }} {{ $submission->user->last_name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Laika kredīti:</span>
                            <span class="ml-2 text-neon-accent">{{ $submission->jobListing->time_credits }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Pašreizējais statuss:</span>
                            <span class="ml-2 text-white">{{ ucfirst($submission->status) }}</span>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="p-8">
        <form action="{{ route('disputes.store', $submission) }}" method="POST" class="bg-gray-900/60 border border-gray-700 rounded-lg p-6">
            @csrf
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-white mb-4">Strīda detaļas</h3>
                <p class="text-gray-300 mb-4">
                    Lūdzu, sniedziet detalizētu paskaidrojumu, kāpēc jūs strīdājaties ar šo darba iesniegumu. 
                    Būtiet specifiski par problēmām un sniedziet jebkurus relevantus pierādījumus.
                </p>
                
                <x-form-field>
                    <x-form-label for="reason">Strīda iemesls</x-form-label>
                    <textarea 
                        id="reason" 
                        name="reason" 
                        rows="6" 
                        class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-neon-accent focus:border-transparent"
                        placeholder="Detalizēti aprakstiet problēmu..."
                        required>{{ old('reason') }}</textarea>
                    <x-form-error name="reason" />
                </x-form-field>

                <div class="mt-4 p-4 bg-yellow-500/10 border border-yellow-500 rounded-md">
                    <p class="text-yellow-200 text-sm">
                        <strong>Svarīgi:</strong> Strīda iesniegšana sasaldēs šo darba iesniegumu, līdz admins to pārskatīs. 
                        Abas puses nevarēs veikt izmaiņas, līdz strīds tiks atrisināts.
                    </p>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('submissions.show', $submission) }}" 
                   class="px-4 py-2 text-gray-300 hover:text-white border border-gray-600 rounded-md hover:bg-gray-800 transition-colors duration-200">
                    Atcelt
                </a>
                <x-form-button type="submit" class="bg-red-600 hover:bg-red-700 text-white">
                    Iesniegt strīdu
                </x-form-button>
            </div>
        </form>
            </div>
        </div>
    </div>
</x-layout>
