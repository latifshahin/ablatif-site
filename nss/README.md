# সঞ্চয়পত্র ফর্ম → PDF (Demo)

## কী আছে
- Plain HTML + JS form
- PDF-LIB দিয়ে `content.pdf` টেমপ্লেটে ডেটা overlay করে ফাইনাল PDF বানানো
- Coordinate Finder tool (`coord-tool.html`) দিয়ে x/y বের করা

## কীভাবে রান করবেন (Offline)
ব্রাউজার `file://` থেকে PDF fetch ব্লক করতে পারে, তাই লোকাল সার্ভার রান করুন।

### Python থাকলে:
```bash
python -m http.server 8000
```

তারপর খুলুন:
http://localhost:8000

## Mapping কী?
`app.js` এ `map` object আছে—এখানে প্রতিটি ফিল্ডের `{x,y,size}` বসাতে হবে।
Coordinate Tool ওপেন করে PDF-এ ক্লিক করলে PDF-lib compatible coordinate দেখাবে।

## Notes
- এই ডেমোতে কিছু demo coordinate দেয়া আছে—আপনাকে real coordinate বসাতে হবে।
- Email automation ব্রাউজার offline এ attachment সহ সম্ভব না; Download + user attach recommended.
