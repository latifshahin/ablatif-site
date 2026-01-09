<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/helpers.php';

$MASTER_PASSWORD = 'Lat123';

$batch = safe_base((string)($_GET['batch'] ?? ''));
$dir = __DIR__ . "/trash/$batch";
if (!is_dir($dir)) die('Trash batch not found.');

if (!isset($_SESSION['purge_ok']) || $_SESSION['purge_ok'] !== $batch) {
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pw = (string)($_POST['password'] ?? '');
        if (hash_equals($MASTER_PASSWORD, $pw)) {
            $_SESSION['purge_ok'] = $batch;
            header("Location: purge_trash.php?batch=" . urlencode($batch));
            exit;
        } else $error = 'Master password required.';
    }
    ?>
    <!doctype html><html lang="bn"><head>
      <meta charset="UTF-8"><title>Permanently Delete</title>
      <link rel="stylesheet" href="assets/style.css">
    </head><body>
    <div class="container">
      <h2>🗑 Permanently Delete (Master)</h2>
      <p>এটি করলে আর Restore করা যাবে না।</p>
      <p><strong><?php echo h($batch); ?></strong></p>
      <?php if($error) echo "<p class='error'>".h($error)."</p>"; ?>
      <form method="post" class="card">
        <label>Master Password</label><br>
        <input type="password" name="password" style="width:280px; padding:8px;">
        <div class="actions" style="margin-top:10px;">
          <button class="btn warning">Confirm Permanent Delete</button>
          <a class="btn" href="notice_history.php?tab=trash&batch=<?php echo h($batch); ?>">Cancel</a>
        </div>
      </form>
    </div>
    </body></html>
    <?php
    exit;
}

rrmdir($dir);
unset($_SESSION['purge_ok']);

header("Location: notice_history.php?tab=trash");
exit;
