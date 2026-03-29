<header class="flex justify-between items-center bg-brand-dark rounded-2xl px-4 sm:px-6 py-4 text-white mb-6 shadow-sm">
    <div class="flex items-center gap-4 w-full sm:w-1/3">
        </div>
    
    <div class="flex items-center gap-4">
        <span class="hidden sm:block text-sm font-medium text-gray-200">
            Welcome, <?= htmlspecialchars(NC_USER) ?>
        </span>
        <div class="w-10 h-10 rounded-full border-2 border-white/20 bg-blue-500 flex items-center justify-center text-white font-bold uppercase">
            <?= substr(NC_USER, 0, 1) ?>
        </div>
    </div>
</header>