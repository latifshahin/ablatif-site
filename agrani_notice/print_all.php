<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

$baseDir = __DIR__ . '/notices';
$batch = safe_base((string)($_GET['batch'] ?? ''));
$q = trim((string)($_GET['q'] ?? ''));

if ($batch !== '' && is_dir($baseDir . '/' . $batch)) {
    $batchDir = $baseDir . '/' . $batch;
} else {
    $dirs = glob($baseDir . '/*', GLOB_ONLYDIR) ?: [];
    if (!$dirs) die('❌ কোনো notice পাওয়া যায়নি। আগে Generate করুন।');
    usort($dirs, fn($a,$b) => filemtime($b) - filemtime($a));
    $batchDir = $dirs[0];
    $batch = basename($batchDir);
}

$notices = glob($batchDir . '/*.html') ?: [];

function match_search(string $html, string $q): bool {
    if ($q === '') return true;
    return mb_stripos($html, $q, 0, 'UTF-8') !== false;
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<title>Print Notices</title>
<link rel="stylesheet" href="assets/style.css">
<style>
.page {
  width: 210mm;
  min-height: 297mm;
  padding: 20mm 15mm;
  margin: 0 auto 10mm;
  background: white;
  page-break-after: always;
  box-sizing: border-box;
}
.no-print{max-width:1100px; margin:10px auto; background:#fff; border:1px solid #ddd; border-radius:10px; padding:10px;}
@media print { .no-print{display:none !important;} .page{margin:0;} }
</style>
</head>
<body>

<div class="no-print">
  <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
    <a class="btn" href="notice_history.php?tab=notices&batch=<?php echo h($batch); ?>">← Back to History</a>

    <form method="get" style="display:flex; gap:6px; margin-left:auto; align-items:center;">
      <input type="hidden" name="batch" value="<?php echo h($batch); ?>">
      <input type="text" name="q" value="<?php echo h($q);?>" placeholder="Search inside notices (optional)"
             style="padding:8px; width:280px;">
      <button class="btn">Filter</button>
      <?php if($q!==''): ?><a class="btn" href="print_all.php?batch=<?php echo h($batch);?>">Clear</a><?php endif; ?>
    </form>

    <button class="btn primary" onclick="window.print()">Print / Save as PDF</button>
  </div>
</div>

<?php
foreach ($notices as $file) {
    $content = file_get_contents($file);
    if (!match_search($content, $q)) continue;
    echo '<div class="page">';
    echo $content;
    echo '</div>';
}
?>

</body>
</html>
