<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

$batch = safe_base((string)($_GET['batch'] ?? ''));
$file  = safe_base((string)($_GET['file'] ?? ''));

$path = __DIR__ . "/notices/$batch/$file";
if (!file_exists($path)) die('Notice not found.');

$html = file_get_contents($path);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<title>Print One Notice</title>
<link rel="stylesheet" href="assets/style.css">
<style>
.page { width:210mm; min-height:297mm; padding:20mm 15mm; margin:10mm auto; background:#fff; box-sizing:border-box; }
.no-print{max-width:1100px; margin:10px auto; background:#fff; border:1px solid #ddd; border-radius:10px; padding:10px;}
@media print { .no-print{display:none !important;} .page{margin:0;} }
</style>
</head>
<body>

<div class="no-print">
  <a class="btn" href="notice_history.php?tab=notices&batch=<?php echo h($batch); ?>">← Back</a>
  <button class="btn primary" onclick="window.print()">Print / Save as PDF</button>
</div>

<div class="page"><?php echo $html; ?></div>
</body>
</html>
