<?php
// =====================================================
// Bangladesh Banking News Digest (Dynamic RSS + Google News)
// Abdul Latif Blog Theme | Newest first | Auto Update
// =====================================================

// ✅ MODE: strict (bank only) or loose (bank + economy)
$strictMode = isset($_GET["mode"]) && $_GET["mode"] === "strict";

// ✅ BANKING keywords (strict)
$bankKeywordsStrict = [
  "bank","banks","banking","bangladesh bank","bb","central bank",
  "npl","default","classified loan","loan","loans","interest","repo","policy rate",
  "deposit","remittance","forex","exchange rate","dollar","usd","currency",
  "islamic bank","shariah","merger","liquidity","crr","slr",
  "money laundering","aml","credit","sme","fintech"
];

// ✅ Banking + economy keywords (loose mode)
$bankKeywordsLoose = array_merge($bankKeywordsStrict, [
  "inflation","budget","fiscal","tax","revenue","export","import",
  "trade","reserve","foreign reserve","gdp","economic growth","bond",
  "capital market","stock","share market","investment"
]);

$keywords = $strictMode ? $bankKeywordsStrict : $bankKeywordsLoose;

// ✅ Traditional RSS Sources
$rssSources = [
  ["name"=>"The Daily Star – Business","url"=>"https://www.thedailystar.net/business/rss.xml"],
  ["name"=>"Dhaka Tribune – Business","url"=>"https://www.dhakatribune.com/rss/business"],
  ["name"=>"The Business Standard – Economy","url"=>"https://www.tbsnews.net/rss-feed/economy"],
  ["name"=>"The Business Standard – Banking","url"=>"https://www.tbsnews.net/rss-feed/banking"],
  ["name"=>"Financial Express – Banking","url"=>"https://today.thefinancialexpress.com.bd/rss/banking"],
  ["name"=>"UNB – Business","url"=>"https://unb.com.bd/rss/business"],
  ["name"=>"Prothom Alo – English","url"=>"https://en.prothomalo.com/feed"],
];

// ✅ GOOGLE NEWS RSS (Bangladesh + Banking)
// NOTE: Google RSS returns many items, very useful.
// You can add more queries if you want.
$googleNewsQueries = [
  "Bangladesh banking loan",
  "Bangladesh Bank policy rate",
  "Bangladesh default loans NPL",
  "Bangladesh Islamic bank merger",
  "Bangladesh forex dollar exchange rate bank",
  "Bangladesh remittance bank",
  "Bangladesh bank scam money laundering",
  "Bangladesh financial sector reform"
];

// ✅ Convert Google queries into RSS URLs
foreach($googleNewsQueries as $q){
  $rssSources[] = [
    "name" => "Google News (BD) – " . $q,
    "url"  => "https://news.google.com/rss/search?q=" . urlencode($q . " when:365d") .
             "&hl=en-BD&gl=BD&ceid=BD:en"
  ];
}

// ✅ Config
$MAX_ITEMS = 150;        // total items to display
$CACHE_TIME = 1200;      // 20 minutes cache

$CACHE_FILE = __DIR__ . "/bd_banking_cache_" . ($strictMode ? "strict" : "loose") . ".json";

// ✅ Fetch RSS safely
function fetchRSS($url){
  $ctx = stream_context_create([
    "http"=>[
      "timeout"=>9,
      "user_agent"=>"Mozilla/5.0 (AbdulLatifBankingNewsBot)"
    ]
  ]);
  $content = @file_get_contents($url, false, $ctx);
  if(!$content) return null;
  return $content;
}

// ✅ Keyword match
function matchesKeywords($text, $keywords){
  $text = strtolower($text);
  foreach($keywords as $k){
    if(strpos($text, strtolower($k)) !== false) return true;
  }
  return false;
}

// ✅ Parse RSS/Atom
function parseRSS($xmlString, $sourceName, $keywords){
  $items = [];

  libxml_use_internal_errors(true);
  $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);
  if(!$xml) return $items;

  // RSS
  if(isset($xml->channel->item)){
    foreach($xml->channel->item as $it){
      $title = trim((string)$it->title);
      $link  = trim((string)$it->link);
      $desc  = strip_tags((string)$it->description);
      $pub   = (string)$it->pubDate;

      if(!$title || !$link) continue;

      // Filter
      if(!matchesKeywords($title." ".$desc, $keywords)) continue;

      $timestamp = strtotime($pub);
      if(!$timestamp) $timestamp = time();

      // Clean snippet (short)
      $desc = preg_replace("/\s+/", " ", $desc);
      if(strlen($desc)>180) $desc = substr($desc,0,180)."...";

      $items[] = [
        "source"=>$sourceName,
        "title"=>$title,
        "link"=>$link,
        "desc"=>$desc,
        "date"=>date("d M Y",$timestamp),
        "ts"=>$timestamp
      ];
    }
  }

  // Atom
  if(isset($xml->entry)){
    foreach($xml->entry as $it){
      $title = trim((string)$it->title);
      $link  = "";
      foreach($it->link as $ln){
        $attrs = $ln->attributes();
        if(isset($attrs["href"])){ $link = (string)$attrs["href"]; break; }
      }
      $desc = strip_tags((string)$it->summary);
      $pub  = (string)$it->updated;

      if(!$title || !$link) continue;
      if(!matchesKeywords($title." ".$desc, $keywords)) continue;

      $timestamp = strtotime($pub);
      if(!$timestamp) $timestamp = time();

      $desc = preg_replace("/\s+/", " ", $desc);
      if(strlen($desc)>180) $desc = substr($desc,0,180)."...";

      $items[] = [
        "source"=>$sourceName,
        "title"=>$title,
        "link"=>$link,
        "desc"=>$desc,
        "date"=>date("d M Y",$timestamp),
        "ts"=>$timestamp
      ];
    }
  }

  return $items;
}

