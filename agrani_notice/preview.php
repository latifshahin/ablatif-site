<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

$raw = $_POST['raw_data'] ?? '';
if (trim((string)$raw) === '') {
    die('❌ কোনো ডাটা পাওয়া যায়নি। আগে Excel থেকে কপি করে index.php পেজে Paste করুন।');
}

list($headers, $rows) = parse_tab_text((string)$raw);
if (empty($rows)) die('❌ কোনো বৈধ রো পাওয়া যায়নি। অন্তত এক লাইন ডাটা লাগবে।');

function list_templates(): array {
    $templates = [];
    if (file_exists(__DIR__ . '/template.html')) {
        $templates['template.html'] = 'template.html (default)';
    }
    $backupDir = __DIR__ . '/template_backups';
    if (is_dir($backupDir)) {
        foreach (glob($backupDir . '/*.html') ?: [] as $f) {
            $base = basename($f);
            $templates['template_backups/' . $base] = $base;
        }
    }
    return $templates;
}

$templates = list_templates();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Preview & Template Select</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        table.preview {width:100%; border-collapse:collapse; margin-top:10px; font-size:13px;}
        table.preview th, table.preview td {border:1px solid #ccc; padding:4px 6px; text-align:left;}
        table.preview th {background:#f0f0f0;}
        .note{font-size:12px; color:#555;}
    </style>
</head>
<body>
<div class="container">
    <h1>Preview Data & Select Template</h1>

    <div class="card">
        <h2>১) ডাটা প্রিভিউ</h2>
        <div class="table-wrapper">
            <table class="preview">
                <thead>
                <tr>
                    <?php foreach ($headers as $h1): ?>
                        <th><?php echo h($h1); ?></th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <?php foreach ($headers as $h1): ?>
                            <td><?php echo h((string)($r[$h1] ?? '')); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>২) Template নির্বাচন করুন</h2>

        <?php if (empty($templates)): ?>
            <p class="error">❌ কোনো template.html বা template_backups/*.html পাওয়া যায়নি।</p>
        <?php else: ?>
            <form method="post" action="generate.php">
                <label><strong>Template File:</strong></label><br>
                <select name="template_file" required>
                    <?php foreach ($templates as $value => $label): ?>
                        <option value="<?php echo h($value); ?>"><?php echo h($label); ?></option>
                    <?php endforeach; ?>
                </select>

                <div style="margin:10px 0;">
                    <label><strong>Batch Delete Password (optional)</strong></label><br>
                    <input type="password" name="batch_delete_password"
                           placeholder="ইউজারদের জন্য (Master ছাড়াও)" style="width:320px; padding:8px;">
                    <p class="note">Master password: <strong>Abc###</strong> সবসময় কাজ করবে।</p>
                </div>

                <textarea name="raw_data" style="display:none;"><?php echo h((string)$raw); ?></textarea>

                <div class="actions">
                    <button type="submit" class="btn primary">✅ Generate Notices (HTML)</button>
                    <a href="index.php" class="btn">↩ আবার পেস্ট করতে ফিরে যান</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
