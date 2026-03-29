<?php
$currentImage = $_GET['image'] ?? null;
$currentFolder = $_GET['folder'] ?? null;
$imageComments = [];
$errorMsg = null;
$ncUsersJson = "[]"; // Default empty user list

// 🛠️ DEBUG VARIABLES
$debugFileId = null;
$debugHttpCode = null;
$debugRawResponse = null;

if ($currentImage) {
    // 1. GET THE FILE ID FROM WEBDAV
    $safePath = implode('/', array_map('rawurlencode', explode('/', $currentImage)));
    $propUrl = rtrim(NC_SERVER, '/') . $safePath;

    $xmlRequest = '<?xml version="1.0"?>
    <d:propfind xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns">
      <d:prop><oc:fileid /></d:prop>
    </d:propfind>';

    $ch1 = curl_init();
    curl_setopt($ch1, CURLOPT_URL, $propUrl);
    curl_setopt($ch1, CURLOPT_USERPWD, NC_USER . ":" . NC_PASS);
    curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "PROPFIND");
    curl_setopt($ch1, CURLOPT_POSTFIELDS, $xmlRequest);
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch1, CURLOPT_HTTPHEADER, ['Depth: 0', 'Content-Type: application/xml']); 
    
    $propResponse = curl_exec($ch1);
    curl_close($ch1);

    if ($propResponse) {
        $xml = simplexml_load_string($propResponse);
        if ($xml !== false) {
            $xml->registerXPathNamespace('oc', 'http://owncloud.org/ns');
            $ids = $xml->xpath('//oc:fileid');
            if (!empty($ids)) {
                $debugFileId = trim((string)$ids[0]); 
            }
        }
    }

    // --- NEW: FETCH NEXTCLOUD USERS FOR AUTOCOMPLETE ---
    // We use the OCS cloud/users endpoint to grab the list of available users
    $usersUrl = rtrim(NC_SERVER, '/') . '/ocs/v1.php/cloud/users?format=json';
    $chU = curl_init();
    curl_setopt($chU, CURLOPT_URL, $usersUrl);
    curl_setopt($chU, CURLOPT_USERPWD, NC_USER . ":" . NC_PASS);
    curl_setopt($chU, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chU, CURLOPT_HTTPHEADER, ['OCS-APIRequest: true']);
    $usersResponse = curl_exec($chU);
    $usersCode = curl_getinfo($chU, CURLINFO_HTTP_CODE);
    curl_close($chU);

    if ($usersCode === 200 && $usersResponse) {
        $parsedUsers = json_decode($usersResponse, true);
        if (isset($parsedUsers['ocs']['data']['users'])) {
            $userArray = [];
            foreach ($parsedUsers['ocs']['data']['users'] as $u) {
                // Tribute.js expects an array of objects with 'key' and 'value'
                $userArray[] = ['key' => $u, 'value' => $u];
            }
            $ncUsersJson = json_encode($userArray);
        }
    }

    // 2. POST A COMMENT TO NEXTCLOUD VIA WEBDAV
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_comment']) && $debugFileId) {
        $commentText = trim($_POST['new_comment']);
        
        if (!empty($commentText)) {
            $postUrl = rtrim(NC_SERVER, '/') . '/remote.php/dav/comments/files/' . urlencode($debugFileId);
            
            $payload = json_encode([
                'actorId' => NC_USER,
                'actorType' => 'users',
                'message' => $commentText,
                'objectType' => 'files',
                'verb' => 'comment'
            ]);

            $chPost = curl_init($postUrl);
            curl_setopt($chPost, CURLOPT_USERPWD, NC_USER . ":" . NC_PASS);
            curl_setopt($chPost, CURLOPT_POST, true);
            curl_setopt($chPost, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($chPost, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chPost, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_exec($chPost);
            curl_close($chPost);
        }
        echo "<script>window.location.href = '?folder=" . urlencode($currentFolder) . "&image=" . urlencode($currentImage) . "';</script>";
        exit;
    }

    // 3. GET THE COMMENTS USING THE WEBDAV COMMENTS API
    if ($debugFileId) {
        $davUrl = rtrim(NC_SERVER, '/') . '/remote.php/dav/comments/files/' . urlencode($debugFileId);
        
        $davXmlRequest = '<?xml version="1.0"?>
        <a:propfind xmlns:a="DAV:" xmlns:oc="http://owncloud.org/ns">
          <a:prop>
            <oc:message/>
            <oc:creationDateTime/>
            <oc:actorId/>
          </a:prop>
        </a:propfind>';
        
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $davUrl);
        curl_setopt($ch2, CURLOPT_USERPWD, NC_USER . ":" . NC_PASS);
        curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "PROPFIND");
        curl_setopt($ch2, CURLOPT_POSTFIELDS, $davXmlRequest);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Depth: 1', 'Content-Type: application/xml']);
        
        $davResponse = curl_exec($ch2);
        $debugHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        $debugRawResponse = $davResponse; 
        curl_close($ch2);

        if ($debugHttpCode === 207 && $davResponse) {
            $xml = simplexml_load_string($davResponse);
            if ($xml !== false) {
                $xml->registerXPathNamespace('d', 'DAV:');
                $xml->registerXPathNamespace('oc', 'http://owncloud.org/ns');
                $responses = $xml->xpath('//d:response');
                
                foreach ($responses as $resp) {
                    $href = (string)$resp->xpath('.//d:href')[0];
                    if (basename(rtrim($href, '/')) === $debugFileId) continue;
                    
                    $msg = $resp->xpath('.//oc:message');
                    $time = $resp->xpath('.//oc:creationDateTime');
                    $actor = $resp->xpath('.//oc:actorId');
                    
                    if (!empty($msg)) {
                        $imageComments[] = [
                            'message' => (string)$msg[0],
                            'creationDateTime' => (string)$time[0],
                            'actorId' => (string)$actor[0]
                        ];
                    }
                }
            } else {
                $errorMsg = "Failed to parse WebDAV XML.";
            }
        } else {
            $errorMsg = "Comments API Error.";
        }
    } else {
        $errorMsg = "Could not resolve Nextcloud File ID.";
    }
}
?>

