<x-layout>
    <x-slot:heading>
        Log In
    </x-slot:heading>

    <div class="max-w-2xl mx-auto">
        <div class="bg-gray-800/40 backdrop-blur-sm p-8 rounded-lg border border-gray-700">
            <form method="POST" action="/login">
                @csrf

                <div class="space-y-12">
                    <div class="border-b border-gray-700 pb-12">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <x-form-field>
                                <x-form-label for="email" class="text-gray-300">Email</x-form-label>

                                <div class="mt-2">
                                    <x-form-input name="email" id="email" type="email" :value="old('email')" required class="text-gray-300" />
                                    <x-form-error name="email" class="text-gray-300" />
                                </div>
                            </x-form-field>

                            <x-form-field>
                                <x-form-label for="password" class="text-gray-300">Password</x-form-label>

                                <div class="mt-2">
                                    <x-form-input name="password" id="password" type="password" required class="text-gray-300" />
                                    <x-form-error name="password" class="text-gray-300" />
                                </div>
                            </x-form-field>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-x-6">
                    <a href="/" class="text-gray-300 hover:text-neon-accent transition-colors duration-200">Cancel</a>
                    <x-form-button class="text-gray-300">Log In</x-form-button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
