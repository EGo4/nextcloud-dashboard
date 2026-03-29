<header class="flex justify-between items-center bg-brand-dark rounded-2xl px-4 sm:px-6 py-4 text-white mb-6 shadow-sm">
    <div class="flex items-center gap-4 w-full sm:w-1/3">
        <button onclick="toggleSidebar()" class="md:hidden text-gray-300 hover:text-white focus:outline-none p-2 -ml-2">
            <i class="fas fa-bars text-xl"></i>
        </button>
        
        <div class="hidden sm:flex items-center bg-white/10 rounded-full px-4 py-2 w-full">
            <i class="fas fa-search text-gray-300 mr-2"></i>
            <input type="text" placeholder="Search" class="bg-transparent border-none outline-none text-sm w-full text-white placeholder-gray-300">
        </div>
    </div>
    
    <div class="flex items-center gap-4">
        <button class="relative text-gray-300 hover:text-white">
            <i class="far fa-bell text-xl"></i>
            <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>
        <img src="https://i.pravatar.cc/150?img=11" alt="User" class="w-10 h-10 rounded-full border-2 border-white/20">
    </div>
</header>