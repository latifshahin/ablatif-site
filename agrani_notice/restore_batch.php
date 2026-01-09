<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

$batch = safe_base((string)($_GET['batch'] ?? ''));
$srcDir = __DIR__ . "/trash/$batch";
if (!is_dir($srcDir)) die('Trash batch not found.');

$dstDir = __DIR__ . "/notices/$batch";
if (is_dir($dstDir)) die('Destination batch already exists.');

@rename($srcDir, $dstDir);

header("Location: notice_history.php?tab=notices&batch=" . urlencode($batch));
exit;
