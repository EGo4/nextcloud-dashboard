<?php
$currentFolder = $_GET['folder'] ?? null;
$currentImage = $_GET['image'] ?? null;

if ($currentFolder) {
    $images = getNextcloudImages($currentFolder);
}
?>

<style>
    /* MULTI-COLUMN LIST VIEW OVERRIDE */
    #image-gallery.view-list {
        display: grid !important;
        /* Creates as many 200px columns as will fit on the screen */
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)) !important;
        gap: 0.75rem !important;
    }
    #image-gallery.view-list .img-card {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        height: 3.5rem !important; /* Strict fixed height for the row */
        width: 100% !important;
        background-color: #f8f9fc !important;
        border-color: #e2e8f0 !important;
    }
    #image-gallery.view-list .img-card[data-selected="true"] {
        background-color: #ffffff !important;
        border-color: #2a366b !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
    }
    #image-gallery.view-list .img-wrap {
        position: relative !important;
        width: 3.5rem !important; /* Perfect square matching the row height */
        height: 3.5rem !important;
        inset: auto !important; /* 🚀 KILLS TAILWIND'S OVERLAP BEHAVIOR */
        flex-shrink: 0 !important;
        border-radius: 0 !important;
        border-right: 1px solid #e2e8f0 !important;
    }
    #image-gallery.view-list .img-title {
        position: static !important; /* Removes it from the bottom of the image */
        background: transparent !important;
        color: #374151 !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        padding: 0 0.75rem !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        display: block !important;
    }
</style>

<div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col h-full min-h-[400px]">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-bold text-gray-800">Project Images</h2>
        
        <?php if ($currentFolder && empty($images['error']) && !empty($images)): ?>
            <div class="flex bg-gray-100 p-1 rounded-lg">
                <button onclick="setGalleryView('grid')" id="btn-grid" class="w-8 h-8 rounded text-gray-400 hover:text-brand-dark transition flex items-center justify-center focus:outline-none">
                    <i class="fas fa-th-large text-sm"></i>
                </button>
                <button onclick="setGalleryView('list')" id="btn-list" class="w-8 h-8 rounded text-gray-400 hover:text-brand-dark transition flex items-center justify-center focus:outline-none">
                    <i class="fas fa-list text-sm"></i>
                </button>
            </div>
        <?php else: ?>
            <i class="far fa-images text-gray-400"></i>
        <?php endif; ?>
    </div>

    <?php if (!$currentFolder): ?>
        <div class="flex-1 flex flex-col items-center justify-center text-gray-400">
            <i class="fas fa-hand-pointer text-4xl mb-3 opacity-50"></i>
            <p class="text-sm">Select a project folder to view images</p>
        </div>
    <?php elseif (isset($images['error'])): ?>
        <div class="p-4 bg-red-50 text-red-600 rounded-xl text-sm font-semibold"><?= htmlspecialchars($images['error']) ?></div>
    <?php elseif (empty($images)): ?>
        <div class="flex-1 flex flex-col items-center justify-center text-gray-400">
            <i class="far fa-image text-4xl mb-3 opacity-50"></i>
            <p class="text-sm">No images found in this folder</p>
        </div>
    <?php else: ?>
        <div id="image-gallery" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 overflow-y-auto pr-2 pb-4 content-start">
            <?php foreach ($images as $img): ?>
                <?php $isImgSelected = $currentImage === $img['path']; ?>
                
                <a href="?folder=<?= urlencode($currentFolder) ?>&image=<?= urlencode($img['path']) ?>" 
                   data-selected="<?= $isImgSelected ? 'true' : 'false' ?>"
                   class="img-card group relative block w-full h-32 sm:h-40 rounded-xl overflow-hidden border-2 transition <?= $isImgSelected ? 'border-brand-dark shadow-lg ring-2 ring-brand-dark ring-offset-2' : 'border-gray-100 hover:border-brand-dark hover:shadow-md' ?>">
                    
                    <div class="img-wrap absolute inset-0 w-full h-full bg-gray-100 border-r border-transparent">
                        <img src="image.php?path=<?= urlencode($img['path']) ?>&thumb=1" loading="lazy" alt="<?= htmlspecialchars($img['name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                    </div>
                    
                    <div class="img-title absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-2 text-[10px] text-white truncate font-medium z-10">
                        <?= htmlspecialchars($img['name']) ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function setGalleryView(view) {
        const gallery = document.getElementById('image-gallery');
        const btnGrid = document.getElementById('btn-grid');
        const btnList = document.getElementById('btn-list');
        
        if (!gallery) return;

        if (view === 'list') {
            gallery.classList.add('view-list');
            btnList.classList.add('bg-white', 'text-brand-dark', 'shadow-sm');
            btnGrid.classList.remove('bg-white', 'text-brand-dark', 'shadow-sm');
        } else {
            gallery.classList.remove('view-list');
            btnGrid.classList.add('bg-white', 'text-brand-dark', 'shadow-sm');
            btnList.classList.remove('bg-white', 'text-brand-dark', 'shadow-sm');
        }
        localStorage.setItem('nc_gallery_view', view);
    }

    document.addEventListener("DOMContentLoaded", function() {
        const savedView = localStorage.getItem('nc_gallery_view') || 'grid';
        setGalleryView(savedView);
    });
</script>