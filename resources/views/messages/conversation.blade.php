<x-layout :hidePageHeader="true" :stretchMain="true">
    <x-slot:heading>Ziņojumi</x-slot:heading>

    <div class="mx-auto max-w-7xl grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- SIDEBAR -->
        <aside class="lg:col-span-1 bg-gray-800/30 rounded-lg border border-gray-700 p-4 h-[82vh] overflow-y-auto">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-white/90">Iesūtne</h3>
                <a href="{{ route('messages.create') }}" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-neon-accent/40 bg-neon-accent/10 text-neon-accent transition-colors hover:bg-neon-accent hover:text-black" title="Jauna saruna" aria-label="Jauna saruna">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"></path>
                    </svg>
                </a>
            </div>

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
                        <x-avatar :user="$c['other']" size="sm" />

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
        <div class="lg:col-span-2 bg-gray-800/30 rounded-lg border border-gray-700 flex flex-col h-[82vh] overflow-hidden">

            <!-- HEADER -->
            <header class="px-6 py-4 flex items-center justify-between border-b border-gray-700">
                <div class="flex items-center gap-4">
                    <x-avatar :user="$other" size="md" />
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
            <div id="messages-list" class="flex-1 overflow-y-auto p-6 flex flex-col gap-3">
                @foreach($messages as $m)

                    <div class="flex {{ $m->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">

                        <div class="max-w-[38%] xl:max-w-[34%] px-3 py-2 rounded-2xl
                            {{ $m->sender_id === auth()->id() 
                                ? 'bg-neon-accent text-black' 
                                : 'bg-gray-800/50 text-gray-100 border border-gray-700' }}"
                        >
                            @if($m->body)
                                <div class="text-[13px] leading-5 break-words">
                                    {{ $m->body }}
                                </div>
                            @endif

                            @if($m->files->count() > 0)
                                <div class="{{ $m->body ? 'mt-2' : '' }} space-y-2">
                                    @foreach($m->files as $file)
                                        @if($file->isImage())
                                            <div class="relative group">
                                                <img src="{{ $file->url }}" 
                                                     alt="{{ $file->file_name }}" 
                                                     class="max-w-[220px] rounded-xl cursor-pointer hover:opacity-90 transition"
                                                     onclick="window.open('{{ $file->url }}', '_blank')">
                                                <div class="absolute bottom-1 right-1 bg-black/70 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition">
                                                    {{ $file->formatted_size }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-2 text-[11px] bg-gray-700/50 rounded-xl px-3 py-2 hover:bg-gray-700/70 transition">
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

                            <div class="mt-1 flex items-center justify-end gap-2 text-[10px] leading-none {{ $m->sender_id === auth()->id() ? 'text-black/65' : 'text-gray-400' }}">
                                <span>{{ $m->created_at->translatedFormat('H:i') }}</span>

                                @if($m->recipient_id === auth()->id() && is_null($m->read_at))
                                    <span class="inline-block w-2 h-2 rounded-full bg-neon-accent"></span>
                                @endif
                            </div>

                        </div>
                    </div>

                @endforeach
            </div>


            <!-- INPUT -->
            <form method="POST" action="{{ route('messages.store') }}" class="border-t border-gray-700/50 p-3" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="recipient_id" value="{{ $other->id }}">

                <div class="space-y-2">
                    <div id="selected-files" class="hidden rounded-xl border border-gray-700 bg-gray-900/50 px-3 py-2">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-2 text-xs text-gray-300">
                                <svg class="h-4 w-4 shrink-0 text-neon-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21.44 11.05 12.25 20.24a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                                </svg>
                                <span id="selected-files-text" class="truncate"></span>
                            </div>
                            <button type="button" id="clear-files" class="shrink-0 rounded-full px-2 py-1 text-xs text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
                                Noņemt
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 rounded-full bg-gray-900/60 border border-gray-700 px-2 py-1.5 focus-within:border-neon-accent transition-colors">
                        <label for="file-input" class="shrink-0 inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-full text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 transition-colors" title="Pievienot failu" aria-label="Pievienot failu">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21.44 11.05 12.25 20.24a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                            </svg>
                        </label>
                        <input type="file" name="files[]" multiple id="file-input" class="sr-only">

                        <textarea
                            name="body"
                            rows="1"
                            class="min-h-8 flex-1 resize-none border-0 bg-transparent px-2 py-1.5 text-sm text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-0"
                            placeholder="Rakstiet ziņojumu..."
                        >{{ old('body') }}</textarea>

                        <button type="submit" class="shrink-0 rounded-full bg-neon-accent px-3.5 py-1.5 text-sm font-medium text-black hover:bg-neon-accent/90 transition-colors">
                            Sūtīt
                        </button>
                    </div>

                    @if($errors->has('files') || $errors->has('files.*'))
                        <p class="text-sm text-red-300">{{ $errors->first('files') ?: $errors->first('files.*') }}</p>
                    @endif
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

        const fileInput = document.getElementById('file-input');
        const selectedFiles = document.getElementById('selected-files');
        const selectedFilesText = document.getElementById('selected-files-text');
        const clearFiles = document.getElementById('clear-files');
        if (fileInput && selectedFiles && selectedFilesText) {
            const updateSelectedFiles = () => {
                const files = Array.from(fileInput.files || []);
                const names = files.slice(0, 2).map(file => file.name).join(', ');
                const suffix = files.length > 2 ? ` un vēl ${files.length - 2}` : '';

                selectedFilesText.textContent = files.length === 0 ? '' : `${names}${suffix}`;
                selectedFiles.classList.toggle('hidden', files.length === 0);
            };

            fileInput.addEventListener('change', () => {
                updateSelectedFiles();
            });

            clearFiles?.addEventListener('click', () => {
                fileInput.value = '';
                updateSelectedFiles();
            });
        }
    </script>

</x-layout>
