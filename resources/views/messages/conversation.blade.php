<x-layout>
    <x-slot:heading>Conversation with {{ $other->first_name }} {{ $other->last_name }}</x-slot:heading>

    <div class="max-w-3xl mx-auto">
        <div class="bg-gray-800/40 p-6 rounded-lg border border-gray-700 mb-4">
            <h3 class="text-lg font-semibold text-white/90">Chat</h3>
            <div class="mt-4 space-y-3">
                @foreach($messages as $m)
                    <div class="flex {{ $m->sender_id === auth()->id() ? 'justify-end' : '' }}">
                        <div class="max-w-xl px-4 py-2 rounded-lg {{ $m->sender_id === auth()->id() ? 'bg-neon-accent text-black' : 'bg-gray-900/60 text-gray-100' }}">
                            <div class="text-sm">{{ $m->body }}</div>
                            <div class="text-xs text-gray-400 mt-1">{{ $m->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-gray-800/40 p-4 rounded-lg border border-gray-700">
            <form method="POST" action="{{ route('messages.store') }}">
                @csrf
                <input type="hidden" name="recipient_id" value="{{ $other->id }}">
                <textarea name="body" rows="4" required class="w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2" placeholder="Type your message..."></textarea>
                <div class="mt-3 flex justify-end">
                    <button type="submit" class="bg-neon-accent text-black px-4 py-2 rounded">Send</button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
