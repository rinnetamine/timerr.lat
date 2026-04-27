<x-layout>
    <x-slot:heading>
        Palīdzības pieprasījums
    </x-slot:heading>

    <div class="max-w-3xl mx-auto">
        <div class="overflow-hidden bg-gray-800/40 backdrop-blur-sm rounded-lg border border-gray-700">
            <x-job-image :job="$job" class="rounded-none border-0 border-b border-gray-700" />
            <div class="p-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="font-bold text-neon-accent text-sm flex items-center gap-2">
                        <x-avatar :user="$job->user" size="sm" />
                        <span>
                        <a href="{{ route('messages.conversation', $job->user->id) }}" class="hover:text-neon-accent/80 transition-colors duration-200">
                            {{ $job->user->first_name }} {{ $job->user->last_name }}
                        </a> vajag palīdzību
                        </span>
                    </div>
                    <h2 class="font-bold text-xl text-white/90 mt-1">{{ $job->title }}</h2>
                </div>
                <div class="bg-neon-accent/20 text-neon-accent px-4 py-2 rounded-full text-sm font-semibold">
                    {{ $job->time_credits }} laika kredīti
                </div>
            </div>
            
            <div class="mb-4">
                <span class="text-gray-400 text-sm">Kategorija: </span>
                @php
                    $catLabel = $job->category;
                    foreach (($categories ?? []) as $topKey => $group) {
                        if ($topKey === $job->category) {
                            $catLabel = $group['label'];
                            break;
                        }
                        if (!empty($group['children']) && is_array($group['children'])) {
                            foreach ($group['children'] as $slug => $label) {
                                if ($slug === $job->category) {
                                    $catLabel = $label;
                                    break 2;
                                }
                            }
                        }
                    }
                @endphp
                <span class="text-white/80 text-sm">{{ $catLabel }}</span>
            </div>
            
            <div class="border-t border-gray-700 pt-4 mt-4">
                <h3 class="font-semibold text-white/90 mb-2">Apraksts</h3>
                <div class="text-gray-300 whitespace-pre-line">{{ $job->description }}</div>
            </div>
            
            @if(auth()->check() && auth()->id() !== $job->user_id)
                <div class="border-t border-gray-700 pt-6 mt-6">
                    <h3 class="font-semibold text-white/90 mb-4">Vēlaties palīdzēt?</h3>
                    
                    @php
                        $userSubmission = \App\Models\JobSubmission::where('job_listing_id', $job->id)
                            ->where('user_id', auth()->id())
                            ->first();
                            
                        $jobClaimed = \App\Models\JobSubmission::where('job_listing_id', $job->id)
                            ->whereIn('status', ['claimed', 'pending', 'approved'])
                            ->exists();
                        
                        // Check if job has any disputed submissions
                        $jobDisputed = \App\Models\JobSubmission::where('job_listing_id', $job->id)
                            ->where('dispute_status', '!=', 'none')
                            ->where('dispute_status', '!=', 'resolved')
                            ->exists();
                    @endphp
                    
                    @if($jobDisputed)
                        <div class="bg-red-500/20 text-red-300 p-4 rounded-md mb-4">
                            <p><strong>Darbs ir aizsaldzis</strong></p>
                            <p class="text-sm mt-1">Šis palīdzības pieprasījums ir aizsaldzis strīdu dēļ un nav pieejams.</p>
                        </div>
                    @elseif($jobClaimed && !$userSubmission)
                        <div class="bg-yellow-500/20 text-yellow-300 p-4 rounded-md mb-4">
                            <p>Šo palīdzības pieprasījumu jau ir saņēmis cits lietotājs.</p>
                        </div>
                    @elseif(!$userSubmission)
                        <!-- Step 1: Claim the job -->
                        <form method="POST" action="{{ route('job-submissions.claim') }}">
                            @csrf
                            <input type="hidden" name="job_id" value="{{ $job->id }}">

                            <p class="text-gray-300 mb-4">Lai pieteiktos šim palīdzības pieprasījumam, vispirms jāsaņem to. Tas rezervē pieprasījumu jums, kamēr sagatavojat savu pieteikumu.</p>

                            <div class="flex justify-between items-center">
                                <button type="submit" class="rounded-md px-4 py-2 text-sm font-medium text-gray-300 border border-gray-700 hover:text-neon-accent hover:bg-gray-800/80 transition-all duration-300">
                                    Saņemt šo palīdzības pieprasījumu
                                </button>
                                <button onclick="history.back()" class="text-gray-500 hover:text-gray-400 text-sm font-medium transition-colors duration-200">
                                    Atgriezties
                                </button>
                            </div>
                        </form>
                    @elseif($userSubmission->status === 'claimed')
                        <!-- Step 2: Complete the application -->
                        <div class="bg-green-500/20 text-green-300 p-4 rounded-md mb-4">
                            <p>Jūs esat saņēmis šo palīdzības pieprasījumu. Lūdzu, pabeidziet savu pieteikumu zemāk.</p>
                        </div>
                        
                        <div class="space-y-6">
                            <form method="POST" action="/job-submissions/{{ $userSubmission->id }}/cancel" id="cancel-form-{{ $userSubmission->id }}">
                                @csrf
                            </form>
                            
                            <form method="POST" action="/job-submissions/complete" enctype="multipart/form-data" onsubmit="console.log('Form submitting...', this);">
                                @csrf
                                <input type="hidden" name="submission_id" value="{{ $userSubmission->id }}">
                                
                                <div class="mb-4">
                                    <x-form-label for="message">Jūsu ziņojums</x-form-label>
                                    <div class="mt-2">
                                        <textarea name="message" id="message" rows="4" required
                                            class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500"
                                            placeholder="Paskaidrojiet, kā jūs varat palīdzēt ar šo pieprasījumu">{{ old('message') }}</textarea>
                                        @error('message')
                                            <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <x-form-label for="files">Pielikumi (neobligāti)</x-form-label>
                                    <div class="mt-2">
                                        <input type="file" name="files[]" id="files" multiple
                                            class="mt-1 block w-full text-gray-300"
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip,.gif,.mp4,.mp3,.avi,.psd,.ai,.sketch,.xd,.fig">
                                        <p class="mt-1 text-sm text-gray-400">Augšupielādējiet attiecīgus failus, lai atbalstītu savu pieteikumu (maks. 50MB katrs)</p>
                                        @error('files.*')
                                            <div class="mt-1 text-red-500 text-sm">{{ $message ?? 'Invalid file' }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="flex justify-between mt-6">
                                    <button type="button" onclick="document.getElementById('cancel-form-{{ $userSubmission->id }}').submit();" class="bg-red-700 hover:bg-red-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200 inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
Atcelt saņemšanu
                                    </button>
                                    
                                    <x-form-button>
Iesniegt pieteikumu
                                    </x-form-button>
                                </div>
                            </form>
                        </div>
                    @elseif($userSubmission->status === 'pending')
                        <div class="bg-yellow-500/20 text-yellow-300 p-4 rounded-md">
                            <p>Jūsu pieteikums ir iesniegts un gaida pārskatīšanu.</p>
                        </div>
                    @elseif($userSubmission->status === 'approved')
                        <div class="bg-green-500/20 text-green-300 p-4 rounded-md">
                            <p>Apsveicu! Jūsu pieteikums ir apstiprināts.</p>
                        </div>
                    @elseif($userSubmission->status === 'declined')
                        <div class="bg-red-500/20 text-red-300 p-4 rounded-md mb-4">
                            <p>Jūsu pieteikums tika noraidīts. Jūs varat atkal saņemt šo palīdzības pieprasījumu, ja vēlaties mēģināt vēlreiz.</p>
                        </div>
                        
                        <form method="POST" action="{{ route('job-submissions.claim') }}">
                            @csrf
                            <input type="hidden" name="job_id" value="{{ $job->id }}">

                            <button type="submit" class="rounded-md px-4 py-2 text-sm font-medium text-gray-300 border border-gray-700 hover:text-neon-accent hover:bg-gray-800/80 transition-all duration-300">
                                Saņemt vēlreiz
                            </button>
                        </form>
                    @endif
                </div>
            @endif
            
            @can('edit-job', $job)
                <div class="border-t border-gray-700 pt-6 mt-6 flex justify-between">
                    <x-button href="/jobs/{{ $job->id }}/edit" class="bg-gray-700 hover:bg-gray-600">Rediģēt pieprasījumu</x-button>
                    
                    <form method="POST" action="/jobs/{{ $job->id }}" onsubmit="return confirm('Vai esat pārliecināti, ka vēlaties dzēst šo palīdzības pieprasījumu?')">
                        @csrf
                        @method('DELETE')
                        <x-form-button class="bg-red-800 hover:bg-red-700">Dzēst pieprasījumu</x-form-button>
                    </form>
                </div>
            @endcan
            </div>
        </div>
    </div>
</x-layout>
