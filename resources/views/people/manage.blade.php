<x-layout>
    <x-slot:heading>User Management: {{ $user->first_name }} {{ $user->last_name }}</x-slot>

    <div class="max-w-6xl mx-auto">
        <!-- User Profile Card -->
        <div class="bg-gray-800/40 backdrop-blur-sm p-8 rounded-lg border border-gray-700 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="flex-shrink-0">
                        <div class="w-24 h-24 rounded-full bg-gray-700 flex items-center justify-center text-3xl text-white/90">
                            {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h2 class="text-2xl font-semibold text-white/90">{{ $user->first_name }} {{ $user->last_name }}</h2>
                            @if($user->role === 'admin')
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-500/20 text-purple-200 border border-purple-500">Admin</span>
                            @endif
                            @if($user->is_banned)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-500/20 text-red-200 border border-red-500">Banned</span>
                            @endif
                        </div>
                        <p class="text-gray-400">{{ $user->email }}</p>
                        <div class="mt-4 flex items-center gap-4">
                            <div class="inline-flex items-center px-4 py-2 rounded-full bg-gray-900/60 border border-gray-700">
                                <span class="text-neon-accent font-medium">{{ $user->time_credits }}</span>
                                <span class="ml-2 text-gray-400">Time Credits</span>
                            </div>
                            <div class="text-sm text-gray-400">
                                Member since {{ $user->created_at->format('M j, Y') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-400">{{ $user->jobs_count }} help requests</div>
                    <div class="text-sm text-gray-400">{{ $user->completed_jobs_count }} completed jobs</div>
                    <div class="text-sm text-gray-400">{{ $user->reviews_received_count }} reviews</div>
                </div>
            </div>
        </div>

        <!-- Admin Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Credit Management -->
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">Credit Management</h3>
                
                <form action="{{ route('admin.users.adjust-credits', $user) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Adjust Credits</label>
                        <div class="flex gap-2">
                            <input 
                                type="number" 
                                name="amount" 
                                class="flex-1 px-3 py-2 bg-gray-900 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-neon-accent focus:border-transparent"
                                placeholder="Amount (-1000 to 1000)"
                                min="-1000" 
                                max="1000" 
                                required>
                            <select 
                                name="quick_amount" 
                                class="px-3 py-2 bg-gray-900 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-neon-accent"
                                onchange="this.form.amount.value = this.value">
                                <option value="">Quick add</option>
                                <option value="10">+10</option>
                                <option value="25">+25</option>
                                <option value="50">+50</option>
                                <option value="100">+100</option>
                                <option value="-10">-10</option>
                                <option value="-25">-25</option>
                                <option value="-50">-50</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Reason</label>
                        <input 
                            type="text" 
                            name="description" 
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-neon-accent focus:border-transparent"
                            placeholder="Reason for adjustment"
                            required>
                    </div>
                    
                    <button type="submit" class="w-full bg-neon-accent text-black font-medium py-2 rounded-md hover:bg-neon-accent/80 transition-colors">
                        Adjust Credits
                    </button>
                </form>
            </div>

            <!-- Account Status -->
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">Account Status</h3>
                
                @if($user->is_banned)
                    <div class="mb-4 p-4 bg-red-500/10 border border-red-500 rounded-md">
                        <div class="text-red-200 font-medium mb-2">User is Banned</div>
                        <div class="text-red-300 text-sm mb-2">Reason: {{ $user->ban_reason ?? 'No reason provided' }}</div>
                        @if($user->banned_at)
                            <div class="text-red-300 text-sm">Banned on: {{ $user->banned_at->format('M j, Y \a\t g:i A') }}</div>
                        @endif
                    </div>
                    
                    <form action="{{ route('admin.users.unban', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 text-white font-medium py-2 rounded-md hover:bg-green-700 transition-colors">
                            Unban User
                        </button>
                    </form>
                @else
                    <div class="mb-4 p-4 bg-green-500/10 border border-green-500 rounded-md">
                        <div class="text-green-200 font-medium">Account is Active</div>
                    </div>
                    
                    @if(!$user->isAdmin())
                        <button 
                            onclick="document.getElementById('banForm').classList.toggle('hidden')"
                            class="w-full bg-red-600 text-white font-medium py-2 rounded-md hover:bg-red-700 transition-colors mb-2">
                            Ban User
                        </button>
                        
                        <form id="banForm" class="hidden space-y-4" action="{{ route('admin.users.ban', $user) }}" method="POST">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Ban Reason</label>
                                <textarea 
                                    name="reason" 
                                    rows="3" 
                                    class="w-full px-3 py-2 bg-gray-900 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-neon-accent focus:border-transparent"
                                    placeholder="Provide a reason for the ban..."
                                    required></textarea>
                            </div>
                            
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-red-600 text-white font-medium py-2 rounded-md hover:bg-red-700 transition-colors">
                                    Confirm Ban
                                </button>
                                <button 
                                    type="button"
                                    onclick="document.getElementById('banForm').classList.add('hidden')"
                                    class="flex-1 bg-gray-600 text-white font-medium py-2 rounded-md hover:bg-gray-700 transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-gray-400 text-sm">Cannot ban admin users</div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 mt-6">
            <h3 class="text-lg font-semibold text-white mb-4">Recent Transactions ({{ $user->transactions_count }} total)</h3>
            
            @if($user->transactions->count() > 0)
                <div class="space-y-2">
                    @foreach($user->transactions as $transaction)
                        <div class="flex items-center justify-between p-3 bg-gray-900/60 rounded border border-gray-700">
                            <div>
                                <div class="text-white">{{ $transaction->description }}</div>
                                <div class="text-gray-400 text-sm">{{ $transaction->created_at->format('M j, Y \a\t g:i A') }}</div>
                            </div>
                            <div class="text-lg font-medium {{ $transaction->amount > 0 ? 'text-green-400' : 'text-red-400' }}">
                                {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }}
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if($user->transactions_count > 10)
                    <div class="mt-4 text-center">
                        <a href="#" class="text-neon-accent hover:text-neon-accent/80 text-sm">
                            View all transactions →
                        </a>
                    </div>
                @endif
            @else
                <div class="text-gray-400 text-center py-8">No transactions yet</div>
            @endif
        </div>

        <!-- Navigation -->
        <div class="mt-6 flex justify-between">
            <a href="{{ route('people.show', $user) }}" class="text-gray-400 hover:text-white transition-colors">
                ← View Public Profile
            </a>
            <a href="{{ route('people.index') }}" class="text-gray-400 hover:text-white transition-colors">
                ← Back to Users
            </a>
        </div>
    </div>
</x-layout>
