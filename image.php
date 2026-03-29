<?php
require 'config.php';

// --- MAGIE 1: SESSION-SPERRE AUFHEBEN ---
// Das erlaubt PHP, hunderte Bilder absolut gleichzeitig zu laden!
session_write_close();

// Schutz-Logik
if (!defined('NC_USER')) {
    http_response_code(401); // Unauthorized
    exit;
}

if (!isset($_GET['path'])) {
    http_response_code(400);
    exit;
}

$path = $_GET['path'];
$isThumb = isset($_GET['thumb']) && $_GET['thumb'] == '1';

$safePath = implode('/', array_map('rawurlencode', explode('/', ltrim($path, '/'))));
$safePath = '/' . ltrim($safePath, '/');

if ($isThumb) {
    $decodedPath = urldecode($path);
    $prefix = '/remote.php/dav/files/' . NC_USER . '/';
    $relativePath = str_replace($prefix, '', $decodedPath);
    $safeRelativePath = implode('/', array_map('rawurlencode', explode('/', ltrim($relativePath, '/'))));
    
    // Thumbnail API
    $url = rtrim(NC_SERVER, '/') . '/index.php/core/preview.png?file=/' . $safeRelativePath . '&x=250&y=250&a=true&mode=cover';
} else {
    // Original WebDAV API
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

// --- MAGIE 2: 0-BYTE FALLBACK ---
// Wenn Nextcloud einen Fehler wirft ODER das Bild leer ist (0 Bytes), lade das echte Bild!
if ($isThumb && ($response['code'] !== 200 || empty(trim($response['data'])))) {
    $fallbackUrl = rtrim(NC_SERVER, '/') . $safePath;
    $response = fetchFromNextcloud($fallbackUrl);
}

// Ausgabe an den Browser
if ($response['code'] === 200 || $response['code'] === 201 || $response['code'] === 207) {
    $contentType = $response['type'] ?: 'image/jpeg';
    
    header("Content-Type: $contentType");
    header("Cache-Control: public, max-age=86400");
    
    // Löscht alle eventuell aus Versehen generierten Leerzeichen/Fehler aus dem Buffer
    ob_clean();
    
    echo $response['data'];
} else {
    http_response_code($response['code']);
}
?>