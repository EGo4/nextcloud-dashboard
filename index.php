<?php 
require 'config.php'; 

if (!defined('NC_USER')) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nextcloud File Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    
    <link rel="stylesheet" href="https://unpkg.com/tributejs@5.1.3/dist/tribute.css">
    <script src="https://unpkg.com/tributejs@5.1.3/dist/tribute.min.js"></script>
</head>
<body class="bg-[#f8f9fc]">

    <div class="flex h-screen w-full overflow-hidden">
        
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col p-4 sm:p-8 overflow-y-auto w-full" hx-boost="true" hx-target="main" hx-select="main" hx-swap="outerHTML">
            
            <?php include 'includes/topbar.php'; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="lg:col-span-1 h-[450px]">
                    <?php include 'modules/projects_tile.php'; ?>
                </div>
                <div class="lg:col-span-2 h-[450px]">
                    <?php include 'modules/image_list.php'; ?>
                </div>
            </div>

            <?php if (isset($_GET['image'])): ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <div class="lg:col-span-2 min-h-[500px]">
                        <?php include 'modules/image_detail.php'; ?>
                    </div>
                    
                    <div class="lg:col-span-1 min-h-[500px]">
                        <div hx-get="modules/comments.php?folder=<?= urlencode($_GET['folder'] ?? '') ?>&image=<?= urlencode($_GET['image'] ?? '') ?>" 
                            hx-trigger="load" 
                            hx-target="this" 
                            hx-select="#nc-comments-box" 
                            hx-swap="outerHTML">
                            
                            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 h-full flex flex-col items-center justify-center text-gray-400">
                                <i class="fas fa-circle-notch fa-spin text-4xl mb-4 text-[#2a366b]"></i>
                                <p class="text-sm font-semibold">Lade Kommentare...</p>
                            </div>
                        </div>
                    </div>
                    
                </div>
            <?php endif; ?>

        </main>
    </div>
</body>
</html>