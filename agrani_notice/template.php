<?php
// Password-protected template editor with auto-backup
session_start();

$PASSWORD = 'Lat123';
$templateFile = __DIR__ . '/template.html';
$backupDir    = __DIR__ . '/template_backups';

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Handle login
if (!isset($_SESSION['agrani_logged_in'])) {
    if (isset($_POST['password'])) {
        if ($_POST['password'] === $PASSWORD) {
            $_SESSION['agrani_logged_in'] = true;
            header('Location: template.php');
            exit;
        } else {
            $error = 'Wrong password.';
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="bn">
    <head>
        <meta charset="UTF-8">
        <title>Template Login – Agrani Notice</title>
        <link rel="stylesheet" href="assets/style.css">
        <meta name="robots" content="noindex, nofollow">

    </head>
    <body>
    <div class="container">
        <h1>Template Editor Login</h1>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
        <?php endif; ?>
        <form method="post" class="card">
            <label>Admin Password:
                <input type="password" name="password">
            </label>
            <div class="actions">
                <button type="submit" class="btn primary">Login</button>
                <a href="index.php" class="btn">&larr; Back</a>
            </div>
        </form>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// Save template
$message = '';
if (isset($_POST['template_html'])) {
    $newHtml = $_POST['template_html'];

    if (file_exists($templateFile)) {
        $backupName = $backupDir . '/template_' . date('Ymd_His') . '.html';
        copy($templateFile, $backupName);
    }

    file_put_contents($templateFile, $newHtml);
    $message = 'Template saved successfully (and backup created).';
}

if (!file_exists($templateFile)) {
    file_put_contents($templateFile, '<p>নোটিশ টেমপ্লেট এখানে থাকবে...</p>');
}

$currentHtml = file_get_contents($templateFile);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Notice Template Editor – Agrani Notice</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .editor-wrapper {
            display: flex;
            gap: 20px;
        }
        .editor-wrapper .half {
            flex: 1;
        }
        .toolbar button {
            margin-right: 5px;
        }
        .preview-box {
            border: 1px solid #ccc;
            padding: 10px;
            background: #fafafa;
            height: 100%;
            overflow: auto;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Notice Template Editor</h1>
    <p class="hint">
        নিচের Template-এ আপনি HTML ব্যবহার করতে পারবেন।<br>
        Excel header name অনুযায়ী placeholder লিখুন যেমন:
        <code>{B_Name}</code>, <code>{Designation}</code>, <code>{NoticeNumber}</code> ইত্যাদি।
    </p>
    <?php if (!empty($message)): ?>
        <p class="success"><?php echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
    <?php endif; ?>

    <div class="card">
        <div class="toolbar">
            <strong>Quick Tags:</strong>
            <button type="button" onclick="wrapTag('<b>','</b>')"><b>B</b></button>
            <button type="button" onclick="wrapTag('<i>','</i>')"><i>I</i></button>
            <button type="button" onclick="wrapTag('<u>','</u>')"><u>U</u></button>
            <button type="button" onclick="insertText('<br>')">BR</button>
            <button type="button" onclick="insertText('<p></p>', -4)">P</button>
        </div>

        <form method="post">
            <div class="editor-wrapper">
                <div class="half">
                    <h3>Template HTML</h3>
                    <textarea id="template_html" name="template_html" rows="26" class="code-box"><?php
                        echo htmlspecialchars($currentHtml, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    ?></textarea>
                </div>
                <div class="half">
                    <h3>Live Preview (Sample)</h3>
                    <div id="preview" class="preview-box"></div>
                </div>
            </div>
            <div class="actions">
                <button type="submit" class="btn primary">Save Template</button>
                <a href="index.php" class="btn">&larr; Back</a>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Recent Backups</h2>
        <ul>
            <?php
            $backups = glob($backupDir . '/*.html');
            rsort($backups);
            $count = 0;
            foreach ($backups as $b) {
                $count++;
                if ($count > 10) break;
                $bn = basename($b);
                echo '<li>' . htmlspecialchars($bn, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
            }
            if ($count === 0) {
                echo '<li>কোনো backup এখনো তৈরি হয়নি।</li>';
            }
            ?>
        </ul>
    </div>
</div>

<script>
    const textarea = document.getElementById('template_html');
    const preview  = document.getElementById('preview');

    function updatePreview() {
        preview.innerHTML = textarea.value;
    }

    function wrapTag(startTag, endTag) {
        const el = textarea;
        const start = el.selectionStart;
        const end   = el.selectionEnd;
        const text  = el.value;
        const selected = text.substring(start, end);
        const before = text.substring(0, start);
        const after  = text.substring(end);
        el.value = before + startTag + selected + endTag + after;
        el.focus();
        el.selectionStart = start + startTag.length;
        el.selectionEnd   = end + startTag.length;
        updatePreview();
    }

    function insertText(txt, cursorOffset) {
        const el = textarea;
        const start = el.selectionStart;
        const end   = el.selectionEnd;
        const text  = el.value;
        const before = text.substring(0, start);
        const after  = text.substring(end);
        el.value = before + txt + after;
        el.focus();
        let pos = start + txt.length;
        if (cursorOffset) {
            pos += cursorOffset;
        }
        el.selectionStart = el.selectionEnd = pos;
        updatePreview();
    }

    textarea.addEventListener('input', updatePreview);
    window.addEventListener('DOMContentLoaded', updatePreview);
</script>

</body>
</html>
