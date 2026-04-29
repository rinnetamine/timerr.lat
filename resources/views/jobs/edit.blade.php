<x-layout>
    <x-slot:heading>
        Rediģēt darbu: {{ $job->title }}
    </x-slot:heading>

    <div class="mx-auto max-w-6xl overflow-hidden rounded-lg border border-gray-700 bg-gray-900/45 backdrop-blur-sm">
        <div class="grid grid-cols-1 lg:grid-cols-[0.85fr_1.15fr]">
            <aside class="border-b border-gray-700 bg-gray-950/45 p-8 lg:border-b-0 lg:border-r">
                <div class="inline-flex rounded-md border border-neon-accent/30 bg-neon-accent/10 px-3 py-2 text-sm font-semibold text-neon-accent">
                    Rediģēšana
                </div>
                <h2 class="mt-6 text-3xl font-bold leading-tight text-white/95">Noslīpē pieprasījumu, lai īstais cilvēks to saprot ātri.</h2>
                <p class="mt-4 text-gray-300">Maini aprakstu, kredītus, kategoriju vai attēlu, nezaudējot esošā pieprasījuma kontekstu.</p>

                <div class="mt-8 overflow-hidden rounded-lg border border-gray-700 bg-gray-900/55">
                    <x-job-image :job="$job" class="rounded-none border-0 border-b border-gray-700" />
                    <div class="space-y-4 p-5">
                        <div>
                            <div class="text-xs uppercase tracking-[0.16em] text-gray-500">Pašreizējais nosaukums</div>
                            <div class="mt-1 font-semibold text-white/90">{{ Str::limit($job->title, 80) }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 border-t border-gray-800 pt-4">
                            <div>
                                <div class="text-2xl font-bold text-neon-accent">{{ $job->time_credits }}</div>
                                <div class="text-xs text-gray-400">kredīti tagad</div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-white/90">{{ $job->category }}</div>
                                <div class="mt-1 text-xs text-gray-400">kategorija</div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="p-8">
            <form method="POST" action="/jobs/{{ $job->id }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="space-y-12">
                    <div class="border-b border-gray-700 pb-12">
                        <h2 class="text-xl font-semibold leading-7 text-white/90">Rediģēt savu palīdzības pieprasījumu</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-300">Atjauniniet sava palīdzības pieprasījuma detaļas</p>

                        @error('error')
                            <div class="mt-2 text-red-500 text-sm">{{ $message }}</div>
                        @enderror

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <x-form-field>
                                <x-form-label for="title">Ar ko jums nepieciešama palīdzība?</x-form-label>
                                <div class="mt-2">
                                    <x-form-input name="title" id="title" maxlength="120" placeholder="piem., Nepieciešama palīdzība ar mājaslapas dizainu, Meklēju matemātikas skolotāju" value="{{ $job->title }}" />
                                    <x-form-error name="title" />
                                </div>
                            </x-form-field>

                            <x-form-field>
                                <x-form-label for="description">Apraksts</x-form-label>
                                <div class="mt-2">
                                    <textarea name="description" id="description" rows="4" maxlength="1000" required
                                            class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500"
                                            placeholder="Detalizēti aprakstiet savu pakalpojumu">{{ $job->description }}</textarea>
                                    <x-form-error name="description" />
                                </div>
                            </x-form-field>

                            <x-form-field>
                                <x-form-label for="time_credits">Piedāvātie laika kredīti</x-form-label>
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-sm text-gray-400">Jūsu pašreizējais bilance: {{ auth()->user()->time_credits }} kredīti</p>
                                    <p class="text-sm text-gray-400">Sākotnējie kredīti: {{ $job->time_credits }}</p>
                                </div>
                                <div class="mt-2">
                                    <x-form-input type="number" name="time_credits" id="time_credits" min="1" value="{{ $job->time_credits }}" placeholder="piem., 2" />
                                    <p class="mt-1 text-sm text-gray-400">Kad atjaunināt kredītus, sākotnējais summas tiks atgriezta jūsu bilancē, un jaunais summas tiks atskaitīta.</p>
                                    <x-form-error name="time_credits" />
                                </div>
                            </x-form-field>

                            <x-form-field>
                                <x-form-label for="category">Kategorija</x-form-label>
                                <div class="mt-2">
                                    <select name="category" id="category" required
                                            class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent">
                                        <option value="" disabled {{ $job->category ? '' : 'selected' }}>Izvēlieties kategoriju</option>
                                        @foreach(($categories ?? []) as $topKey => $group)
                                            @if(!empty($group['children']) && is_array($group['children']))
                                                <optgroup label="{{ $group['label'] }}">
                                                    @foreach($group['children'] as $slug => $label)
                                                        <option value="{{ $slug }}" {{ $job->category == $slug ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </optgroup>
                                                <option value="{{ $topKey }}" {{ $job->category == $topKey ? 'selected' : '' }}>Visi {{ $group['label'] }}</option>
                                            @else
                                                <option value="{{ $topKey }}" {{ $job->category == $topKey ? 'selected' : '' }}>{{ $group['label'] }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <x-form-error name="category" />
                                </div>
                            </x-form-field>

                            <x-form-field>
                                <x-form-label for="image">Attēls</x-form-label>
                                <div class="mt-2 space-y-3">
                                    <x-job-image :job="$job" />
                                    <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp"
                                        class="block w-full rounded-md border border-gray-700 bg-gray-900/60 px-3 py-2 text-sm text-gray-300 file:mr-4 file:rounded-md file:border-0 file:bg-neon-accent file:px-3 file:py-2 file:text-sm file:font-medium file:text-black hover:file:bg-neon-accent/80">
                                    <p class="text-sm text-gray-400">Neobligāti. Augšupielādējot jaunu attēlu, tas aizstās pašreizējo.</p>
                                    <x-form-error name="image" />
                                </div>
                            </x-form-field>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between gap-x-6">
                    <div class="flex items-center">
                        <button form="delete-form" type="submit" class="text-red-400 hover:text-red-300 text-sm font-semibold transition-colors duration-200">Dzēst</button>
                    </div>

                    <div class="flex items-center gap-x-6">
                        <a href="/jobs/{{ $job->id }}" class="text-gray-300 hover:text-neon-accent transition-colors duration-200">Atcelt</a>
                        <x-form-button>
                            Atjaunināt
                        </x-form-button>
                    </div>
                </div>
            </form>

            <form method="POST" action="/jobs/{{ $job->id }}" id="delete-form" class="hidden">
                @csrf
                @method('DELETE')
            </form>
            </div>
        </div>
    </div>
</x-layout>
