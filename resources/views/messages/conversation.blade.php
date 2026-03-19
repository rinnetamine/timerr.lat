<x-layout>
    <x-slot:heading>Conversation with 
        <a href="{{ route('people.show', $other->id) }}" class="hover:text-neon-accent transition-colors duration-200">
            {{ $other->first_name }} {{ $other->last_name }}
        </a>
    </x-slot:heading>

    <div class="mx-auto max-w-7xl grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- conversations sidebar -->
        <aside class="lg:col-span-1 bg-gray-800/30 rounded-lg border border-gray-700 p-4 h-[70vh] overflow-y-auto">
            <h3 class="text-lg font-semibold text-white/90 mb-3">Inbox</h3>
            <div class="space-y-3">
                @foreach($conversations as $c)
                    <a href="{{ route('messages.conversation', $c['other']->id) }}" class="flex items-center gap-3 p-3 rounded hover:bg-gray-900/60 border border-transparent hover:border-neon-accent transition {{ $other->id === $c['other']->id ? 'bg-gray-900/60 border-neon-accent' : '' }}">
                        <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-white text-sm font-medium">{{ strtoupper(substr($c['other']->first_name,0,1) ?: 'U') }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center">
                                <div class="truncate font-medium text-white/90">
                                <a href="{{ route('people.show', $c['other']->id) }}" class="hover:text-neon-accent transition-colors duration-200">
                                    {{ $c['other']->first_name }} {{ $c['other']->last_name }}
                                </a>
                            </div>
                                <div class="text-xs text-gray-400">{{ $c['latest']->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="text-sm text-gray-400 truncate">{{ $c['latest']->body }}</div>
                        </div>
                        @if($c['unread'] > 0)
                            <div class="ml-2">
                                <span class="inline-block w-2 h-2 rounded-full bg-neon-accent" aria-hidden="true"></span>
                            </div>
                        @endif
                    </a>
                @endforeach
            </div>
        </aside>

        <!-- conversation panel -->
        <div class="lg:col-span-2 bg-gray-800/30 rounded-lg border border-gray-700 p-0 flex flex-col h-[70vh] overflow-hidden">
            <header class="px-6 py-4 flex items-center justify-between border-b border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center text-white text-lg font-semibold">{{ strtoupper(substr($other->first_name,0,1) ?: 'U') }}</div>
                    <div>
                        <div class="font-semibold text-white/90">
                            <a href="{{ route('people.show', $other->id) }}" class="hover:text-neon-accent transition-colors duration-200">
                                {{ $other->first_name }} {{ $other->last_name }}
                            </a>
                        </div>
                        <div class="text-sm text-gray-400">@if($other->email) {{ $other->email }} @endif</div>
                    </div>
                </div>
                <div class="text-sm text-gray-400">
                    <a href="/people/{{ $other->id }}" class="hover:text-neon-accent">View profile</a>
                </div>
            </header>

            <div class="flex-1 p-6 overflow-y-auto space-y-4" id="messages-list">
                @foreach($messages as $m)
                    <div class="flex {{ $m->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[70%] px-4 py-2 rounded-lg {{ $m->sender_id === auth()->id() ? 'bg-neon-accent text-black' : 'bg-gray-800/50 text-gray-100 border border-gray-700' }}">
                            <div class="text-sm leading-relaxed">{{ $m->body }}</div>
                            <div class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                                <span>{{ $m->created_at->format('M j, H:i') }}</span>
                                @if($m->recipient_id === auth()->id() && is_null($m->read_at))
                                    <span class="inline-block w-2 h-2 rounded-full bg-neon-accent" title="Unread"></span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('messages.store') }}" class="px-6 py-4 border-t border-gray-700 bg-gray-800/40">
                @csrf
                <input type="hidden" name="recipient_id" value="{{ $other->id }}">
                <div class="flex gap-3">
                    <textarea name="body" rows="2" required class="flex-1 rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2" placeholder="Write a message..."></textarea>
                    <button type="submit" class="bg-neon-accent text-black px-4 py-2 rounded">Send</button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
