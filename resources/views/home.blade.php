<x-layout>
    <x-slot:heading>Welcome to Timerr</x-slot:heading>

    <div class="space-y-8">
        <!-- Hero Section -->
        <div class="text-center space-y-4">
            <h2 class="text-4xl font-bold text-white/90">Exchange Skills with Time</h2>
            <p class="text-xl text-gray-300">Share your expertise, earn time credits, and get services you need</p>
        </div>

        <!-- How It Works -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 hover:border-neon-accent/50 transition-all duration-300">
                <div class="text-neon-accent text-2xl mb-3">1</div>
                <h3 class="text-xl font-semibold text-white/90 mb-2">Offer Services</h3>
                <p class="text-gray-300">Create listings for your skills and talents</p>
            </div>
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 hover:border-neon-accent/50 transition-all duration-300">
                <div class="text-neon-accent text-2xl mb-3">2</div>
                <h3 class="text-xl font-semibold text-white/90 mb-2">Connect</h3>
                <p class="text-gray-300">Discuss requirements with interested users</p>
            </div>
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 hover:border-neon-accent/50 transition-all duration-300">
                <div class="text-neon-accent text-2xl mb-3">3</div>
                <h3 class="text-xl font-semibold text-white/90 mb-2">Get Verified</h3>
                <p class="text-gray-300">Complete services and receive verification</p>
            </div>
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 hover:border-neon-accent/50 transition-all duration-300">
                <div class="text-neon-accent text-2xl mb-3">4</div>
                <h3 class="text-xl font-semibold text-white/90 mb-2">Exchange Time</h3>
                <p class="text-gray-300">Use earned credits for other services</p>
            </div>
        </div>

        <!-- Featured Categories -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 hover:border-neon-accent/50 transition-all duration-300">
                <h3 class="text-xl font-semibold text-white/90 mb-2">Creative Services</h3>
                <p class="text-gray-300">Design, Art, Music, Animation</p>
            </div>
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 hover:border-neon-accent/50 transition-all duration-300">
                <h3 class="text-xl font-semibold text-white/90 mb-2">Education</h3>
                <p class="text-gray-300">Tutoring, Language Learning, Skills Training</p>
            </div>
            <div class="bg-gray-800/40 backdrop-blur-sm p-6 rounded-lg border border-gray-700 hover:border-neon-accent/50 transition-all duration-300">
                <h3 class="text-xl font-semibold text-white/90 mb-2">Professional</h3>
                <p class="text-gray-300">Translation, Writing, Consulting</p>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center mt-12 space-y-4">
            <x-button href="/jobs/create" class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300">
                Offer Your Services
            </x-button>
            <p class="text-gray-300">or</p>
            <x-button href="/jobs" class="text-gray-300 hover:text-neon-accent hover:bg-gray-800/80 border border-gray-700 transition-all duration-300">
                Browse Services
            </x-button>
        </div>
    </div>
</x-layout>
