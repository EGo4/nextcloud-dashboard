<div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col h-full">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-bold text-gray-800">Quick Action</h2>
        <i class="fas fa-sliders-h text-gray-400"></i>
    </div>
    
    <form action="#" method="POST" class="flex-1 space-y-4">
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Patient Name</label>
            <input type="text" placeholder="e.g. John Doe" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-dark focus:ring-1 focus:ring-brand-dark transition text-sm">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Department</label>
            <select class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-dark focus:ring-1 focus:ring-brand-dark transition text-sm bg-white">
                <option>General Practice</option>
                <option>Cardiology</option>
                <option>Neurology</option>
            </select>
        </div>

        <div class="pt-2 flex gap-3">
            <button type="button" class="flex-1 px-4 py-2 bg-gray-100 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">Cancel</button>
            <button type="submit" class="flex-1 px-4 py-2 bg-brand-dark text-white rounded-xl text-sm font-semibold hover:bg-opacity-90 transition">Save Data</button>
        </div>
    </form>
</div>