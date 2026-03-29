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
</head>
<body class="bg-[#f8f9fc]">

    <div class="flex h-screen w-full overflow-hidden">
        
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col p-4 sm:p-8 overflow-y-auto w-full">
            
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
                        <?php include 'modules/comments.php'; ?>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>
</body>
</html>œ