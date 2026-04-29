<x-layout>
    <x-slot:heading>
        Ierakstīties
    </x-slot:heading>

    <div class="mx-auto max-w-5xl overflow-hidden rounded-lg border border-gray-700 bg-gray-900/45 backdrop-blur-sm">
        <div class="grid grid-cols-1 lg:grid-cols-[0.9fr_1.1fr]">
            <aside class="border-b border-gray-700 bg-gray-950/45 p-8 lg:border-b-0 lg:border-r">
                <div class="inline-flex rounded-md border border-neon-accent/30 bg-neon-accent/10 px-3 py-2 text-sm font-semibold text-neon-accent">
                    Timerr konts
                </div>
                <h2 class="mt-6 text-3xl font-bold leading-tight text-white/95">Atgriezies pie savas laika apmaiņas.</h2>
                <p class="mt-4 text-gray-300">Pārvaldi pieteikumus, kredītus, ziņas un pakalpojumus vienā vietā.</p>

                <div class="mt-8 divide-y divide-gray-800 border-y border-gray-800">
                    <div class="py-4">
                        <div class="text-sm font-semibold text-white/90">Ātra piekļuve</div>
                        <p class="mt-1 text-sm text-gray-400">Turpini sarunas un seko saviem pieteikumiem.</p>
                    </div>
                    <div class="py-4">
                        <div class="text-sm font-semibold text-white/90">Laika kredīti</div>
                        <p class="mt-1 text-sm text-gray-400">Redzi, ko vari nopelnīt un izmantot tālāk.</p>
                    </div>
                    <div class="py-4">
                        <div class="text-sm font-semibold text-white/90">Uzticama kopiena</div>
                        <p class="mt-1 text-sm text-gray-400">Strādā ar cilvēkiem, kuru profils un aktivitāte ir redzama.</p>
                    </div>
                </div>
            </aside>

            <div class="p-8">
            <form method="POST" action="/login">
                @csrf

                <div class="space-y-12">
                    <div class="border-b border-gray-700 pb-12">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <x-form-field>
                                <x-form-label for="email" class="text-gray-300">E-pasts</x-form-label>

                                <div class="mt-2">
                                    <x-form-input name="email" id="email" type="email" :value="old('email')" required class="text-gray-300" />
                                    <x-form-error name="email" class="text-gray-300" />
                                </div>
                            </x-form-field>

                            <x-form-field>
                                <x-form-label for="password" class="text-gray-300">Parole</x-form-label>

                                <div class="mt-2">
                                    <x-form-input name="password" id="password" type="password" required class="text-gray-300" />
                                    <x-form-error name="password" class="text-gray-300" />
                                </div>
                            </x-form-field>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-x-6">
                    <a href="/" class="text-gray-300 hover:text-neon-accent transition-colors duration-200">Atcelt</a>
                    <x-form-button class="text-gray-300">Ierakstīties</x-form-button>
                </div>
            </form>
            </div>
        </div>
    </div>
</x-layout>
