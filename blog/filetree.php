<?php
// SIMPLE FILE TREE GENERATOR (SAFE FILTERED VERSION)
// Upload: public_html/filetree.php
// Visit: https://yourdomain.com/filetree.php

$root = __DIR__; // current directory
$ignore = ['.git', 'node_modules', 'vendor', '.env', '.htaccess', 'storage', 'cache'];

function tree($dir, $prefix = '') {
    global $ignore;
    $files = scandir($dir);
    $files = array_diff($files, ['.', '..']);
    sort($files);

    foreach ($files as $file) {
        if (in_array($file, $ignore)) continue;

        $path = $dir . DIRECTORY_SEPARATOR . $file;
        echo htmlspecialchars($prefix . "├── " . $file) . "<br>";

        if (is_dir($path)) {
            tree($path, $prefix . "│   ");
        }
    }
}

echo "<h2>File Tree of: " . htmlspecialchars($root) . "</h2>";
echo "<pre style='font-size:14px;line-height:1.4;'>";

tree($root);

echo "</pre>";
?>