// ✅ Load from cache
$allNews = [];
if(file_exists($CACHE_FILE) && (time() - filemtime($CACHE_FILE) < $CACHE_TIME)){
  $allNews = json_decode(file_get_contents($CACHE_FILE), true) ?: [];
} else {
  foreach($rssSources as $src){
    $xml = fetchRSS($src["url"]);
    if($xml){
      $parsed = parseRSS($xml, $src["name"], $keywords);
      $allNews = array_merge($allNews, $parsed);
    }
  }

  // Sort newest first
  usort($allNews, fn($a,$b)=> $b["ts"] <=> $a["ts"]);

  // Deduplicate by link
  $unique = [];
  $seen = [];
  foreach($allNews as $n){
    if(isset($seen[$n["link"]])) continue;
    $seen[$n["link"]] = true;
    $unique[] = $n;
  }

  // Limit
  $allNews = array_slice($unique, 0, $MAX_ITEMS);

  // Save cache
  file_put_contents($CACHE_FILE, json_encode($allNews));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bangladesh Banking News Digest (Auto Updated)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <meta name="description" content="Auto-updated Bangladesh banking news digest from RSS and Google News. Newest first. Filtered for banking + finance + Bangladesh Bank updates.">

  <!-- GA -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-X4YNW0E3XG"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-X4YNW0E3XG');
  </script>

  <!-- Adsense -->
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3608304969182163"
     crossorigin="anonymous"></script>

  <!-- Blog Theme -->
  <link rel="stylesheet" href="../assets/css/blog.css?v=2">

  <style>
    .news-search{width:100%;padding:12px;border-radius:12px;border:1px solid rgba(0,0,0,0.15);font-size:15px;font-weight:700;margin:12px 0;}
    body.dark .news-search{background:#0b1220;color:#fff;border:1px solid rgba(255,255,255,0.14);}
    .news-item{padding:14px;border-radius:14px;border:1px solid rgba(0,0,0,0.08);margin-top:12px;background:rgba(0,0,0,0.02);}
    body.dark .news-item{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.10);}
    .meta{font-size:13px;opacity:0.9;font-weight:700;margin-bottom:6px;}
    .meta span{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:800;background:rgba(37,99,235,0.12);color:#2563eb;margin-right:6px;margin-bottom:6px;}
    body.dark .meta span{background:rgba(59,130,246,0.16);color:#93c5fd;}
    .note-box{background:rgba(37,99,235,0.08);border-left:4px solid #2563eb;padding:12px;border-radius:12px;margin:12px 0;font-weight:700;}
    body.dark .note-box{background:rgba(59,130,246,0.08);border-left:4px solid #60a5fa;}
    .modeBtn{display:inline-block;margin:6px 10px 0 0;padding:8px 12px;border-radius:10px;border:1px solid rgba(0,0,0,0.15);font-weight:800;text-decoration:none;}
  </style>
</head>

<body>
<div class="container">

  <!-- Topbar -->
  <div class="topbar">
    <div class="brand">
      <h1 style="margin:0;font-size:18px;">Abdul Latif &ndash; Blog</h1>
      <p style="margin:0;color:var(--muted);font-size:13px;">Banking &bull; Loans &bull; Fintech</p>
    </div>
    <div>
      <a class="btn" href="../index.html">&larr; Back</a>
      <button class="btn" onclick="toggleTheme()">&#127769;</button>
    </div>
  </div>

  <div class="page">

    <!-- Main -->
    <div class="main-content">
      <div class="post">

        <h1>Bangladesh Banking News Digest (Auto Updated)</h1>
        <p>Auto-collected news from RSS + Google News (Bangladesh). Newest first.</p>

        <div class="note-box">
          ✅ Showing <b><?php echo count($allNews); ?></b> items.  
          <br>Mode:
          <a class="modeBtn" href="?mode=loose">Banking + Economy (More News)</a>
          <a class="modeBtn" href="?mode=strict">Strict Banking Only</a>
          <br><small>Cache refreshes every 20 minutes.</small>
        </div>

        <input id="searchBox" class="news-search"
          placeholder="Search (loan, NPL, repo, BB, remittance, dollar...)"
          oninput="filterNews()">

        <div id="newsWrap">
          <?php if(count($allNews)==0): ?>
            <p><b>No news loaded.</b> Some sources may be blocked temporarily. Try again later.</p>
          <?php endif; ?>

          <?php foreach($allNews as $n): ?>
          <div class="news-item">
            <div class="meta">
              <span><?php echo htmlspecialchars($n["source"]); ?></span>
              <span><?php echo htmlspecialchars($n["date"]); ?></span>
            </div>
            <h3 style="margin:0 0 8px;font-size:16px;font-weight:900;">
              <a href="<?php echo htmlspecialchars($n["link"]); ?>" target="_blank" rel="nofollow noopener">
                <?php echo htmlspecialchars($n["title"]); ?>
              </a>
            </h3>
            <p style="margin:0;opacity:0.9;font-weight:600;"><?php echo htmlspecialchars($n["desc"]); ?></p>
          </div>
          <?php endforeach; ?>
        </div>

        <p style="margin-top:18px;">
          <a href="../index.html">&larr; Back to Blog</a>
        </p>

      </div>
    </div>

    <!-- Sidebar -->
    <aside class="sidebar">

      <div class="widget">
        <h3>🔎 Search</h3>
        <form action="https://www.google.com/search" method="get" target="_blank">
          <input type="text" name="q" placeholder="Search in this blog..." required>
          <input type="hidden" name="as_sitesearch" value="ablatif.com/blog">
          <small>Search powered by Google</small>
        </form>
      </div>

      <div class="widget">
        <h3>About</h3>
        <p style="margin:0;">
          Banking insights, loan analysis, and personal finance tips by Abdul Latif.
        </p>
        <small>Fast &amp; secure static blog.</small>
      </div>
        <!-- Recent Posts (auto from index.html data-date) -->
        <div class="widget recent-widget">
          <h3>Recent Posts</h3>
          <ul id="recentPostsList" class="recent-list">
            <li class="loading">Loading…</li>
          </ul>
        </div>
        
        <style>
          /* Professional Recent Posts widget (only this widget) */
          .recent-widget .recent-list{
            list-style: none;
            margin: 10px 0 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 8px; /* spacing between items */
          }
        
          .recent-widget .recent-list li{
            margin: 0;
            padding: 0;
          }
        
          .recent-widget .recent-list li a{
            display: flex;
            gap: 10px;
            align-items: flex-start;        /* aligns icon at top */
            text-decoration: none;
            font-size: 13px;
            line-height: 1.28;              /* compact but readable */
            color: inherit;
          }
        
          /* bullet icon */
          .recent-widget .recent-list li a::before{
            content: "➜";
            flex: 0 0 16px;                 /* fixed width = perfect alignment */
            margin-top: 1px;
            color: #666;
            font-weight: 700;
          }
        
          /* hover effect */
          .recent-widget .recent-list li a:hover{
            text-decoration: underline;
          }
        
          /* loading style */
          .recent-widget .recent-list .loading{
            font-size: 13px;
            color: var(--muted);
          }
        
          /* Dark mode support */
          body.dark .recent-widget .recent-list li a::before{
            color: #aaa;
          }
        </style>
        
        <script>
        (async function(){
          try{
            const res = await fetch("../index.html?v=" + Date.now());
            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, "text/html");
            const cards = Array.from(doc.querySelectorAll(".post-card[data-date]"));
        
            const posts = cards.map(card => {
              const date = card.getAttribute("data-date");
              const a = card.querySelector("h3 a");
              const title = a ? a.textContent.trim() : "";
              const url = a ? a.getAttribute("href") : "";
              return {date, title, url};
            }).filter(p => p.title && p.url && p.date);
        
            posts.sort((a,b)=> new Date(b.date) - new Date(a.date));
            const recent = posts.slice(0,5);   // show 5
        
            const ul = document.getElementById("recentPostsList");
            ul.innerHTML = "";
        
            recent.forEach(p=>{
              const li = document.createElement("li");
              const a = document.createElement("a");
              a.href = "../" + p.url; 
              a.textContent = p.title;
              li.appendChild(a);
              ul.appendChild(li);
            });
        
          }catch(e){
            document.getElementById("recentPostsList").innerHTML =
              "<li class='loading'>Recent posts unavailable</li>";
          }
        })();
        </script>

    </aside>

  </div>

  <div class="footer">&copy; 2026 Abdul Latif</div>

</div>

<script>
  function toggleTheme(){
    document.body.classList.toggle("dark");
    localStorage.setItem("blogTheme", document.body.classList.contains("dark") ? "dark" : "light");
  }
  if(localStorage.getItem("blogTheme")==="dark"){ document.body.classList.add("dark"); }

  function filterNews(){
    const q = document.getElementById("searchBox").value.toLowerCase();
    document.querySelectorAll(".news-item").forEach(item=>{
      item.style.display = item.innerText.toLowerCase().includes(q) ? "" : "none";
    });
  }
</script>

</body>
</html>
