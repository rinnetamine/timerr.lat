<x-layout>
    <x-slot:heading>Contact Messages</x-slot:heading>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gray-800/40 backdrop-blur-sm rounded-lg border border-gray-700 p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-200">Contact Messages</h2>
                <div class="flex space-x-4">
                    <a href="{{ route('admin.contact') }}?status=unread" 
                       class="px-4 py-2 rounded-md border border-gray-700 text-gray-300 hover:text-neon-accent hover:bg-gray-800/80">
                        Unread Messages
                    </a>
                    <a href="{{ route('admin.contact') }}?status=all" 
                       class="px-4 py-2 rounded-md border border-gray-700 text-gray-300 hover:text-neon-accent hover:bg-gray-800/80">
                        All Messages
                    </a>
                </div>
            </div>

            <div class="space-y-4">
                @foreach($messages as $message)
                    <div class="bg-gray-900/40 backdrop-blur-sm rounded-lg border border-gray-700 p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-medium text-gray-200">{{ $message->subject }}</h3>
                                <p class="text-sm text-gray-400">
                                    From: {{ $message->name }} <br>
                                    Email: {{ $message->email }} <br>
                                    @if($message->user)
                                        User: {{ $message->user->name }}
                                    @endif
                                </p>
                                <p class="mt-2 text-gray-300">{{ $message->message }}</p>
                            </div>
                            <div class="flex flex-col items-end space-y-2">
                                <span class="px-2 py-1 rounded-full text-xs {{ $message->status === 'unread' ? 'bg-red-900/40 text-red-300' : 'bg-green-900/40 text-green-300' }}">
                                    {{ ucfirst($message->status) }}
                                </span>
                                <div class="flex space-x-2">
                                    <form action="{{ route('admin.contact.mark-read', $message) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="text-gray-300 hover:text-green-300 hover:bg-gray-800/80 p-2 rounded-md border border-gray-700">
                                            Mark Read
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.contact.mark-unread', $message) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="text-gray-300 hover:text-red-300 hover:bg-gray-800/80 p-2 rounded-md border border-gray-700">
                                            Mark Unread
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <p class="mt-4 text-sm text-gray-400">
                            Sent: {{ $message->created_at->format('F j, Y, g:i a') }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layout>
