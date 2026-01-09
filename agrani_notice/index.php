<?php
// agrani_notice v6.2 - Main menu & paste form
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-X4YNW0E3XG"></script>
  <script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-X4YNW0E3XG');
  </script>
    <title>Agrani Notice Generator v6.2</title>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3608304969182163"
     crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <h1>Agrani Notice Generator v6.2</h1>

    <div class="card">
        <h2>১) Excel থেকে কপি করে এখানে Paste করুন</h2>
        <p class="hint">
            Excel-এ যে টেবিলটি আছে সেটি সিলেক্ট করুন → <strong>Ctrl + C</strong> → নিচের বক্সে ক্লিক করে <strong>Ctrl + V</strong>।<br>
            প্রথম লাইনে অবশ্যই column name থাকবে যেমন (B_Name, MobilePhone,Designation, WorkAddress, B_Institution, Outstanding, OverDueOrDefaulted, SanctionDate, 	ExpireDate, ActualInstallment, PrinAc, LoanAmount, NoticeNumber, NoticeDate, LastRepayDate)। কোন পরিবর্তন হলে সে ডাটা লোড হবে না। আপনি চাইলে এই <a href="template.xlsx" download><b>Data Format</b></a> ডাউন লোড করে  তার মধ্যে ইউনিকোড বাংলায় লিখতে পারেন। খেয়াল রাখবেন উপরের টাইটেল কলাম কোন পরিবর্তন করা যাবে আর ইউনিকোড ব্যতিত অন্য  ফন্ট লিখলে পড়তে সমস্যা হতে পারে। </p>
        <form method="post" action="preview.php">
            <textarea name="raw_data" rows="12" class="paste-box" placeholder="মোবাইল	B_Name	Designation	WorkAddress	..."></textarea>
            <div class="actions">
                <button type="submit" class="btn primary">Preview Data</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>২) অন্যান্য অপশন</h2>
        <p>
            <a href="print_all.php" class="btn">View / Print All Notices</a>
            <a href="template.php" class="btn warning">Notice Template Editor</a>
            <a href="notice_history.php" class="btn">📂 Notice History (Single Print)
</a>


        </p>
    </div>
</div>
</body>
</html>
