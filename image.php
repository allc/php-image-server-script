<?php
// check path
if (!isset($_GET['path'])) {
    response_404();
}
$path = $_GET['path'];
if ($path === '') {
    response_404();
}

// check content type
$content_type = mime_content_type($path);
// check if the file is an image
if (strpos($content_type, 'image/') === false) {
    response_404();
}

// last modified
$filetime = filemtime($path);
$is_304 = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) === $filetime;

// get file size
$filesize = filesize($path);

// set headers
if (!$is_304) {
    header('Content-type: ' . $content_type);
    header('Content-Length: ' . $filesize);
}
header('Last-Modified: '. date(DATE_RFC2822, $filetime));
header('Cache-Control: public, max-age=86400');
if ($is_304) {
    http_response_code(304);
    exit();
}

readfile($path);

/*
 * handle 404
 */
function response_404() {
    http_response_code(404);
    // include 404 page
    echo '404 Not Found';
    die();
}