<?php
/**
 * A minimal PhotoBackup API endpoint developed in PHP.
 *
 * @version 1.0.0-rc.2
 * @author Martijn van der Ven <martijn@zegnat.net>
 * @copyright 2015 Martijn van der Ven
 * @license http://opensource.org/licenses/MIT The MIT License
 */

/**
 * The password required to upload to this server.
 *
 * The password is currently stored as clear text here. PHP code is not normally
 * readable by third-parties so this should be safe enough. Many applications
 * store database credentials in this way as well. A secondary and safer way is
 * being considered for the next version.
 *
 * @global string $Password A string (encapsulated by quotes).
 */
$Password = 'example';

/**
 * The directory where files should be uploaded to.
 *
 * This directory path is relative to this index.php file and should not end
 * with a /. It should point to an existing directory on the server.
 *
 * @global string $MediaRoot A string (encapsulated by quotes).
 */
$MediaRoot = 'photos';

// -----------------------------------------------------------------------------
// NO CONFIGURATION NECCESSARY BEYOND THIS POINT.
// -----------------------------------------------------------------------------

/**
 * Establish what HTTP version is being used by the server.
 */
if (isset($_SERVER['SERVER_PROTOCOL'])) {
    $protocol = $_SERVER['SERVER_PROTOCOL'];
} else {
    $protocol = 'HTTP/1.0';
}

/**
 * Find out if the client is requesting the test page.
 */
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$testing = substr($request, -5) === '/test';

/**
 * If no data has been POSTed to the server and the client did not request the
 * test page, exit imidiately.
 */
if (empty($_POST) && !$testing) {
    exit();
}

/**
 * Double check if a password has been configured. If there has not and we are
 * testing the server, exit with HTTP code 401.
 */
if (
    $testing &&
    (
        !isset($Password) ||
        !is_string($Password)
    )
) {
    if ($testing) {
        header($protocol . ' 401 Unauthorized');
        exit();
    }
}

/**
 * If the client did not submit a password, or the submitted password did not
 * match this server's password, exit with HTTP code 403.
 */
if (
    !isset($Password) ||
    !isset($_POST['password']) ||
    $_POST['password'] !== hash('sha512', $Password)
) {
    header($protocol . ' 403 Forbidden');
    exit();
}

/**
 * If the upload destination folder has not been configured, does not exist, or
 * is not writable by PHP, exit with HTTP code 500.
 */
if (
    !isset($MediaRoot) ||
    !is_string($MediaRoot) ||
    !file_exists($MediaRoot) ||
    !is_dir($MediaRoot) ||
    !is_writable($MediaRoot)
) {
    header($protocol . ' 500 Internal Server Error');
    exit();
}

/**
 * If we were only supposed to test the server, end here.
 */
if ($testing) {
    exit();
}

/**
 * If the client did not submit a filesize, exit with HTTP code 400.
 */
if (!isset($_POST['filesize'])) {
    header($protocol . ' 400 Bad Request');
    exit();
}

/**
 * If the client did not upload a file, or something went wrong in the upload
 * process, exit with HTTP code 401.
 */
if (
    !isset($_FILES['upfile']) ||
    $_FILES['upfile']['error'] !== UPLOAD_ERR_OK ||
    !is_uploaded_file($_FILES['upfile']['tmp_name'])
) {
    header($protocol . ' 401 Unauthorized');
    exit();
}

/**
 * If the client submitted filesize did not match the uploaded file's size, exit
 * with HTTP code 411.
 */
if (intval($_POST['filesize']) !== $_FILES['upfile']['size']) {
    header($protocol . ' 411 Length Required');
    exit();
}

/**
 * Sanitize the file name to maximise server operating system compatibility and
 * minimize possible attacks against this implementation.
 */
$filename = preg_replace('@\s+@', '-', $_FILES['upfile']['name']);
$filename = preg_replace('@[^0-9a-z._-]@i', '', $filename);
$target = $MediaRoot . '/' . $filename;

/**
 * If a file with the same name and size exists, treat the new upload as a
 * duplicate and exit.
 */
if (
    file_exists($target) &&
    filesize() === $_POST['filesize']
) {
    header($protocol . ' 409 Conflict');
    exit();
}

/**
 * Move the uploaded file into the target directory. If anything did not work,
 * exit with HTTP code 500.
 */
if (!move_uploaded_file($_FILES["upfile"]["tmp_name"], $target)) {
    header($protocol . ' 500 Internal Server Error');
    exit();
}

exit();
