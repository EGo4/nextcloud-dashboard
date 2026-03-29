<?php
require_once 'includes/nextcloud_api.php';
$projects = getNextcloudFolders(NC_BASE_FOLDER);
$currentFolder = $_GET['folder'] ?? null;
?>
<div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col h-full">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-bold text-gray-800">Projects</h2>
        <i class="fas fa-folder-tree text-gray-400"></i>
    </div>
    
    <?php if (isset($projects['error'])): ?>
        <div class="p-4 bg-red-50 text-red-600 rounded-xl text-sm font-semibold"><?= htmlspecialchars($projects['error']) ?></div>
    <?php else: ?>
        <div class="flex-1 overflow-y-auto space-y-2 pr-2">
            <?php foreach ($projects as $project): ?>
                <?php $isSelected = $currentFolder === $project['path']; ?>
                <a href="?folder=<?= urlencode($project['path']) ?>" 
                   class="flex items-center gap-3 p-3 rounded-xl transition border <?= $isSelected ? 'bg-brand-dark text-white border-brand-dark shadow-md' : 'bg-gray-50 text-gray-700 border-gray-100 hover:border-brand-dark hover:bg-white' ?>">
                    <i class="fas <?= $isSelected ? 'fa-folder-open text-white' : 'fa-folder text-[#93c5fd]' ?> text-xl"></i>
                    <span class="text-sm font-semibold truncate"><?= htmlspecialchars($project['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>