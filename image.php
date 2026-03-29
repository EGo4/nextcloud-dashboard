<?php
require 'config.php';

// Session sofort schließen, um parallele Downloads zu erlauben (Multi-Threading Fix)
session_write_close();

if (!defined('NC_USER')) {
    http_response_code(401);
    exit;
}

if (!isset($_GET['path'])) {
    http_response_code(400);
    exit;
}

$path = $_GET['path'];
$isThumb = isset($_GET['thumb']) && $_GET['thumb'] == '1';

// Den relativen Dateipfad extrahieren (z.B. "Privat/Bilder_extern/IMG_1.jpg")
$decodedPath = urldecode($path);
$prefix = '/remote.php/dav/files/' . NC_USER . '/';
$relativePath = ltrim(str_replace($prefix, '', $decodedPath), '/');

// =========================================================================
// 1. ÜBERHOLSPUR: LOKALER CACHE (SSD)
// =========================================================================
if (defined('LOCAL_NC_PATH') && LOCAL_NC_PATH !== '') {
    // Lokalen Pfad betriebssystemunabhängig zusammenbauen
    $localFilePath = rtrim(LOCAL_NC_PATH, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    
    // Prüfen, ob die Datei lokal auf der Festplatte liegt
    if (file_exists($localFilePath) && is_file($localFilePath)) {
        
        // MIME-Type sicher ermitteln
        $contentType = 'image/jpeg'; // Standard-Fallback
        if (function_exists('mime_content_type')) {
            $contentType = mime_content_type($localFilePath) ?: $contentType;
        } else {
            $ext = strtolower(pathinfo($localFilePath, PATHINFO_EXTENSION));
            if ($ext === 'png') $contentType = 'image/png';
            elseif ($ext === 'webp') $contentType = 'image/webp';
            elseif ($ext === 'gif') $contentType = 'image/gif';
        }
        
        header("Content-Type: $contentType");
        header("Cache-Control: public, max-age=86400");
        header("X-Source: Local-Cache"); // Kleiner Header für uns zum Debuggen
        
        ob_clean();
        readfile($localFilePath); // Datei direkt von SSD in den Browser streamen!
        exit;
    }
}

// =========================================================================
// 2. FALLBACK: WEBDAV SERVER (Netzwerk)
// =========================================================================
$safePath = implode('/', array_map('rawurlencode', explode('/', ltrim($path, '/'))));
$safePath = '/' . ltrim($safePath, '/');

if ($isThumb) {
    $safeRelativePath = implode('/', array_map('rawurlencode', explode('/', ltrim($relativePath, '/'))));
    $url = rtrim(NC_SERVER, '/') . '/index.php/core/preview.png?file=/' . $safeRelativePath . '&x=250&y=250&a=true&mode=cover';
} else {
    $url = rtrim(NC_SERVER, '/') . $safePath;
}

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

$response = fetchFromNextcloud($url);

// Wenn Nextcloud-Thumbnail-API zickt oder 0 Bytes liefert -> Vollbild laden
if ($isThumb && ($response['code'] !== 200 || empty(trim($response['data'])))) {
    $fallbackUrl = rtrim(NC_SERVER, '/') . $safePath;
    $response = fetchFromNextcloud($fallbackUrl);
}

if ($response['code'] === 200 || $response['code'] === 201 || $response['code'] === 207) {
    $contentType = $response['type'] ?: 'image/jpeg';
    
    header("Content-Type: $contentType");
    header("Cache-Control: public, max-age=86400");
    header("X-Source: WebDAV"); // Header zeigt an, dass vom Server geladen wurde
    
    ob_clean();
    echo $response['data'];
} else {
    http_response_code($response['code']);
}
?>