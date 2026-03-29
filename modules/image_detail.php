<?php $currentImage = $_GET['image'] ?? null; ?>
<div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 h-full flex flex-col relative overflow-hidden">
    
    <div class="flex justify-between items-center mb-4 z-10 relative bg-white pb-2">
        <h2 class="text-lg font-bold text-gray-800 truncate pr-4" title="<?= htmlspecialchars(basename($currentImage)) ?>">
            <?= htmlspecialchars(basename($currentImage)) ?>
        </h2>
        
        <div class="flex items-center gap-3 flex-shrink-0">
            <div class="flex bg-gray-100 rounded-lg p-1">
                <button onclick="zoomImage(-1)" class="w-8 h-8 rounded text-gray-500 hover:text-brand-dark hover:bg-white shadow-sm transition flex items-center justify-center focus:outline-none" title="Zoom Out">
                    <i class="fas fa-search-minus"></i>
                </button>
                <button onclick="resetZoom()" class="w-8 h-8 rounded text-gray-500 hover:text-brand-dark hover:bg-white shadow-sm transition flex items-center justify-center focus:outline-none" title="Reset View">
                    <i class="fas fa-expand"></i>
                </button>
                <button onclick="zoomImage(1)" class="w-8 h-8 rounded text-gray-500 hover:text-brand-dark hover:bg-white shadow-sm transition flex items-center justify-center focus:outline-none" title="Zoom In">
                    <i class="fas fa-search-plus"></i>
                </button>
            </div>

            <a href="image.php?path=<?= urlencode($currentImage) ?>" download class="ml-2 bg-brand-dark text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-opacity-90 transition shadow-sm flex items-center">
                <i class="fas fa-download mr-2"></i> Download
            </a>
        </div>
    </div>

    <div id="zoom-container" class="flex-1 bg-gray-50 rounded-xl overflow-hidden flex items-center justify-center border border-gray-200 relative cursor-grab active:cursor-grabbing group">
        
        <div class="absolute top-4 left-1/2 -translate-x-1/2 bg-black/60 text-white text-xs px-3 py-1.5 rounded-full opacity-0 group-hover:opacity-100 transition duration-300 pointer-events-none z-10 hidden sm:block">
            Scroll to zoom • Click & drag to pan
        </div>

        <img id="zoom-image" src="image.php?path=<?= urlencode($currentImage) ?>" class="max-w-full max-h-[600px] object-contain origin-center transition-transform duration-75 select-none" draggable="false">
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const container = document.getElementById('zoom-container');
    const img = document.getElementById('zoom-image');
    
    if (!container || !img) return;

    let scale = 1;
    let pointX = 0;
    let pointY = 0;
    let start = { x: 0, y: 0 };
    let panning = false;

    // 1. BOUNDARY MATH
    function clamp() {
        // Prevent zooming out smaller than the original image
        if (scale <= 1) {
            scale = 1;
            pointX = 0;
            pointY = 0;
            return;
        }
        
        // Prevent dragging the image completely off-screen
        const rect = container.getBoundingClientRect();
        // Calculate max allowed panning based on current scale
        const maxX = (rect.width * scale - rect.width) / 2;
        const maxY = (rect.height * scale - rect.height) / 2;
        
        pointX = Math.min(Math.max(pointX, -maxX), maxX);
        pointY = Math.min(Math.max(pointY, -maxY), maxY);
    }

    function setTransform() {
        clamp();
        img.style.transform = `translate(${pointX}px, ${pointY}px) scale(${scale})`;
    }

    // 2. MOUSE WHEEL ZOOMING (Fixed mouse targeting)
    container.addEventListener('wheel', function (e) {
        e.preventDefault();
        
        const rect = container.getBoundingClientRect();
        
        // Calculate mouse position relative to the CENTER of the container
        const mouseX = e.clientX - rect.left - (rect.width / 2);
        const mouseY = e.clientY - rect.top - (rect.height / 2);
        
        // Determine zoom direction
        const zoomModifier = (e.deltaY < 0) ? 1.15 : (1 / 1.15);
        const newScale = Math.max(1, Math.min(scale * zoomModifier, 10)); // Clamp between 1x and 10x
        
        const ratio = newScale / scale;
        
        // Adjust translation to keep the pixel under the mouse locked in place
        pointX = mouseX - (mouseX - pointX) * ratio;
        pointY = mouseY - (mouseY - pointY) * ratio;
        
        scale = newScale;
        setTransform();
    }, { passive: false });

    // 3. CLICK & DRAG PANNING (Fixed edge cases)
    container.addEventListener('mousedown', function (e) {
        e.preventDefault();
        if (scale > 1) { // Only allow dragging if zoomed in
            start = { x: e.clientX - pointX, y: e.clientY - pointY };
            panning = true;
        }
    });

    // Use window so panning doesn't abruptly stop if the mouse leaves the container
    window.addEventListener('mouseup', function () {
        panning = false;
    });

    window.addEventListener('mousemove', function (e) {
        if (!panning) return;
        e.preventDefault();
        pointX = e.clientX - start.x;
        pointY = e.clientY - start.y;
        setTransform();
    });

    // 4. BUTTON CONTROLS
    window.zoomImage = function(direction) {
        const modifier = (direction > 0) ? 1.25 : (1 / 1.25);
        scale = Math.max(1, Math.min(scale * modifier, 10));
        
        // When using buttons, zoom perfectly into the center
        if (scale === 1) {
            pointX = 0;
            pointY = 0;
        } else {
            // Keep current panning center if already zoomed
            pointX *= modifier;
            pointY *= modifier;
        }
        
        setTransform();
    }

    window.resetZoom = function() {
        scale = 1;
        pointX = 0;
        pointY = 0;
        setTransform();
    }
});
</script>