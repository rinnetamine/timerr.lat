<x-layout>
    <x-slot:heading>Contact Us</x-slot:heading>

    <div class="max-w-2xl mx-auto">
        @if(session('success'))
            <div class="bg-green-900/40 backdrop-blur-sm p-4 rounded-lg mb-6 border border-green-800 text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-gray-800/40 backdrop-blur-sm p-8 rounded-lg border border-gray-700">
            <form action="/contact" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300">Name</label>
                    <input type="text" name="name" id="name" required
                           class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500 transition-all duration-200">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                    <input type="email" name="email" id="email" required
                           class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500 transition-all duration-200">
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-300">Subject</label>
                    <input type="text" name="subject" id="subject" required
                           class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500 transition-all duration-200">
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-300">Message</label>
                    <textarea name="message" id="message" rows="4" required
                              class="mt-1 block w-full rounded-md bg-gray-900/60 border border-gray-700 text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neon-accent/50 focus:border-neon-accent placeholder-gray-500 transition-all duration-200"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300 px-4 py-2 rounded-md">
                        Send Message
                    </button>
                </div>
            </form>
        </div>

        <!-- Contact Information
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                <h3 class="text-lg font-medium text-white/90 mb-2">Email Us</h3>
                <p class="text-gray-300">support@timerr.lat</p>
            </div>
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700">
                <h3 class="text-lg font-medium text-white/90 mb-2">Follow Us</h3>
                <div class="flex space-x-4 text-gray-300">
                    <a href="#" class="hover:text-neon-accent">Twitter</a>
                    <a href="#" class="hover:text-neon-accent">LinkedIn</a>
                    <a href="#" class="hover:text-neon-accent">GitHub</a>
                </div>
            </div>
        </div> -->
    </div>
</x-layout>
