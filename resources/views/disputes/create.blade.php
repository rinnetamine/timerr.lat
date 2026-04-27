<x-layout>
    <x-slot name="heading">Iesniegt strīdu</x-slot>

    <div class="max-w-2xl mx-auto">
	        <div class="bg-gray-900/60 border border-gray-700 rounded-lg p-6 mb-6">
	            <h2 class="text-xl font-bold text-white mb-4">Darba informācija</h2>
                <x-job-image :job="$submission->jobListing" class="mb-4" />
	            <div class="space-y-3">
                <div>
                    <span class="text-gray-400">Darba nosaukums:</span>
                    <span class="text-white ml-2">{{ $submission->jobListing->title }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Publicēja:</span>
	                    <span class="inline-flex items-center gap-2 text-white ml-2"><x-avatar :user="$submission->jobListing->user" size="sm" />{{ $submission->jobListing->user->first_name }} {{ $submission->jobListing->user->last_name }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Iesniedza:</span>
	                    <span class="inline-flex items-center gap-2 text-white ml-2"><x-avatar :user="$submission->user" size="sm" />{{ $submission->user->first_name }} {{ $submission->user->last_name }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Laika kredīti:</span>
                    <span class="text-neon-accent ml-2">{{ $submission->jobListing->time_credits }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Pašreizējais statuss:</span>
                    <span class="text-white ml-2">{{ ucfirst($submission->status) }}</span>
                </div>
            </div>
        </div>

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
</x-layout>
