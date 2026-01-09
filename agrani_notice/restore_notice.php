<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

$batch = safe_base((string)($_GET['batch'] ?? ''));
$file  = safe_base((string)($_GET['file'] ?? ''));

$src = __DIR__ . "/trash/$batch/$file";
if (!file_exists($src)) die('Not found in trash.');

$dstDir = __DIR__ . "/notices/$batch";
@mkdir($dstDir, 0777, true);

@rename($src, $dstDir . '/' . $file);

header("Location: notice_history.php?tab=trash&batch=" . urlencode($batch));
exit;
