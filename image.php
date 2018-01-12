<?php
define('ALLOWED_FILE_DIRECTORIES', array('images/'));
define('ALLOWED_FILE_EXTENSIONS', array('jpeg', 'jpg', 'png'));

/*
 * Check path
 * Hopefully this is relatively secure
 */

// check if path is given
if (!isset($_GET['path'])) {
    response_404();
}

// get path
$path = $_GET['path'];
$real_path = realpath($path);

// check if the path is valid
if (!is_file($real_path)) {
    response_404();
}

//check if the file extension is allowed
$extension = strtolower(pathinfo($real_path, PATHINFO_EXTENSION));
if (is_null($extension) || !in_array($extension, ALLOWED_FILE_EXTENSIONS)) {
    response_404();
}

// check if the path is allowed
$is_allowed_file_path = false;
foreach (ALLOWED_FILE_DIRECTORIES as $allowed_file_path) {
    $allowed_file_path .= DIRECTORY_SEPARATOR;
    $allowed_file_path_length = strlen($allowed_file_path);
    if ($allowed_file_path === substr($real_path, 0, $allowed_file_path_length)) {
        $is_allowed_file_path = true;
    }
}
if (!$is_allowed_file_path) {
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
function response_404($message_404 = '404 Not Found') {
    http_response_code(404);
    // include 404 page
    echo $message_404;
    die();
}
