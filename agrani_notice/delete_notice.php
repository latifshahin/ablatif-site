<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$MASTER_PASSWORD = 'Lat123';

$batch = basename($_GET['batch'] ?? '');
$file  = basename($_GET['file'] ?? '');

$path = __DIR__."/notices/$batch/$file";
if(!file_exists($path)) die('Notice not found');

if(!isset($_SESSION['delete_ok'])){
    if($_SERVER['REQUEST_METHOD']==='POST'){
        if($_POST['password']===$MASTER_PASSWORD){
            $_SESSION['delete_ok']=true;
            header("Location: ".$_SERVER['REQUEST_URI']);
            exit;
        } else {
            $error = 'Wrong password';
        }
    }
    ?>
    <form method="post" style="padding:20px">
        <h3>⚠ Confirm Delete</h3>
        <?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <input type="password" name="password" placeholder="Master password">
        <br><br>
        <button class="btn danger">Delete</button>
        <a class="btn" href="notice_history.php">Cancel</a>
    </form>
    <?php
    exit;
}

$trashDir = __DIR__."/trash/$batch";
if(!is_dir($trashDir)) mkdir($trashDir,0777,true);
rename($path,"$trashDir/$file");

unset($_SESSION['delete_ok']);
header("Location: notice_history.php");
exit;
