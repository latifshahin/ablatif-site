<?php
header('Content-Type: text/html; charset=UTF-8');

$BASE = __DIR__ . '/notices';

$batch = $_GET['batch'] ?? null;
$search = trim($_GET['q'] ?? '');

function count_notices($dir){
    return count(glob($dir.'/*.html'));
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<title>Notice History</title>
<link rel="stylesheet" href="assets/style.css">
<style>
.list-item{padding:10px;border-bottom:1px solid #ddd}
.small{font-size:0.9em;color:#555}
</style>
</head>
<body>
<div class="container">

<h1>Notice History</h1>

<?php if(!$batch): ?>

<!-- ================= BATCH LIST ================= -->
<h2>📁 Batch List</h2>

<?php
$batches = glob($BASE.'/*', GLOB_ONLYDIR);
usort($batches, fn($a,$b)=>filemtime($b)-filemtime($a));

if(!$batches){
    echo "<p class='error'>❌ কোনো batch পাওয়া যায়নি।</p>";
}

foreach($batches as $b){
    $bn = basename($b);
    ?>
    <div class="card list-item">
        <strong><?php echo $bn; ?></strong>
        <span class="small">
            (<?php echo count_notices($b); ?> notices)
        </span>
        <div class="actions">
            <a class="btn" href="notice_history.php?batch=<?php echo urlencode($bn); ?>">
                ▶ View notices
            </a>
            <a class="btn danger"
               href="delete_batch.php?batch=<?php echo urlencode($bn); ?>"
               onclick="return confirm('এই পুরো batch ডিলিট করবেন?');">
               🗑 Delete batch
            </a>
        </div>
    </div>
    <?php
}
?>

<?php else: ?>

<!-- ================= SINGLE NOTICE LIST ================= -->
<a href="notice_history.php" class="btn">&larr; Back to batch list</a>

<h2>📄 Notices in batch: <?php echo htmlspecialchars($batch); ?></h2>

<form class="no-print">
    <input type="text" name="q"
           value="<?php echo htmlspecialchars($search); ?>"
           placeholder="Search name / account">
    <input type="hidden" name="batch" value="<?php echo htmlspecialchars($batch); ?>">
    <button class="btn">Search</button>
</form>

<?php
$dir = $BASE.'/'.basename($batch);
if(!is_dir($dir)){
    echo "<p class='error'>Invalid batch.</p>";
} else {
    $files = glob($dir.'/*.html');
    if(!$files){
        echo "<p class='error'>No notices found.</p>";
    }

    foreach($files as $f){
        $html = file_get_contents($f);
        if($search && stripos($html,$search)===false) continue;

        // simple extract name/account from filename
        $label = basename($f,'.html');
        ?>
        <div class="card list-item">
            <strong><?php echo htmlspecialchars($label); ?></strong>
            <div class="actions">
                <a class="btn"
                   href="single_print.php?batch=<?php echo urlencode($batch); ?>&file=<?php echo urlencode(basename($f)); ?>">
                   🖨 Print
                </a>
                <a class="btn danger"
                   href="delete_notice.php?batch=<?php echo urlencode($batch); ?>&file=<?php echo urlencode(basename($f)); ?>"
                   onclick="return confirm('এই নোটিশ ডিলিট করবেন?');">
                   🗑 Delete
                </a>
            </div>
        </div>
        <?php
    }
}
?>

<?php endif; ?>

</div>
</body>
</html>
