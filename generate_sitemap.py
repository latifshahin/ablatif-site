import os
from datetime import datetime

# ========== SETTINGS ==========
SITE = "https://ablatif.com"
ROOT = "."  # script runs inside local site root folder
OUTPUT_FILE = "sitemap.xml"
today = datetime.today().strftime("%Y-%m-%d")

# folders to include in sitemap
HTML_FOLDERS = [
    "",                 # root pages like index.html
    "blog",
    "blog/posts",
    "cof",
    "emi",
    "loan_tools",
    "tools"
]

# exclude files
EXCLUDE_FILES = [
    "filetree.php",
    "index.php"
]
# ===============================


def build_url(loc, lastmod, changefreq="monthly", priority="0.6"):
    return f"""
  <url>
    <loc>{loc}</loc>
    <lastmod>{lastmod}</lastmod>
    <changefreq>{changefreq}</changefreq>
    <priority>{priority}</priority>
  </url>"""


def list_html_files(folder):
    path = os.path.join(ROOT, folder)
    if not os.path.exists(path):
        return []

    files = []
    for f in os.listdir(path):
        if f.endswith(".html") and f not in EXCLUDE_FILES:
            files.append(f)
    return sorted(files)


def main():
    xml = ['<?xml version="1.0" encoding="UTF-8"?>']
    xml.append('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">')

    total = 0

    for folder in HTML_FOLDERS:
        files = list_html_files(folder)

        for f in files:
            # URL build
            if folder == "":
                loc = f"{SITE}/{f}"
                if f == "index.html":
                    loc = f"{SITE}/"
            else:
                loc = f"{SITE}/{folder}/{f}"

            # Last modified from file timestamp
            file_path = os.path.join(ROOT, folder, f)
            lastmod = datetime.fromtimestamp(os.path.getmtime(file_path)).strftime("%Y-%m-%d")

            # priority & changefreq rules
            if loc == f"{SITE}/":
                priority = "1.0"
                changefreq = "weekly"
            elif "blog/posts" in loc:
                priority = "0.9"
                changefreq = "yearly"
            elif "blog/" in loc:
                priority = "0.8"
                changefreq = "monthly"
            else:
                priority = "0.6"
                changefreq = "monthly"

            xml.append(build_url(loc, lastmod, changefreq, priority))
            total += 1

    xml.append("</urlset>")

    with open(OUTPUT_FILE, "w", encoding="utf-8") as f:
        f.write("\n".join(xml))

    print(f"? Sitemap generated: {OUTPUT_FILE}")
    print(f"? Total pages included: {total}")
    print(f"? Upload it to: public_html/sitemap.xml")


if __name__ == "__main__":
    main()
