<?php
$batch = $_GET['batch'] ?? '';
$dir = __DIR__ . "/notices/" . basename($batch);

if (!is_dir($dir)) {
    die('Invalid batch.');
}

$zipName = "notices_$batch.zip";
$zipPath = sys_get_temp_dir() . '/' . $zipName;

$zip = new ZipArchive();
$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

foreach (glob($dir . '/*.html') as $file) {
    $zip->addFile($file, basename($file));
}
$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($zipPath));
readfile($zipPath);
unlink($zipPath);
exit;
