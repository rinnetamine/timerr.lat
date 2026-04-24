<x-layout>
    <x-slot:heading>
        Mans profils
    </x-slot:heading>

    <!-- main profile container with spacing between sections -->
    <div class="space-y-8">
        <!-- User Info Card -->
        <div class="bg-gray-800/40 backdrop-blur-sm p-8 rounded-lg border border-gray-700">
            <div class="flex items-center space-x-6">
                <div class="flex-shrink-0">
                    <!-- profile avatar showing first letter of first and last name -->
                    <div class="w-24 h-24 rounded-full bg-gray-700 flex items-center justify-center text-3xl text-white/90">
                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h2 class="text-2xl font-semibold text-white/90">{{ $user->first_name }} {{ $user->last_name }}</h2>
                        @if($user->reviews_received_rating_avg && $user->reviews_received_rating_avg > 0)
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($user->reviews_received_rating_avg))
                                        <span class="text-yellow-400 text-lg">★</span>
                                    @elseif($i - 0.5 <= $user->reviews_received_rating_avg)
                                        <span class="text-yellow-400 text-lg">☆</span>
                                    @else
                                        <span class="text-gray-600 text-lg">★</span>
                                    @endif
                                @endfor
                                <span class="text-xs text-gray-400 ml-1">({{ number_format($user->reviews_received_rating_avg, 1) }})</span>
                            </div>
                        @else
                            <span class="text-sm text-gray-500">Nav vērtējuma</span>
                        @endif
                    </div>
                    <p class="text-gray-400">{{ $user->email }}</p>
                    <div class="mt-4 inline-flex items-center px-4 py-2 rounded-full bg-gray-900/60 border border-gray-700">
                        <span class="text-neon-accent font-medium">{{ $user->time_credits }}</span>
                        <span class="ml-2 text-gray-400">Laika kredīti</span>
                    </div>
                    
                    <button 
                        onclick="openPasswordModal()"
                        class="mt-3 px-3 py-2 bg-orange-600 text-white font-medium rounded-md hover:bg-orange-700 transition-colors text-sm">
                        Mainīt paroli
                    </button>
                </div>
                <div class="flex-shrink-0 text-right">
                    <div class="text-sm text-gray-400">{{ $user->jobs_count ?? $user->jobs()->count() }} palīdzības pieprasījumi</div>
                    <div class="text-sm text-gray-400">{{ $user->completed_jobs_count ?? $user->completedJobsCount() }} pabeigti darbi</div>
                </div>
            </div>
        </div>

        <!-- Services Section -->
        <div id="passwordModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-4 border w-96 shadow-lg rounded-md bg-gray-800 border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white/90">Mainīt paroli</h3>
                    <button 
                        onclick="closePasswordModal()"
                        class="text-gray-400 hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('profile.change-password') }}" method="POST" class="space-y-4">
                    @csrf
                    @if ($errors->any())
                        <div class="bg-red-500/10 border border-red-500 rounded-md p-3 mb-4">
                            @foreach ($errors->all() as $error)
                                <div class="text-red-300 text-sm">{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="bg-green-500/10 border border-green-500 rounded-md p-3 mb-4">
                            <div class="text-green-300 text-sm">{{ session('success') }}</div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Esošā parole</label>
                        <input 
                            type="password" 
                            name="current_password" 
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-neon-accent focus:border-transparent"
                            placeholder="Ievadiet esošo paroli"
                            required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Jaunā parole</label>
                        <input 
                            type="password" 
                            name="password" 
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-neon-accent focus:border-transparent"
                            placeholder="Ievadiet jauno paroli"
                            minlength="6"
                            required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Apstipriniet jauno paroli</label>
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-neon-accent focus:border-transparent"
                            placeholder="Apstipriniet jauno paroli"
                            minlength="6"
                            required>
                    </div>
                    
                    <div class="bg-blue-500/10 border border-blue-500 rounded-md p-3">
                        <div class="text-blue-200 font-medium mb-2">Paroles prasības:</div>
                        <ul class="list-disc list-inside text-sm text-blue-300 space-y-1">
                            <li>Vismaz 6 rakstzīmes</li>
                            <li>Vismaz 1 cipars (0-9)</li>
                            <li>Vismaz 1 speciāla rakstzīme (!@#$%^&*()_+=-[]{}:;"'<>,.?/)</li>
                        </ul>
                    </div>
                    
                    <div class="flex gap-2 pt-4">
                        <button 
                            type="button"
                            onclick="closePasswordModal()"
                            class="flex-1 px-4 py-2 bg-gray-600 text-white font-medium rounded-md hover:bg-gray-700 transition-colors">
                            Atcelt
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-neon-accent text-black font-medium rounded-md hover:bg-neon-accent/80 transition-colors">
                            Mainīt paroli
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Services Section -->
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-white/90">Mani pakalpojumi</h3>
                <x-button href="/jobs/create" class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300">
                    Izveidot jaunu pakalpojumu
                </x-button>
            </div>

            @if($services->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($services as $service)
                        <a href="/jobs/{{ $service->id }}" class="block group">
                            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 group-hover:border-neon-accent transition-colors duration-300">
                                <h4 class="text-lg font-medium text-white/90 group-hover:text-neon-accent transition-colors duration-300">{{ $service->title }}</h4>
                                <p class="mt-2 text-gray-400 line-clamp-3">{{ $service->description }}</p>
                                <div class="mt-4 flex justify-between items-center">
                                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-gray-900/60 border border-gray-700">
                                        <span class="text-neon-accent font-medium">{{ $service->time_credits }}</span>
                                        <span class="ml-2 text-gray-400 text-sm">Kredīti</span>
                                    </div>
                                    <span class="text-sm text-gray-500">{{ $service->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <span class="text-neon-accent/80 text-sm font-medium group-hover:text-neon-accent transition-colors duration-300 flex items-center">
                                        Skatīt detaļas
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-gray-800/40 backdrop-blur-sm rounded-lg border border-gray-700">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-400">Vēl nav pakalpojumu</h3>
                    <p class="mt-1 text-sm text-gray-500">Sāciet, izveidojot jaunu pakalpojumu.</p>
                    <div class="mt-6">
                        <x-button href="/jobs/create" class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300">
                            Izveidot jaunu pakalpojumu
                        </x-button>
                    </div>
                </div>
            @endif
        </div>

        <!-- My Applications Section -->
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-white/90">Mani pieteikumi</h3>
                <a href="/jobs" class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300 px-4 py-2 rounded-md text-sm font-medium">
                    Pārlūkot palīdzības pieprasījumus
                </a>
            </div>
            
            @if($sentSubmissions->count() > 0)
                <div class="space-y-4">
                    @foreach($sentSubmissions as $submission)
                        <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-medium text-white/90">{{ $submission->jobListing->title }}</h4>
                                    <p class="text-gray-400 text-sm mt-1">Publicēja {{ $submission->jobListing->user->first_name }} {{ $submission->jobListing->user->last_name }}</p>
                                </div>
                                <div>
                                    @if($submission->status === 'claimed')
                                        <span class="bg-blue-500/20 text-blue-300 px-3 py-1 rounded-full text-xs font-semibold">Saņēmis</span>
                                    @elseif($submission->status === 'pending')
                                        <span class="bg-yellow-500/20 text-yellow-300 px-3 py-1 rounded-full text-xs font-semibold">Gaida</span>
                                    @elseif($submission->status === 'approved')
                                        <span class="bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-xs font-semibold">Apstiprināts</span>
                                    @elseif($submission->status === 'declined')
                                        <span class="bg-red-500/20 text-red-300 px-3 py-1 rounded-full text-xs font-semibold">Noraidīts</span>
                                    @elseif($submission->status === 'admin_review')
                                        <span class="bg-purple-500/20 text-purple-300 px-3 py-1 rounded-full text-xs font-semibold">Administratora pārskatīšana</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($submission->status === 'claimed')
                                <div class="mt-3 text-gray-300">
                                    <p class="italic">Jūs esat saņēmis šo palīdzības pieprasījumu. Lūdzu, pabeidziet savu pieteikumu.</p>
                                </div>
                                <div class="mt-4">
                                    <a href="/jobs/{{ $submission->job_listing_id }}" class="text-neon-accent hover:text-neon-accent/80 transition-colors duration-200 text-sm font-medium">
                                        Pabeigt pieteikumu
                                    </a>
                                </div>
                            @else
                                <div class="mt-3 text-gray-300 text-sm">
                                    <p>{{ Str::limit($submission->message, 100) }}</p>
                                </div>
                                <div class="mt-4">
                                    <a href="/submissions/{{ $submission->id }}" class="text-neon-accent hover:text-neon-accent/80 transition-colors duration-200 text-sm font-medium">
                                        Skatīt detaļas
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-gray-800/40 backdrop-blur-sm rounded-lg border border-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-400">Vēl nav pieteikumu</h3>
                    <p class="mt-1 text-sm text-gray-500">Pārlūkojiet palīdzības pieprasījumus un piedāvājiet savu palīdzību.</p>
                    <div class="mt-6">
                        <a href="/jobs" class="inline-flex items-center px-4 py-2 border border-gray-700 rounded-md shadow-sm text-sm font-medium text-gray-300 bg-gray-900/60 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neon-accent">
                            Pārlūkot palīdzības pieprasījumus
                        </a>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Received Applications Section -->
        <div class="space-y-6">
            <h3 class="text-xl font-semibold text-white/90">Pieteikumi maniem palīdzības pieprasījumiem</h3>
            
            @if($receivedSubmissions->count() > 0)
                <div class="space-y-4">
                    @foreach($receivedSubmissions as $submission)
                        <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-medium text-white/90">{{ $submission->jobListing->title }}</h4>
                                    <p class="text-neon-accent text-sm mt-1">{{ $submission->user->first_name }} {{ $submission->user->last_name }} vēlas palīdzēt</p>
                                </div>
                                <div>
                                    @if($submission->status === 'claimed')
                                        <span class="bg-blue-500/20 text-blue-300 px-3 py-1 rounded-full text-xs font-semibold">Saņēmis</span>
                                    @elseif($submission->status === 'pending')
                                        <span class="bg-yellow-500/20 text-yellow-300 px-3 py-1 rounded-full text-xs font-semibold">Gaida</span>
                                    @elseif($submission->status === 'approved')
                                        <span class="bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-xs font-semibold">Apstiprināts</span>
                                    @elseif($submission->status === 'declined')
                                        <span class="bg-red-500/20 text-red-300 px-3 py-1 rounded-full text-xs font-semibold">Noraidīts</span>
                                    @elseif($submission->status === 'admin_review')
                                        <span class="bg-purple-500/20 text-purple-300 px-3 py-1 rounded-full text-xs font-semibold">Administratora pārskatīšana</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($submission->status === 'claimed')
                                <div class="mt-3 text-gray-300">
                                    <p class="italic">{{ $submission->user->first_name }} ir saņēmis šo palīdzības pieprasījumu, bet vēl nav pabeidzis savu pieteikumu.</p>
                                </div>
                            @else
                                <div class="mt-3 text-gray-300 text-sm">
                                    <p>{{ Str::limit($submission->message, 100) }}</p>
                                </div>
                            @endif
                            
                            <div class="mt-4">
                                <a href="/submissions/{{ $submission->id }}" class="text-neon-accent hover:text-neon-accent/80 transition-colors duration-200 text-sm font-medium">
                                    Skatīt pilnu pieteikumu
                                </a>
                                
                                @if($submission->status === 'pending')
                                    <div class="mt-3 flex space-x-2">
                                        <form method="POST" action="/submissions/{{ $submission->id }}/approve">
                                            @csrf
                                            <button type="submit" class="bg-green-700 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200">
                                                Apstiprināt
                                            </button>
                                        </form>
                                        
                                        <button type="button" onclick="document.getElementById('declineForm-{{ $submission->id }}').classList.remove('hidden')" class="bg-red-700 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200">
                                            Noraidīt
                                        </button>
                                        
                                        <form method="POST" action="/submissions/{{ $submission->id }}/decline" id="declineForm-{{ $submission->id }}" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
                                            @csrf
                                            <div class="bg-gray-800 p-6 rounded-lg max-w-md w-full">
                                                <h3 class="text-lg font-semibold text-white mb-4">Noraidīt pieteikumu</h3>
                                                
                                                <p class="text-gray-300 mb-4">
                                                    Šis pieteikums tiks nosūtīts admin pārskatīšanai. Lūdzu, norādiet iemeslu noraidīšanai:
                                                </p>
                                                
                                                <textarea name="admin_notes" rows="4" required
                                                    class="w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500"
                                                    placeholder="Paskaidrojiet, kāpēc noraidāt šo pieteikumu..."></textarea>
                                                
                                                <div class="mt-4 flex justify-end space-x-3">
                                                    <button type="button" onclick="document.getElementById('declineForm-{{ $submission->id }}').classList.add('hidden')" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                                                        Atcelt
                                                    </button>
                                                    <button type="submit" class="bg-red-700 hover:bg-red-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200">
                                                        Iesniegt un noraidīt
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-gray-800/40 backdrop-blur-sm rounded-lg border border-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-400">Vēl nav saņemtu pieteikumu</h3>
                    <p class="mt-1 text-sm text-gray-500">Kad kāds pieteiksies palīdzēt ar jūsu pieprasījumiem, tie parādīsies šeit.</p>
                </div>
            @endif
        </div>
        
        {{-- Reviews received --}}
        <div class="space-y-6">
            <h3 class="text-xl font-semibold text-white/90">Saņemtās atsauksmes</h3>
            @if($user->reviewsReceived()->count() === 0)
                <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 text-gray-400">
                    Vēl nav atsauksmju. Pabeidziet palīdzības pieprasījumus, lai saņemtu atsauksmes no citiem!
                </div>
            @else
                <div class="space-y-4">
                    @foreach($user->reviewsReceived as $review)
                        <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-sm text-gray-300">{{ $review->reviewer->first_name }} {{ $review->reviewer->last_name }} — <span class="text-neon-accent">{{ $review->rating }}/5</span></div>
                                    @if($review->comment)
                                        <div class="mt-2 text-gray-300 whitespace-pre-line">{{ $review->comment }}</div>
                                    @endif
                                    <div class="text-xs text-gray-500 mt-2">{{ $review->created_at->translatedFormat('j. M Y') }}</div>
                                </div>
                                <div class="flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $review->rating)
                                            <span class="text-yellow-400 text-sm">★</span>
                                        @else
                                            <span class="text-gray-600 text-sm">★</span>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        <!-- transaction history -->
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-white/90">Transakciju vēsture</h3>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('transactions.export') }}" class="bg-green-600/90 hover:bg-green-700/90 text-white px-3 py-1 rounded text-sm font-medium transition-colors duration-200">Skatīt kā HTML</a>
                    <a href="{{ route('transactions.download') }}" class="bg-green-600/90 hover:bg-green-700/90 text-white px-3 py-1 rounded text-sm font-medium transition-colors duration-200">Lejupielādēt PDF</a>
                    <a href="{{ route('transactions.csv') }}" class="bg-green-600/90 hover:bg-green-700/90 text-white px-3 py-1 rounded text-sm font-medium transition-colors duration-200">Lejupielādēt CSV</a>
                    <a href="{{ route('transactions.excel') }}" class="bg-green-600/90 hover:bg-green-700/90 text-white px-3 py-1 rounded text-sm font-medium transition-colors duration-200">Lejupielādēt Excel failu</a>
                </div>
            </div>
            <div class="bg-gray-800/40 backdrop-blur-sm rounded-lg border border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-900/60">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Datums</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Apraksts</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Kredīti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @forelse($transactions ?? [] as $transaction)
                                @if(!is_object($transaction))
                                    @continue
                                @endif
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                        {{ $transaction->created_at?->translatedFormat('j. M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                        {{ $transaction->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="{{ $transaction->amount > 0 ? 'text-green-400' : 'text-red-400' }}">
                                            {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-400">
                                        Vēl nav transakciju
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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

// Close modal when clicking outside
document.getElementById('passwordModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closePasswordModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closePasswordModal();
    }
});
</script>
