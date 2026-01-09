<?php
header('Content-Type: text/html; charset=UTF-8');

function parse_tab_text($text){
    $text = trim(str_replace("\r\n","\n",$text));
    $lines = array_filter(explode("\n",$text),fn($l)=>trim($l)!=='');
    if(count($lines)<2) return [[],[]];

    $headers = array_map('trim',explode("\t",array_shift($lines)));
    $rows = [];

    foreach($lines as $ln){
        $cols = array_pad(explode("\t",$ln),count($headers),'');
        $rows[] = array_combine($headers,array_map('trim',$cols));
    }
    return [$headers,$rows];
}

$raw = $_POST['raw_data'] ?? '';
$templateFile = $_POST['template_file'] ?? 'template.html';
if(trim($raw)==='') die('No data');

$templatePath = __DIR__.'/'.$templateFile;
if(!file_exists($templatePath)) die('Template not found');

$templateHtml = file_get_contents($templatePath);
list($headers,$rows) = parse_tab_text($raw);
if(!$rows) die('No rows');

$batchId = date('Ymd_His');
$batchDir = __DIR__."/notices/$batchId";
mkdir($batchDir,0777,true);

file_put_contents(
    "$batchDir/_meta.json",
    json_encode(['created_at'=>date('Y-m-d H:i:s')],JSON_UNESCAPED_UNICODE)
);

foreach($rows as $row){
    $html = $templateHtml;
    foreach($row as $k=>$v){
        $html = str_replace('{'.$k.'}',
            htmlspecialchars($v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'),
            $html);
    }

    $name  = preg_replace('/[^\p{Bengali}A-Za-z0-9_-]+/u','_',$row['B_Name']??'Borrower');
    $acc   = preg_replace('/[^A-Za-z0-9_-]+/','_',$row['PrinAc']??'NA');

    file_put_contents("$batchDir/{$name}_{$acc}.html",$html);
}

header("Location: notice_history.php");
exit;
