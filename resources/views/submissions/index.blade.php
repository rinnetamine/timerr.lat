<x-layout>
    <x-slot:heading>
        Mani pieteikumi
    </x-slot:heading>

    <div class="max-w-4xl mx-auto space-y-10">
        <!-- Received Applications (for help requests you posted) -->
        <div>
            <h2 class="text-xl font-semibold text-white/90 mb-4">Pieteikumi jūsu palīdzības pieprasījumiem</h2>
            
            @if($receivedSubmissions->isEmpty())
                <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 text-gray-400">
                    Jūs vēl neesat saņēmis nevienu pieteikumu. Kad kāds pieteiksies palīdzēt ar jūsu pieprasījumiem, tie parādīsies šeit.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($receivedSubmissions as $submission)
                        <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-white/90">{{ $submission->jobListing->title }}</h3>
                                    <p class="text-neon-accent text-sm mt-1">
                                    <a href="{{ route('people.show', $submission->user->id) }}" class="hover:text-neon-accent/80 transition-colors duration-200">
                                        {{ $submission->user->first_name }} {{ $submission->user->last_name }}
                                    </a> vēlas palīdzēt
                                </p>
                                </div>
                                <div>
                                    @if($submission->status === 'claimed')
                                        <span class="bg-blue-500/20 text-blue-300 px-3 py-1 rounded-full text-xs font-semibold">Saņēmts</span>
                                    @elseif($submission->status === 'pending')
                                        <span class="bg-yellow-500/20 text-yellow-300 px-3 py-1 rounded-full text-xs font-semibold">Gaida</span>
                                    @elseif($submission->status === 'approved')
                                        <span class="bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-xs font-semibold">Apstiprināts</span>
                                    @elseif($submission->status === 'declined')
                                        <span class="bg-red-500/20 text-red-300 px-3 py-1 rounded-full text-xs font-semibold">Noraidīts</span>
                                    @elseif($submission->status === 'admin_review')
                                        <span class="bg-purple-500/20 text-purple-300 px-3 py-1 rounded-full text-xs font-semibold">Admin pārskatīšana</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mt-3 text-gray-300 text-sm">
                                <p>{{ Str::limit($submission->message, 100) }}</p>
                            </div>
                            
                            <div class="mt-4 flex justify-between items-center">
                                <a href="/submissions/{{ $submission->id }}" class="text-neon-accent hover:text-neon-accent/80 transition-colors duration-200 text-sm font-medium">
                                    Skatīt pilnu pieteikumu
                                </a>
                                
                                @if($submission->status === 'pending')
                                    <div class="flex space-x-2">
                                        <form method="POST" action="/submissions/{{ $submission->id }}/approve">
                                            @csrf
                                            <button type="submit" class="bg-green-700 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200">
                                                Apstiprināt
                                            </button>
                                        </form>
                                        
                                        <form method="POST" action="/submissions/{{ $submission->id }}/decline">
                                            @csrf
                                            <button type="submit" class="bg-red-700 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors duration-200">
                                                Noraidīt
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        <!-- Your Applications (that you sent to others) -->
        <div>
            <h2 class="text-xl font-semibold text-white/90 mb-4">Mani pieteikumi, lai palīdzētu citiem</h2>
            
            @if($sentSubmissions->isEmpty())
                <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 text-gray-400">
                    Jūs vēl neesat pieteicies nevienu palīdzības pieprasījumu. Pārlūkojiet palīdzības pieprasījumus un piedāvājiet savu palīdzību!
                </div>
            @else
                <div class="space-y-4">
                    @foreach($sentSubmissions as $submission)
                        <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-white/90">{{ $submission->jobListing->title }}</h3>
                                    <p class="text-gray-400 text-sm mt-1">Publicēja 
                                    <a href="{{ route('people.show', $submission->jobListing->user->id) }}" class="hover:text-neon-accent transition-colors duration-200">
                                        {{ $submission->jobListing->user->first_name }} {{ $submission->jobListing->user->last_name }}
                                    </a>
                                </p>
                                </div>
                                <div>
                                    @if($submission->status === 'claimed')
                                        <span class="bg-blue-500/20 text-blue-300 px-3 py-1 rounded-full text-xs font-semibold">Saņēmts</span>
                                    @elseif($submission->status === 'pending')
                                        <span class="bg-yellow-500/20 text-yellow-300 px-3 py-1 rounded-full text-xs font-semibold">Gaida</span>
                                    @elseif($submission->status === 'approved')
                                        <span class="bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-xs font-semibold">Apstiprināts</span>
                                    @elseif($submission->status === 'declined')
                                        <span class="bg-red-500/20 text-red-300 px-3 py-1 rounded-full text-xs font-semibold">Noraidīts</span>
                                    @elseif($submission->status === 'admin_review')
                                        <span class="bg-purple-500/20 text-purple-300 px-3 py-1 rounded-full text-xs font-semibold">Admin pārskatīšana</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mt-3 text-gray-300 text-sm">
                                <p>{{ Str::limit($submission->message, 100) }}</p>
                            </div>
                            
                            <div class="mt-4">
                                <a href="/submissions/{{ $submission->id }}" class="text-neon-accent hover:text-neon-accent/80 transition-colors duration-200 text-sm font-medium">
                                    Skatīt detaļas
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layout>
