<x-layout>
    <x-slot name="heading">File Dispute</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-gray-900/60 border border-gray-700 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4">Job Information</h2>
            <div class="space-y-3">
                <div>
                    <span class="text-gray-400">Job Title:</span>
                    <span class="text-white ml-2">{{ $submission->jobListing->title }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Posted by:</span>
                    <span class="text-white ml-2">{{ $submission->jobListing->user->first_name }} {{ $submission->jobListing->user->last_name }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Submitted by:</span>
                    <span class="text-white ml-2">{{ $submission->user->first_name }} {{ $submission->user->last_name }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Time Credits:</span>
                    <span class="text-neon-accent ml-2">{{ $submission->jobListing->time_credits }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Current Status:</span>
                    <span class="text-white ml-2">{{ ucfirst($submission->status) }}</span>
                </div>
            </div>
        </div>

        <form action="{{ route('disputes.store', $submission) }}" method="POST" class="bg-gray-900/60 border border-gray-700 rounded-lg p-6">
            @csrf
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-white mb-4">Dispute Details</h3>
                <p class="text-gray-300 mb-4">
                    Please provide a detailed explanation of why you are disputing this job submission. 
                    Be specific about the issues and provide any relevant evidence.
                </p>
                
                <x-form-field>
                    <x-form-label for="reason">Dispute Reason</x-form-label>
                    <textarea 
                        id="reason" 
                        name="reason" 
                        rows="6" 
                        class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-neon-accent focus:border-transparent"
                        placeholder="Describe the issue in detail..."
                        required>{{ old('reason') }}</textarea>
                    <x-form-error name="reason" />
                </x-form-field>

                <div class="mt-4 p-4 bg-yellow-500/10 border border-yellow-500 rounded-md">
                    <p class="text-yellow-200 text-sm">
                        <strong>Important:</strong> Filing a dispute will freeze this job submission until an admin reviews it. 
                        Both parties will be unable to make changes until the dispute is resolved.
                    </p>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('submissions.show', $submission) }}" 
                   class="px-4 py-2 text-gray-300 hover:text-white border border-gray-600 rounded-md hover:bg-gray-800 transition-colors duration-200">
                    Cancel
                </a>
                <x-form-button type="submit" class="bg-red-600 hover:bg-red-700 text-white">
                    Submit Dispute
                </x-form-button>
            </div>
        </form>
    </div>
</x-layout>