<link rel="stylesheet" href="https://unpkg.com/tributejs@5.1.3/dist/tribute.css">

<style>
    /* Custom Styling for the Autocomplete Dropdown to match our theme */
    .tribute-container { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-radius: 0.75rem; border: 1px solid #f3f4f6; }
    .tribute-container ul { background: #ffffff; border-radius: 0.75rem; padding: 0.5rem 0; margin: 0; list-style: none; }
    .tribute-container li { padding: 0.5rem 1rem; cursor: pointer; font-size: 0.875rem; color: #374151; font-weight: 500; transition: all 0.2s; }
    .tribute-container li.highlight { background: #2a366b; color: white; }
</style>

<div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 h-full flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-bold text-gray-800">Nextcloud Comments</h2>
        <span class="bg-gray-100 text-brand-dark text-xs font-bold px-2 py-1 rounded-full"><?= count($imageComments) ?></span>
    </div>
    
    <div class="flex-1 overflow-y-auto space-y-4 pr-2 mb-4">
        <?php if (!$currentImage): ?>
            <div class="text-center text-gray-400 text-sm mt-10">
                <i class="far fa-image text-4xl mb-3 opacity-30 block"></i>
                Select an image to view comments.
            </div>
        <?php elseif ($errorMsg): ?>
            <div class="p-4 bg-red-50 text-red-600 rounded-xl text-sm font-semibold border border-red-100 break-words">
                <i class="fas fa-exclamation-triangle block text-2xl mb-2 opacity-50 text-center"></i>
                <p class="mb-2 text-center text-red-800 uppercase tracking-wide"><b><?= htmlspecialchars($errorMsg) ?></b></p>
                <hr class="border-red-200 my-2">
                <p><b>File ID:</b> '<?= htmlspecialchars($debugFileId ?? 'Null') ?>'</p>
                <p><b>HTTP Code:</b> <?= htmlspecialchars($debugHttpCode ?? 'Null') ?></p>
            </div>
        <?php elseif (empty($imageComments)): ?>
            <div class="text-center text-gray-400 text-sm mt-10">
                <i class="far fa-comment-dots text-4xl mb-3 opacity-30 block"></i>
                No comments on this file in Nextcloud.
            </div>
        <?php else: ?>
            <?php foreach ($imageComments as $comment): ?>
                <?php 
                    $timeDate = date('M j, Y - g:i A', strtotime($comment['creationDateTime'])); 
                    $isMe = ($comment['actorId'] === NC_USER);
                    
                    // Highlight @mentions in the text
                    $messageText = htmlspecialchars($comment['message']);
                    $messageText = preg_replace('/@([a-zA-Z0-9_.-]+)/', '<span class="text-brand-dark font-bold bg-blue-100 px-1 rounded">@$1</span>', $messageText);
                ?>
                <div class="p-3 rounded-xl border <?= $isMe ? 'bg-blue-50 border-blue-100 ml-6 rounded-tr-none' : 'bg-gray-50 border-gray-100 mr-6 rounded-tl-none' ?>">
                     <div class="flex justify-between items-center mb-1">
                        <span class="text-xs font-bold <?= $isMe ? 'text-blue-800' : 'text-gray-800' ?>">
                            <?= htmlspecialchars($comment['actorId']) ?>
                        </span>
                        <span class="text-[10px] text-gray-400"><?= $timeDate ?></span>
                    </div>
                    <p class="text-sm text-gray-700 leading-relaxed"><?= nl2br($messageText) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <form action="?folder=<?= urlencode($currentFolder) ?>&image=<?= urlencode($currentImage) ?>" method="POST" class="mt-auto relative">
        <input type="text" id="commentInput" name="new_comment" placeholder="<?= $currentImage ? 'Type @ to mention someone...' : 'Select an image first' ?>" <?= $currentImage ? 'required' : 'disabled' ?> autocomplete="off" class="w-full pl-4 pr-12 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-brand-dark text-sm bg-gray-50 focus:bg-white transition disabled:opacity-50">
        <button type="submit" <?= $currentImage ? '' : 'disabled' ?> class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-brand-dark text-white rounded-lg flex items-center justify-center hover:bg-opacity-90 transition shadow-sm disabled:opacity-50">
            <i class="fas fa-paper-plane text-xs relative -left-[1px]"></i>
        </button>
    </form>
</div>

<script src="https://unpkg.com/tributejs@5.1.3/dist/tribute.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Load the JSON string generated by our PHP API call
        var nextcloudUsers = <?= $ncUsersJson ?>;
        
        var inputField = document.getElementById('commentInput');

        if (inputField && nextcloudUsers.length > 0) {
            var tribute = new Tribute({
                values: nextcloudUsers,
                selectTemplate: function (item) {
                    // This defines what gets inserted into the input box
                    return '@' + item.original.value;
                },
                menuItemTemplate: function (item) {
                    // This defines what the dropdown looks like
                    return '<i class="fas fa-user-circle text-gray-400 mr-2"></i>' + item.string;
                },
                noMatchTemplate: function () {
                    return '<span style="visibility: hidden;"></span>';
                }
            });

            tribute.attach(inputField);
        }
    });
</script>