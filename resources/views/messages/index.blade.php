<x-layout>
    <x-slot:heading>Messages</x-slot:heading>

    <div class="max-w-3xl mx-auto">
        <div class="bg-gray-800/40 p-6 rounded-lg border border-gray-700 mb-6">
            <h3 class="text-lg font-semibold text-white/90">Inbox</h3>
            <div class="mt-4 space-y-3">
                @forelse($conversations as $c)
                    <a href="{{ route('messages.conversation', $c['other']->id) }}" class="block bg-gray-900/60 p-3 rounded border border-gray-700 hover:border-neon-accent">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="text-white/90 font-medium">{{ $c['other']->first_name }} {{ $c['other']->last_name }}</div>
                                <div class="text-gray-400 text-sm">{{ $c['latest']->body }}</div>
                            </div>
                            <div class="text-right text-sm">
                                <div class="text-gray-400">{{ $c['latest']->created_at->diffForHumans() }}</div>
                                @if($c['unread'] > 0)
                                    <div class="mt-1 inline-block bg-neon-accent text-black px-2 py-0.5 rounded text-xs">{{ $c['unread'] }}</div>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-gray-400">No conversations yet</div>
                @endforelse
            </div>
        </div>
    </div>
</x-layout>
