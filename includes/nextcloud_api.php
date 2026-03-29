<?php
function getNextcloudFolders($baseFolder) {
    // Nextcloud WebDAV URL structure
    $url = rtrim(NC_SERVER, '/') . '/remote.php/dav/files/' . NC_USER . '/' . trim($baseFolder, '/') . '/';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, NC_USER . ":" . NC_PASS);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PROPFIND");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Depth: 1 means "give me the folder and its immediate contents only"
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Depth: 1']); 

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 207 Multi-Status is the success code for WebDAV
    if ($httpcode !== 207) {
        return ['error' => "Failed to connect to Nextcloud. HTTP Code: $httpcode"];
    }

    $folders = [];
    
    // Parse the WebDAV XML response
    $xml = simplexml_load_string($response);
    if ($xml === false) return ['error' => "Failed to parse XML response."];
    
    // Register the WebDAV namespace (d:)
    $xml->registerXPathNamespace('d', 'DAV:');
    $responses = $xml->xpath('//d:response');
    
    foreach ($responses as $resp) {
        $href = (string)$resp->xpath('.//d:href')[0];
        $decodedHref = urldecode($href);
        
        // Skip the base folder itself (we only want the subfolders)
        $expectedBasePath = '/remote.php/dav/files/' . NC_USER . '/' . trim($baseFolder, '/') . '/';
        if ($decodedHref === $expectedBasePath) continue;

        // Check if the item is a directory (collection)
        $isCollection = $resp->xpath('.//d:collection');
        if ($isCollection) {
            $folders[] = [
                'name' => basename($decodedHref),
                'path' => $decodedHref
            ];
        }
    }
    
    return $folders;
}

function getNextcloudImages($folderPath) {
    // Safely URL-encode the folder names but leave the '/' slashes intact
    $encodedPath = implode('/', array_map('rawurlencode', explode('/', rtrim($folderPath, '/'))));
    $url = rtrim(NC_SERVER, '/') . $encodedPath . '/';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, NC_USER . ":" . NC_PASS);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PROPFIND");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Depth: 1']); 

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // If it fails, print the HTTP code to help us debug!
    if ($httpcode !== 207) {
        return ['error' => "Failed to fetch images. HTTP Code: " . $httpcode];
    }

    $images = [];
    $xml = simplexml_load_string($response);
    if ($xml === false) return ['error' => "Failed to parse XML."];
    
    $xml->registerXPathNamespace('d', 'DAV:');
    $responses = $xml->xpath('//d:response');
    
    foreach ($responses as $resp) {
        $href = (string)$resp->xpath('.//d:href')[0];
        $decodedHref = urldecode($href);
        
        if ($decodedHref === urldecode($folderPath) . '/') continue; // Skip parent folder

        // Check if it's an image file
        $contentType = $resp->xpath('.//d:getcontenttype');
        $type = $contentType ? (string)$contentType[0] : '';
        
        if (strpos($type, 'image/') === 0) {
            $images[] = [
                'name' => basename($decodedHref),
                'path' => $decodedHref
            ];
        }
    }
    return $images;
}
?>