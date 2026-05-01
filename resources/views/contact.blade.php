{{-- Šis skats rāda kontaktformu ziņojuma nosūtīšanai administratoram. --}}
<x-layout>
    <x-slot:heading>Sazināties ar mums</x-slot:heading>

    <div class="mx-auto max-w-5xl overflow-hidden rounded-lg border border-gray-700 bg-gray-900/45 backdrop-blur-sm">
        <div class="grid grid-cols-1 lg:grid-cols-[0.9fr_1.1fr]">
            <aside class="border-b border-gray-700 bg-gray-950/45 p-8 lg:border-b-0 lg:border-r">
                <div class="inline-flex rounded-md border border-neon-accent/30 bg-neon-accent/10 px-3 py-2 text-sm font-semibold text-neon-accent">
                    Saziņa
                </div>
                <h2 class="mt-6 text-3xl font-bold leading-tight text-white/95">Pastāsti, kas Timerr jāuzlabo.</h2>
                <p class="mt-4 text-gray-300">Jautājumi, kļūdas, idejas vai sadarbība. Viss, kas palīdz platformai kļūt lietderīgākai.</p>

                <div class="mt-8 divide-y divide-gray-800 border-y border-gray-800">
                    <div class="py-4">
                        <div class="text-sm font-semibold text-white/90">Atbalsts</div>
                        <p class="mt-1 text-sm text-gray-400">Palīdzība ar kontu, pieteikumiem vai kredītiem.</p>
                    </div>
                    <div class="py-4">
                        <div class="text-sm font-semibold text-white/90">Atsauksmes</div>
                        <p class="mt-1 text-sm text-gray-400">Pastāsti, kas strādā labi un kas traucē.</p>
                    </div>
                    <div class="py-4">
                        <div class="text-sm font-semibold text-white/90">Idejas</div>
                        <p class="mt-1 text-sm text-gray-400">Jaunas funkcijas, kategorijas vai kopienas virzieni.</p>
                    </div>
                </div>
            </aside>

            <div class="p-8">
            <form action="/contact" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300">Vārds</label>
                    <input type="text" name="name" id="name" maxlength="60" required
                           class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500 transition-all duration-200">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300">E-pasts</label>
                    <input type="email" name="email" id="email" required
                           class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500 transition-all duration-200">
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-300">Temats</label>
                    <input type="text" name="subject" id="subject" maxlength="120" required
                           class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500 transition-all duration-200">
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-300">Ziņojums</label>
                    <textarea name="message" id="message" rows="4" maxlength="1000" required
                              class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500 transition-all duration-200"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300 px-4 py-2 rounded-md">
                        Sūtīt ziņojumu
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>
</x-layout>
