<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/helpers.php';

$MASTER_PASSWORD = 'Lat123';

$batch = safe_base((string)($_GET['batch'] ?? ''));
if ($batch === '') die('Invalid batch.');

$srcDir = __DIR__ . "/notices/$batch";
if (!is_dir($srcDir)) die('Batch not found.');

$meta = read_json($srcDir . '/_meta.json');
$batchPassword = (string)($meta['delete_password'] ?? '');

if (!isset($_SESSION['del_ok_batch']) || $_SESSION['del_ok_batch'] !== $batch) {
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        [$ok, $mode] = verify_passwords($_POST['password'] ?? '', $batchPassword, $MASTER_PASSWORD);
        if ($ok) {
            $_SESSION['del_ok_batch'] = $batch;
            header("Location: delete_batch.php?batch=" . urlencode($batch));
            exit;
        } else $error = $mode;
    }
    ?>
    <!doctype html><html lang="bn"><head>
      <meta charset="UTF-8"><title>Confirm Batch Delete</title>
      <link rel="stylesheet" href="assets/style.css">
    </head><body>
    <div class="container">
      <h2>⚠ Batch Delete</h2>
      <p>এই batch-এর সব notice Trash-এ যাবে (পরে Restore করা যাবে)।</p>
      <p><strong><?php echo h($batch); ?></strong></p>
      <?php if($error) echo "<p class='error'>".h($error)."</p>"; ?>
      <form method="post" class="card">
        <label>Delete Password (Batch password বা Master password)</label><br>
        <input type="password" name="password" style="width:280px; padding:8px;">
        <div class="actions" style="margin-top:10px;">
          <button class="btn warning">Confirm Delete</button>
          <a class="btn" href="notice_history.php?tab=notices&batch=<?php echo h($batch); ?>">Cancel</a>
        </div>
      </form>
    </div>
    </body></html>
    <?php
    exit;
}

$trashDir = __DIR__ . "/trash/$batch";
@mkdir(__DIR__ . "/trash", 0777, true);
if (is_dir($trashDir)) rrmdir($trashDir);
@rename($srcDir, $trashDir);

unset($_SESSION['del_ok_batch']);
header("Location: notice_history.php?tab=trash&batch=" . urlencode($batch));
exit;
