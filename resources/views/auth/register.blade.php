<x-layout>
    <x-slot:heading>
        Reģistrēties
    </x-slot:heading>

    <div class="mx-auto max-w-6xl overflow-hidden rounded-lg border border-gray-700 bg-gray-900/45 backdrop-blur-sm">
        <div class="grid grid-cols-1 lg:grid-cols-[0.85fr_1.15fr]">
            <aside class="border-b border-gray-700 bg-gray-950/45 p-8 lg:border-b-0 lg:border-r">
                <div class="inline-flex rounded-md border border-neon-accent/30 bg-neon-accent/10 px-3 py-2 text-sm font-semibold text-neon-accent">
                    Pievienojies Timerr
                </div>
                <h2 class="mt-6 text-3xl font-bold leading-tight text-white/95">Sāc ar prasmēm, nevis maku.</h2>
                <p class="mt-4 text-gray-300">Izveido profilu, saņem sākuma kredītus un atrodi cilvēkus, ar kuriem apmainīties ar palīdzību.</p>

                <div class="mt-8 grid grid-cols-2 gap-4 border-y border-gray-800 py-6">
                    <div>
                        <div class="text-3xl font-bold text-neon-accent">10</div>
                        <div class="mt-1 text-sm text-gray-400">sākuma kredīti</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-cyan-300">1:1</div>
                        <div class="mt-1 text-sm text-gray-400">stunda pret kredītu</div>
                    </div>
                </div>

                <p class="mt-6 text-sm leading-6 text-gray-400">Jo skaidrāks profils, jo vieglāk citiem uzticēties tavām prasmēm un piedāvājumiem.</p>
            </aside>

            <div class="p-8">
            <form method="POST" action="/register">
                @csrf

                <div class="space-y-12">
                    <div class="border-b border-gray-700 pb-12">
                        <h2 class="text-xl font-semibold leading-7 text-white/90">Izveido savu kontu</h2>
                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <x-form-field>
                                <x-form-label for="first_name">Vārds</x-form-label>

                                <div class="mt-2">
                                    <x-form-input name="first_name" id="first_name" maxlength="30" required />
                                    <x-form-error name="first_name" />
                                </div>
                            </x-form-field>

                            <x-form-field>
                                <x-form-label for="last_name">Uzvārds</x-form-label>

                                <div class="mt-2">
                                    <x-form-input name="last_name" id="last_name" maxlength="30" required />
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
    </div>
</x-layout>
