<?php
require 'config.php';

if (!isset($_GET['path'])) {
    http_response_code(400);
    exit;
}

$path = $_GET['path'];
$isThumb = isset($_GET['thumb']) && $_GET['thumb'] == '1';

// Safely encode the path, keeping slashes intact
$safePath = implode('/', array_map('rawurlencode', explode('/', $path)));

if ($isThumb) {
    $decodedPath = urldecode($path);
    $prefix = '/remote.php/dav/files/' . NC_USER . '/';
    $relativePath = str_replace($prefix, '', $decodedPath);
    $safeRelativePath = implode('/', array_map('rawurlencode', explode('/', ltrim($relativePath, '/'))));
    
    // URL for Nextcloud's internal thumbnail generator
    $url = rtrim(NC_SERVER, '/') . '/index.php/core/preview.png?file=/' . $safeRelativePath . '&x=250&y=250&a=true&mode=cover';
} else {
    // URL for the full-resolution WebDAV file
    $url = rtrim(NC_SERVER, '/') . $safePath;
}

// Helper function to execute the cURL request
function fetchFromNextcloud($targetUrl) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $targetUrl);
    curl_setopt($ch, CURLOPT_USERPWD, NC_USER . ":" . NC_PASS);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $data = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    return ['code' => $code, 'data' => $data, 'type' => $type];
}

// 1. Try to fetch the requested image (Full or Thumb)
$response = fetchFromNextcloud($url);

// 2. THE FALLBACK: If we asked for a thumbnail but Nextcloud threw an error (like a 404 on shared folders),
// instantly fall back to fetching the full image via WebDAV so it doesn't break the UI.
if ($isThumb && $response['code'] !== 200 && $response['code'] !== 201) {
    $fallbackUrl = rtrim(NC_SERVER, '/') . $safePath;
    $response = fetchFromNextcloud($fallbackUrl);
}

// 3. Output the final result to the browser
if ($response['code'] === 200 || $response['code'] === 201 || $response['code'] === 207) {
    $contentType = $response['type'] ?: 'image/png';
    header("Content-Type: $contentType");
    // Tell the browser to cache this so it doesn't re-download it every time you click a comment
    header("Cache-Control: public, max-age=86400");
    echo $response['data'];
} else {
    http_response_code($response['code']);
}
?>