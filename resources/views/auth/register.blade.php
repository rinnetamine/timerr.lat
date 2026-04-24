<x-layout>
    <x-slot:heading>
        Reģistrēties
    </x-slot:heading>

    <div class="max-w-2xl mx-auto">
        <div class="bg-gray-800/40 backdrop-blur-sm p-8 rounded-lg border border-gray-700">
            <form method="POST" action="/register">
                @csrf

                <div class="space-y-12">
                    <div class="border-b border-gray-700 pb-12">
                        <h2 class="text-xl font-semibold leading-7 text-white/90">Izveido savu kontu</h2>
                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <x-form-field>
                                <x-form-label for="first_name">Vārds</x-form-label>

                                <div class="mt-2">
                                    <x-form-input name="first_name" id="first_name" required />
                                    <x-form-error name="first_name" />
                                </div>
                            </x-form-field>

                            <x-form-field>
                                <x-form-label for="last_name">Uzvārds</x-form-label>

                                <div class="mt-2">
                                    <x-form-input name="last_name" id="last_name" required />
                                    <x-form-error name="last_name" />
                                </div>
                            </x-form-field>

                            <x-form-field>
                                <x-form-label for="email">E-pasts</x-form-label>

                                <div class="mt-2">
                                    <x-form-input name="email" id="email" type="email" required />
                                    <x-form-error name="email" />
                                </div>
                            </x-form-field>

                            <x-form-field>
                                <x-form-label for="password">Parole</x-form-label>

                                <div class="mt-2">
                                    <x-form-input name="password" id="password" type="password" required />
                                    <x-form-error name="password" />
                                </div>
                                
                                <div class="mt-2 text-sm text-gray-400">
                                    Parolei jāsatur vismaz:
                                    <ul class="list-disc list-inside mt-1 space-y-1">
                                        <li>6 rakstzīmes minimums</li>
                                        <li>1 cipars (0-9)</li>
                                        <li>1 speciālā rakstzīme (!@#$%^&*()_+=-[]{}:;"'<>,.?/)</li>
                                    </ul>
                                </div>
                            </x-form-field>

                            <x-form-field>
                                <x-form-label for="password_confirmation">Apstiprināt paroli</x-form-label>

                                <div class="mt-2">
                                    <x-form-input name="password_confirmation" id="password_confirmation" type="password" required />
                                    <x-form-error name="password_confirmation" />
                                </div>
                            </x-form-field>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-x-6">
                    <a href="/" class="text-gray-300 hover:text-neon-accent transition-colors duration-200">Atcelt</a>
                    <x-form-button>Reģistrēties</x-form-button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
