<div id="mobile-overlay" class="fixed inset-0 bg-brand-dark/40 backdrop-blur-sm z-40 hidden md:hidden transition-opacity" onclick="toggleSidebar()"></div>

<div id="sidebar-wrapper" class="fixed inset-y-0 left-0 z-50 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 w-64 md:w-20 flex-shrink-0">

    <aside class="absolute top-0 left-0 h-screen w-64 md:w-20 md:hover:w-64 bg-brand-dark text-white flex flex-col py-8 overflow-hidden transition-all duration-300 ease-in-out group shadow-[4px_0_24px_rgba(0,0,0,0.1)]">
        
        <button onclick="toggleSidebar()" class="md:hidden absolute top-6 right-6 text-gray-400 hover:text-white">
            <i class="fas fa-times text-2xl"></i>
        </button>

        <div class="flex items-center px-6 mb-10 h-8 whitespace-nowrap">
            <i class="fas fa-user-md text-blue-300 text-2xl w-8 text-center flex-shrink-0"></i>
            <span class="text-xl font-bold ml-4 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">DASH</span>
        </div>
        
        <div class="px-6 mb-4 h-4 whitespace-nowrap">
            <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">Main Menu</span>
        </div>
        
        <nav class="flex-1 space-y-3 px-3">
            <a href="#" class="flex items-center bg-white text-brand-dark px-3 py-3 rounded-xl font-medium shadow-sm transition whitespace-nowrap overflow-hidden">
                <i class="fas fa-home w-8 text-xl text-center flex-shrink-0"></i>
                <span class="ml-4 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">Dashboard</span>
            </a>
            
            <a href="#" class="flex items-center text-gray-300 hover:text-white hover:bg-white/10 px-3 py-3 rounded-xl transition whitespace-nowrap overflow-hidden">
                <i class="fas fa-wheelchair w-8 text-xl text-center flex-shrink-0"></i>
                <span class="ml-4 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">Patients</span>
            </a>

            <a href="#" class="flex items-center text-gray-300 hover:text-white hover:bg-white/10 px-3 py-3 rounded-xl transition whitespace-nowrap overflow-hidden">
                <i class="fas fa-folder-open w-8 text-xl text-center flex-shrink-0"></i>
                <span class="ml-4 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">Projects</span>
            </a>
            
            <a href="#" class="flex items-center text-gray-300 hover:text-white hover:bg-white/10 px-3 py-3 rounded-xl transition whitespace-nowrap overflow-hidden">
                <i class="fas fa-cog w-8 text-xl text-center flex-shrink-0"></i>
                <span class="ml-4 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">Settings</span>
            </a>
        </nav>

        <div class="px-3 mt-auto">
            <a href="#" class="flex items-center text-gray-300 hover:text-white hover:bg-white/10 px-3 py-3 rounded-xl transition whitespace-nowrap overflow-hidden border-t border-white/10">
                <i class="fas fa-sign-out-alt w-8 text-xl text-center flex-shrink-0"></i>
                <span class="ml-4 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">Log Out</span>
            </a>
        </div>
    </aside>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar-wrapper');
        const overlay = document.getElementById('mobile-overlay');
        
        // Toggles the slide-in animation
        sidebar.classList.toggle('-translate-x-full');
        // Toggles the dark background overlay
        overlay.classList.toggle('hidden');
    }
</script>