{{-- Šis skats rāda darba sludinājuma izveides formu. --}}
<x-layout>
    <x-slot:heading>
        Lūdziet palīdzību
    </x-slot:heading>

    <div class="mx-auto max-w-6xl overflow-hidden rounded-lg border border-gray-700 bg-gray-900/45 backdrop-blur-sm">
        <div class="grid grid-cols-1 lg:grid-cols-[0.85fr_1.15fr]">
            <aside class="border-b border-gray-700 bg-gray-950/45 p-8 lg:border-b-0 lg:border-r">
                <div class="inline-flex rounded-md border border-neon-accent/30 bg-neon-accent/10 px-3 py-2 text-sm font-semibold text-neon-accent">
                    Jauns pakalpojums
                </div>
                <h2 class="mt-6 text-3xl font-bold leading-tight text-white/95">Pārvērt vajadzību skaidrā apmaiņā.</h2>
                <p class="mt-4 text-gray-300">Labs pieprasījums palīdz citiem ātri saprast, ko vajag paveikt, cik daudz laika tas prasa un kāds rezultāts būs labs.</p>

                <div class="mt-8 divide-y divide-gray-800 border-y border-gray-800">
                    <div class="py-4">
                        <div class="text-sm font-semibold text-white/90">Apraksti rezultātu</div>
                        <p class="mt-1 text-sm text-gray-400">Ko tieši cilvēkam vajadzētu izdarīt?</p>
                    </div>
                    <div class="py-4">
                        <div class="text-sm font-semibold text-white/90">Izvēlies kategoriju</div>
                        <p class="mt-1 text-sm text-gray-400">Tā palīdzēs pareizajiem cilvēkiem tevi atrast.</p>
                    </div>
                    <div class="py-4">
                        <div class="text-sm font-semibold text-white/90">Piedāvā kredītus</div>
                        <p class="mt-1 text-sm text-gray-400">Tavai bilancei pašlaik ir {{ auth()->user()->time_credits }} kredīti.</p>
                    </div>
                </div>
            </aside>

            <div class="p-8">
            <form method="POST" action="/jobs" enctype="multipart/form-data">
                @csrf

                <div class="pb-12 border-b border-gray-700">
                    <h2 class="text-xl font-semibold leading-7 text-white/90">Lūdziet sabiedrībai palīdzību</h2>
                    <p class="mt-1 text-sm leading-6 text-gray-300">Aprakstiet, ar ko jums nepieciešama palīdzība, un piedāvājiet laika kredītus apmaiņā</p>

                    @error('error')
                        <div class="mt-2 text-red-500 text-sm">{{ $message }}</div>
                    @enderror

                    <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <x-form-field>
                            <x-form-label for="title">Ar ko jums nepieciešama palīdzība?</x-form-label>
                            <div class="mt-2">
                                <x-form-input name="title" id="title" maxlength="120" placeholder="piem., Nepieciešama palīdzība ar mājaslapas dizainu, Meklēju matemātikas skolotāju" value="{{ old('title') }}" />
                                <x-form-error name="title" />
                            </div>
                        </x-form-field>

                        <x-form-field>
                            <x-form-label for="description">Apraksts</x-form-label>
                            <div class="mt-2">
                                <textarea name="description" id="description" rows="4" maxlength="1000" required
                                        class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500"
                                        placeholder="Detalizēti aprakstiet savu pakalpojumu">{{ old('description') }}</textarea>
                                <x-form-error name="description" />
                            </div>
                        </x-form-field>

                        <x-form-field>
                            <x-form-label for="time_credits">Piedāvātie laika kredīti</x-form-label>
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm text-gray-400">Jūsu pašreizējais bilance: {{ auth()->user()->time_credits }} kredīti</p>
                            </div>
                            <div class="mt-2">
                                <x-form-input type="number" name="time_credits" id="time_credits" min="1" max="{{ auth()->user()->time_credits }}" value="{{ old('time_credits') }}" placeholder="piem., 2" />
                                <p class="mt-1 text-sm text-gray-400">Šie kredīti tiks atskaitīti no jūsu konta un piešķirti personai, kas jums palīdzēs.</p>
                                <x-form-error name="time_credits" />
                            </div>
                        </x-form-field>

                        <x-form-field>
                            <x-form-label for="category">Kategorija</x-form-label>
                            <div class="mt-2">
                                <select name="category" id="category" required
                                        class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent">
                                    <option value="">Izvēlieties kategoriju</option>
                                    @foreach(($categories ?? []) as $topKey => $group)
                                        @if(!empty($group['children']) && is_array($group['children']))
                                            <optgroup label="{{ $group['label'] }}">
                                                @foreach($group['children'] as $slug => $label)
                                                    <option value="{{ $slug }}" {{ old('category') == $slug ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </optgroup>
                                            <option value="{{ $topKey }}" {{ old('category') == $topKey ? 'selected' : '' }}>Visi {{ $group['label'] }}</option>
                                        @else
                                            <option value="{{ $topKey }}" {{ old('category') == $topKey ? 'selected' : '' }}>{{ $group['label'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <x-form-error name="category" />
                            </div>
                        </x-form-field>

                        <x-form-field>
                            <x-form-label for="image">Attēls</x-form-label>
                            <div class="mt-2">
                                <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp"
                                    class="block w-full rounded-md border border-gray-700 bg-gray-900/60 px-3 py-2 text-sm text-gray-300 file:mr-4 file:rounded-md file:border-0 file:bg-neon-accent file:px-3 file:py-2 file:text-sm file:font-medium file:text-black hover:file:bg-neon-accent/80">
                                <p class="mt-1 text-sm text-gray-400">Neobligāti. Ja nepievienosiet attēlu, tiks izmantots kategorijai piemērots noklusējuma attēls.</p>
                                <x-form-error name="image" />
                            </div>
                        </x-form-field>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-x-6">
                    <a href="/jobs" class="text-gray-300 hover:text-neon-accent transition-colors duration-200">Atcelt</a>
                    <x-form-button>
                        Publicēt palīdzības pieprasījumu
                    </x-form-button>
                </div>
            </form>
            </div>
        </div>
    </div>
</x-layout>
