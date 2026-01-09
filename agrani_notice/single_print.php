<?php
header('Content-Type: text/html; charset=UTF-8');

$batch = basename($_GET['batch'] ?? '');
$file  = basename($_GET['file'] ?? '');

$path = __DIR__."/notices/$batch/$file";
if(!file_exists($path)) die('Not found');
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="assets/style.css">
</head>
<body onload="window.print()">
<div class="page">
<?php echo file_get_contents($path); ?>
</div>
</body>
</html>
