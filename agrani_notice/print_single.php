<?php
$batch  = $_GET['batch'] ?? '';
$file   = $_GET['file'] ?? '';

$path = __DIR__ . "/notices/" . basename($batch) . "/" . basename($file);

if (!file_exists($path)) {
    die("❌ Notice পাওয়া যায়নি");
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<title>Single Notice Print</title>
<link rel="stylesheet" href="assets/style.css">
<style>
.page {
    width: 210mm;
    min-height: 297mm;
    padding: 20mm 15mm;
    margin: auto;
    background: white;
    page-break-after: always;
}
@media print {
    body { margin: 0; }
}
</style>
</head>
<body>

<div class="page">
    <?php echo file_get_contents($path); ?>
</div>

</body>
</html>
