<?php

session_start();
require_once '../logic/auth.php';
require_once '../logic/forms.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$form_id = $_GET['id'] ?? null;
if (!$form_id) {
    header('Location: my_forms.php');
    exit;
}

$form = get_form($form_id);
if (!$form || $form['user_id'] != $_SESSION['user_id']) {
    header('Location: my_forms.php');
    exit;
}

$fields = get_form_fields($form_id);
if (empty($fields)) {
    header('Location: view_responses.php?id=' . $form_id);
    exit;
}

$submissions = get_form_submissions($form_id);
$zip_filename = 'form_responses_' . preg_replace('/[^a-z0-9]+/i', '_', strtolower($form['name'])) . '_' . date('Y-m-d') . '.zip';
$zip_filepath = sys_get_temp_dir() . '/' . $zip_filename;
$zip = new ZipArchive();
if ($zip->open($zip_filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("An error occurred while creating the ZIP file.");
}

$csv_string = '';
$stream = fopen('php://memory', 'w+');
$headers = ['Submission Date'];
if ($form['require_auth']) {
    $headers[] = 'Email';
}
foreach ($fields as $field) {
    $headers[] = $field['name'];
}
fputcsv($stream, $headers, ',', '"', '\\');
if (!empty($submissions)) {
    foreach ($submissions as $submission) {
        $row = [date('Y-m-d H:i:s', strtotime($submission['submission_time']))];
        if ($form['require_auth']) {
            $row[] = $submission['user_email'] ?? 'Anonymous';
        }

        $values = get_submission_values($submission['id']);
        $valuesByFieldId = [];
        foreach ($values as $value) {
            $valuesByFieldId[$value['field_id']] = $value;
        }

        foreach ($fields as $field) {
            $field_value = $valuesByFieldId[$field['id']] ?? null;
            if ($field['type'] === 'file' && !empty($field_value['value'])) {
                $file_path = __DIR__ . '/../files/' . $field_value['value'];
                if (file_exists($file_path)) {
                    $zip->addFile($file_path, 'files/' . $field_value['value']);
                    $row[] = 'files/' . $field_value['value'];
                } else {
                    $row[] = 'File not found';
                }
            } else {
                    $row[] = $field_value['value'] ?? '';
            }
        }
        fputcsv($stream, $row, ',', '"', '\\');
    }
} else {
    fputcsv($stream, ['No submissions available for this form'], ',', '"', '\\');
}

rewind($stream);
$csv_string = stream_get_contents($stream);
fclose($stream);
$zip->addFromString('responses.csv', $csv_string);
$zip->close();
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
header('Content-Length: ' . filesize($zip_filepath));
header('Pragma: no-cache');
header('Expires: 0');
ob_clean();
flush();
readfile($zip_filepath);
unlink($zip_filepath);
exit;
