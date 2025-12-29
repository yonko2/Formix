<?php

session_start();
require_once '../logic/auth.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$filename = $_GET['filename'] ?? null;
if (!$filename) {
    http_response_code(400);
    echo "Error: No file specified.";
    exit;
}

$basename = basename($filename);
$file_path = __DIR__ . '/../files/' . $basename;
if ($basename !== $filename || !file_exists($file_path)) {
    http_response_code(404);
    echo "Error: File not found.";
    exit;
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $basename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
ob_clean();
flush();
readfile($file_path);
exit;
