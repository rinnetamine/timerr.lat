<x-layout>
    <x-slot:heading>
        Create Service Listing
    </x-slot:heading>

    <div class="max-w-2xl mx-auto">
        <div class="bg-gray-800/40 backdrop-blur-sm p-8 rounded-lg border border-gray-700">
            <form method="POST" action="/jobs">
                @csrf

                <div class="space-y-12">
                    <div class="border-b border-gray-700 pb-12">
                        <h2 class="text-xl font-semibold leading-7 text-white/90">Create a New Service</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-300">Share your skills with the community</p>

                        @error('error')
                            <div class="mt-2 text-red-500 text-sm">{{ $message }}</div>
                        @enderror

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <x-form-field>
                                <x-form-label for="title">Service Title</x-form-label>

                                <div class="mt-2">
                                    <x-form-input name="title" id="title" placeholder="e.g., Web Design, Language Tutoring" value="{{ old('title') }}" />
                                    <x-form-error name="title" />
                                </div>
                            </x-form-field>

                            <x-form-field>
                                <x-form-label for="description">Description</x-form-label>

                                <div class="mt-2">
                                    <textarea name="description" id="description" rows="4" required
                                            class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500"
                                            placeholder="Describe your service in detail">{{ old('description') }}</textarea>
                                    <x-form-error name="description" />
                                </div>
                            </x-form-field>

                            <x-form-field>
                                <x-form-label for="time_credits">Time Credits Required</x-form-label>
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-sm text-gray-400">Your current balance: {{ auth()->user()->time_credits }} credits</p>
                                </div>
                                <div class="mt-2">
                                    <x-form-input type="number" name="time_credits" id="time_credits" min="1" max="{{ auth()->user()->time_credits }}" value="{{ old('time_credits') }}" placeholder="e.g., 2" />
                                    <p class="mt-1 text-sm text-gray-400">These credits will be deducted from your account and reserved for the person who completes this job.</p>
                                    <x-form-error name="time_credits" />
                                </div>
                            </x-form-field>

                            <x-form-field>
                                <x-form-label for="category">Category</x-form-label>
                                <div class="mt-2">
                                    <select name="category" id="category" required
                                            class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent">
                                        <option value="">Select a category</option>
                                        <option value="creative" {{ old('category') == 'creative' ? 'selected' : '' }}>Creative Services</option>
                                        <option value="education" {{ old('category') == 'education' ? 'selected' : '' }}>Education</option>
                                        <option value="professional" {{ old('category') == 'professional' ? 'selected' : '' }}>Professional Services</option>
                                        <option value="technology" {{ old('category') == 'technology' ? 'selected' : '' }}>Technology</option>
                                        <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    <x-form-error name="category" />
                                </div>
                            </x-form-field>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-x-6">
                    <a href="/jobs" class="text-gray-300 hover:text-neon-accent transition-colors duration-200">Cancel</a>
                    <x-form-button>
                        Create Service
                    </x-form-button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
