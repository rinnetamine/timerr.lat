<x-layout>
    <x-slot:heading>
        My Profile
    </x-slot:heading>

    <div class="space-y-8">
        <!-- User Info Card -->
        <div class="bg-gray-800/40 backdrop-blur-sm p-8 rounded-lg border border-gray-700">
            <div class="flex items-center space-x-6">
                <div class="flex-shrink-0">
                    <div class="w-24 h-24 rounded-full bg-gray-700 flex items-center justify-center text-3xl text-white/90">
                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                    </div>
                </div>
                <div>
                    <h2 class="text-2xl font-semibold text-white/90">{{ $user->first_name }} {{ $user->last_name }}</h2>
                    <p class="text-gray-400">{{ $user->email }}</p>
                    <div class="mt-4 inline-flex items-center px-4 py-2 rounded-full bg-gray-900/60 border border-gray-700">
                        <span class="text-neon-accent font-medium">{{ $user->time_credits }}</span>
                        <span class="ml-2 text-gray-400">Time Credits</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Section -->
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-white/90">My Services</h3>
                <x-button href="/jobs/create" class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300">
                    Create New Service
                </x-button>
            </div>

            @if($services->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($services as $service)
                        <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 hover:border-neon-accent transition-colors duration-300">
                            <h4 class="text-lg font-medium text-white/90">{{ $service->title }}</h4>
                            <p class="mt-2 text-gray-400 line-clamp-3">{{ $service->description }}</p>
                            <div class="mt-4 flex justify-between items-center">
                                <div class="inline-flex items-center px-3 py-1 rounded-full bg-gray-900/60 border border-gray-700">
                                    <span class="text-neon-accent font-medium">{{ $service->time_credits }}</span>
                                    <span class="ml-2 text-gray-400 text-sm">Credits</span>
                                </div>
                                <span class="text-sm text-gray-500">{{ $service->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-gray-800/40 backdrop-blur-sm rounded-lg border border-gray-700">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-400">No services yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new service.</p>
                    <div class="mt-6">
                        <x-button href="/jobs/create" class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300">
                            Create New Service
                        </x-button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Transaction History -->
        <div class="space-y-6">
            <h3 class="text-xl font-semibold text-white/90">Transaction History</h3>
            <div class="bg-gray-800/40 backdrop-blur-sm rounded-lg border border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-900/60">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Credits</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @forelse($transactions ?? [] as $transaction)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                        {{ $transaction?->created_at?->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                        {{ $transaction?->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="{{ isset($transaction->amount) && $transaction->amount > 0 ? 'text-green-400' : 'text-red-400' }}">
                                            {{ isset($transaction->amount) && $transaction->amount > 0 ? '+' : '' }}{{ $transaction?->amount }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-400">
                                        No transactions yet
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