<x-layout>
    <x-slot:heading>
        Saruna ar 
        <a href="{{ route('people.show', $other->id) }}" class="hover:text-neon-accent transition-colors duration-200">
            {{ $other->first_name }} {{ $other->last_name }}
        </a>
    </x-slot:heading>

    <style>
        .static-sidebar {
            position: sticky;
            top: 1rem;
            height: calc(100vh - 2rem);
            overflow-y: auto;
        }
        
        .chat-container {
            height: calc(100vh - 2rem);
            display: flex;
            flex-direction: column;
        }
        
        .messages-list {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .message-form {
            border-top: 1px solid rgba(75, 85, 99, 0.5);
            padding: 1rem;
        }
        
        @media (max-width: 1024px) {
            .static-sidebar {
                position: relative;
                top: 0;
                height: auto;
                max-height: 50vh;
            }
            
            .chat-container {
                height: auto;
                min-height: 50vh;
            }
        }
    </style>

    <div class="mx-auto max-w-7xl grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- SIDEBAR -->
        <aside class="lg:col-span-1 bg-gray-800/30 rounded-lg border border-gray-700 p-4 static-sidebar">
            <h3 class="text-lg font-semibold text-white/90 mb-3">Iesūtne</h3>

            <div class="space-y-3">
                @foreach($conversations as $c)

                    <div 
                        onclick="window.location='{{ route('messages.conversation', $c['other']->id) }}'" 
                        class="cursor-pointer flex items-center gap-3 p-3 rounded border transition
                        {{ $other->id === $c['other']->id 
                            ? 'bg-gray-900/60 border-neon-accent' 
                            : 'border-transparent hover:bg-gray-900/60 hover:border-neon-accent' }}"
                    >

                        <!-- Avatar -->
                        <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-white text-sm font-medium">
                            {{ strtoupper(substr($c['other']->first_name,0,1) ?: 'U') }}
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center gap-2">

                                <!-- Name -->
                                <div class="truncate font-medium text-white/90 flex items-center gap-2">
                                    <a 
                                        href="{{ route('people.show', $c['other']->id) }}" 
                                        onclick="event.stopPropagation()" 
                                        class="hover:text-neon-accent transition-colors duration-200"
                                    >
                                        {{ $c['other']->first_name }} {{ $c['other']->last_name }}
                                    </a>
                                    @if(isset($c['job_relationship']))
                                        @if($c['job_relationship'] === 'worker')
                                            <span class="text-xs bg-blue-500/20 text-blue-300 px-1.5 py-0.5 rounded">Strādnieks</span>
                                        @else
                                            <span class="text-xs bg-green-500/20 text-green-300 px-1.5 py-0.5 rounded">Klients</span>
                                        @endif
                                    @endif
                                </div>

                                <!-- Time -->
                                <div class="text-xs text-gray-400 whitespace-nowrap">
                                    {{ $c['latest']->created_at->diffForHumans() }}
                                </div>
                            </div>

                            <!-- Last message -->
                            <div class="text-sm text-gray-400 truncate">
                                {{ $c['latest']->body }}
                            </div>
                        </div>

                        <!-- Unread -->
                        @if($c['unread'] > 0)
                            <span class="inline-block w-2 h-2 rounded-full bg-neon-accent"></span>
                        @endif

                    </div>

                @endforeach
            </div>
        </aside>


        <!-- CHAT PANEL -->
        <div class="lg:col-span-2 bg-gray-800/30 rounded-lg border border-gray-700 chat-container">

            <!-- HEADER -->
            <header class="px-6 py-4 flex items-center justify-between border-b border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center text-white text-lg font-semibold">
                        {{ strtoupper(substr($other->first_name,0,1) ?: 'U') }}
                    </div>
                    <div>
                        <div class="font-semibold text-white/90">
                            <a href="{{ route('people.show', $other->id) }}" class="hover:text-neon-accent transition-colors duration-200">
                                {{ $other->first_name }} {{ $other->last_name }}
                            </a>
                            @if(!empty($jobRelationships))
                                <span class="ml-2">
                                    @foreach($jobRelationships as $rel)
                                        @if($rel['role'] === 'worker')
                                            <span class="text-xs bg-blue-500/20 text-blue-300 px-2 py-1 rounded">Strādnieks</span>
                                        @else
                                            <span class="text-xs bg-green-500/20 text-green-300 px-2 py-1 rounded">Klients</span>
                                        @endif
                                    @endforeach
                                </span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-400">
                            @if($other->email) {{ $other->email }} @endif
                        </div>
                    </div>
                </div>

                <a href="{{ route('people.show', $other->id) }}" class="text-sm text-gray-400 hover:text-neon-accent">
                    Skatīt profilu
                </a>
            </header>


            <!-- MESSAGES -->
            <div id="messages-list" class="messages-list">
                @foreach($messages as $m)

                    <div class="flex {{ $m->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">

                        <div class="max-w-[70%] px-4 py-2 rounded-lg
                            {{ $m->sender_id === auth()->id() 
                                ? 'bg-neon-accent text-black' 
                                : 'bg-gray-800/50 text-gray-100 border border-gray-700' }}"
                        >

                            <div class="text-sm leading-relaxed break-words">
                                {{ $m->body }}
                            </div>

                            @if($m->files->count() > 0)
                                <div class="mt-2 space-y-2">
                                    @foreach($m->files as $file)
                                        @if($file->isImage())
                                            <div class="relative group">
                                                <img src="{{ $file->url }}" 
                                                     alt="{{ $file->file_name }}" 
                                                     class="max-w-xs rounded cursor-pointer hover:opacity-90 transition"
                                                     onclick="window.open('{{ $file->url }}', '_blank')">
                                                <div class="absolute bottom-1 right-1 bg-black/70 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition">
                                                    {{ $file->formatted_size }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-2 text-xs bg-gray-700/50 rounded px-3 py-2 hover:bg-gray-700/70 transition">
                                                @if($file->isPdf())
                                                    <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20M10,19L12,15H9V10H15V15L13,19H10Z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                    </svg>
                                                @endif
                                                <a href="{{ $file->url }}" 
                                                   target="_blank" 
                                                   class="text-neon-accent hover:underline flex-1 truncate">
                                                    {{ $file->file_name }}
                                                </a>
                                                <span class="text-gray-400 whitespace-nowrap">{{ $file->formatted_size }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            <div class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                                <span>{{ $m->created_at->translatedFormat('j. M, H:i') }}</span>

                                @if($m->recipient_id === auth()->id() && is_null($m->read_at))
                                    <span class="inline-block w-2 h-2 rounded-full bg-neon-accent"></span>
                                @endif
                            </div>

                        </div>
                    </div>

                @endforeach
            </div>


            <!-- INPUT -->
            <form method="POST" action="{{ route('messages.store') }}" class="message-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="recipient_id" value="{{ $other->id }}">

                <div class="space-y-3">
                    <textarea 
                        name="body" 
                        rows="2" 
                        class="w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:border-neon-accent"
                        placeholder="Rakstiet ziņojumu..."
                    >{{ old('body') }}</textarea>

                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <input type="file" name="files[]" multiple id="file-input">
                        </div>
                        <button type="submit" class="bg-neon-accent text-black px-4 py-2 rounded hover:opacity-90 transition">
                            Sūtīt
                        </button>
                    </div>
                    @if($errors->has('files') || $errors->has('files.*'))
                        <p class="text-sm text-red-300">{{ $errors->first('files') ?: $errors->first('files.*') }}</p>
                    @endif
                    <p class="text-xs text-gray-400">Varat sūtīt ziņu, pielikumu vai abus kopā. Maksimālais izmērs vienam failam: 20 MB.</p>
                                    </div>
            </form>

        </div>
    </div>


    <!-- AUTO SCROLL -->
    <script>
        const el = document.getElementById('messages-list');
        if (el) {
            el.scrollTop = el.scrollHeight;
        }
    </script>

</x-layout>
