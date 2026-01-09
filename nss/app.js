console.log("app.js loaded ✅");

let lastGeneratedBlobUrl = null;

window.addEventListener("load", function () {
  const buyerSection = document.getElementById("buyerSection");
  const buyer2Col = document.getElementById("buyer2Col");

  const goNextBtn = document.getElementById("goNext");
  const goPdfBtn = document.getElementById("goPdf");
  const printBtn = document.getElementById("printPdf");

  const chequeBox = document.getElementById("chequeBox");
  const pensionBox = document.getElementById("pensionBox");

  // ✅ Dynamic nominee area + controls
  const nomineeDynamicArea = document.getElementById("nomineeDynamicArea");
  const nomAdult = document.getElementById("nomAdult");
  const nomMinor = document.getElementById("nomMinor");
  const nomineeCountEl = document.getElementById("nomineeCount");

  /* ================================
     Dynamic Nominee UI Renderer
  =================================== */

  function adultNomineeHTML(i) {
    return `
      <div class="card" style="background:#f8fafc; margin-top:12px;">
        <h2>নমিনি (${i}) - প্রাপ্তবয়স্ক</h2>

        <div class="grid-2">
          <div><label>নাম</label><input id="n${i}_name" value="প্রাপ্তবয়স্ক নমিনি ${i}"></div>
          <div><label>NID</label><input id="n${i}_nid" value="12345678901${i}"></div>
          <div><label>জন্মতারিখ</label><input id="n${i}_dob" value="01-01-1990"></div>
          <div><label>মোবাইল</label><input id="n${i}_mobile" value="0170000000${i}"></div>
          <div style="grid-column: span 2;"><label>ঠিকানা</label><input id="n${i}_address" value="ঢাকা, বাংলাদেশ"></div>
          <div><label>অংশ</label><input id="n${i}_share" value="${i === 1 ? "50%" : "50%"}"></div>
          <div><label>সম্পর্ক</label><input id="n${i}_relation" value="ভাই"></div>
        </div>
      </div>
    `;
  }

  function minorNomineeHTML(i) {
    return `
      <div class="card" style="background:#fff7ed; margin-top:12px;">
        <h2>নমিনি (${i}) - অপ্রাপ্তবয়স্ক / প্রতিষ্ঠান</h2>

        <div class="grid-2">
          <div><label>নমিনি/প্রতিষ্ঠানের নাম</label><input id="m${i}_name" value="অপ্রাপ্তবয়স্ক নমিনি ${i}"></div>
          <div><label>মোবাইল</label><input id="m${i}_mobile" value="0180000000${i}"></div>
          <div style="grid-column: span 2;"><label>ঠিকানা</label><input id="m${i}_address" value="জামালপুর, বাংলাদেশ"></div>
          <div><label>অংশ</label><input id="m${i}_share" value="${i === 1 ? "50%" : "50%"}"></div>
          <div><label>প্রত্যয়নকারীর NID</label><input id="m${i}_attestor_nid" value="9876543210"></div>
          <div><label>প্রত্যয়নকারীর জন্মতারিখ</label><input id="m${i}_attestor_dob" value="01-01-1970"></div>
        </div>
      </div>
    `;
  }

  function renderNomineeUI() {
    if (!nomineeDynamicArea) return;

    const count = nomineeCountEl ? nomineeCountEl.value : "1";
    const adultChecked = nomAdult ? nomAdult.checked : true;
    const minorChecked = nomMinor ? nomMinor.checked : false;

    nomineeDynamicArea.innerHTML = "";

    let types = [];

    if (count === "1") {
      if (adultChecked && !minorChecked) types = ["adult"];
      else if (!adultChecked && minorChecked) types = ["minor"];
      else if (adultChecked && minorChecked) types = ["adult"];
      else types = ["adult"];
    }

    if (count === "2") {
      if (adultChecked && !minorChecked) types = ["adult", "adult"];
      else if (!adultChecked && minorChecked) types = ["minor", "minor"];
      else if (adultChecked && minorChecked) types = ["adult", "minor"];
      else types = ["adult", "adult"];
    }

    types.forEach((t, idx) => {
      if (t === "adult") nomineeDynamicArea.innerHTML += adultNomineeHTML(idx + 1);
      if (t === "minor") nomineeDynamicArea.innerHTML += minorNomineeHTML(idx + 1);
    });
  }

  nomineeCountEl && nomineeCountEl.addEventListener("change", renderNomineeUI);
  nomAdult && nomAdult.addEventListener("change", renderNomineeUI);
  nomMinor && nomMinor.addEventListener("change", renderNomineeUI);

  renderNomineeUI();

  /* ================================
     Next Button (Validation Gate)
  =================================== */

  goNextBtn &&
    goNextBtn.addEventListener("click", function () {
      const schemeType = document.getElementById("schemeType")?.value;
      const amount = document.getElementById("amount")?.value;

      if (!schemeType || !amount) {
        alert("❌ সঞ্চয়পত্রের ধরন এবং টাকার পরিমাণ অবশ্যই নির্বাচন করতে হবে!");
        return;
      }

      buyerSection.style.display = "block";

      const buyerCount = document.getElementById("buyerCount")?.value || "1";
      buyer2Col && (buyer2Col.style.display = buyerCount === "2" ? "block" : "none");

      // cheque visibility
      const payType = document.getElementById("pay_type")?.value || "";
      if (chequeBox) chequeBox.style.display = payType === "চেক" ? "block" : "none";

      // pension visibility
      if (pensionBox) pensionBox.style.display = schemeType === "পেনশনার সঞ্চয়পত্র" ? "block" : "none";

      // refresh nominees
      renderNomineeUI();
    });

  /* ================================
     Live toggle cheque/pension
  =================================== */

  const payTypeEl = document.getElementById("pay_type");
  payTypeEl &&
    payTypeEl.addEventListener("change", function (e) {
      if (!chequeBox) return;
      chequeBox.style.display = e.target.value === "চেক" ? "block" : "none";
    });

  const schemeTypeEl = document.getElementById("schemeType");
  schemeTypeEl &&
    schemeTypeEl.addEventListener("change", function (e) {
      if (!pensionBox) return;
      pensionBox.style.display = e.target.value === "পেনশনার সঞ্চয়পত্র" ? "block" : "none";
    });

  /* ================================
     Print
  =================================== */

  printBtn &&
    printBtn.addEventListener("click", function () {
      if (!lastGeneratedBlobUrl) {
        alert("আগে Generate PDF করুন।");
        return;
      }
      const w = window.open(lastGeneratedBlobUrl, "_blank");
      w.addEventListener("load", function () {
        w.focus();
        w.print();
      });
    });

  /* ================================
     Generate PDF
  =================================== */

  goPdfBtn &&
    goPdfBtn.addEventListener("click", function () {
      alert("Generate button clicked ✅");

      try {
        if (typeof PDFLib === "undefined") {
          alert("❌ PDFLib not loaded! pdf-lib.min.js ঠিক আছে কিনা দেখুন।");
          return;
        }

        const data = collectFormData();
        if (!data.schemeType || !data.amount) {
          alert("সঞ্চয়পত্র ধরন ও টাকার পরিমাণ অবশ্যই দিতে হবে।");
          return;
        }

        fetch("content.pdf")
          .then(function (res) {
            if (!res.ok) throw new Error("content.pdf fetch failed: " + res.status);
            return res.arrayBuffer();
          })
          .then(function (pdfBytes) {
            return PDFLib.PDFDocument.load(pdfBytes);
          })
          .then(function (pdfDoc) {
            const page = pdfDoc.getPages()[0];

            // ✅ Mapping: UNCHANGED
            const map = {
				schemeType: { x: 200, y: 884, size: 22 },
				amount: { x: 200, y: 453, size: 22 },
				amountWords: { x: 350, y: 453, size: 22 },

				b1_name: { x: 220, y: 849, size: 22 },
				b1_nid: { x: 200, y: 833, size: 20 },
				b1_dob: { x: 200, y: 815, size: 20 },
				b1_address: { x: 198, y: 796, size: 18 },
				b1_mobile: { x: 200, y: 779, size: 20 },
				b1_tin: { x: 200, y: 759, size: 22 },

				b2_name: { x: 220, y: 741, size: 22 },
				b2_nid: { x: 201.25, y: 725, size: 20 },
				b2_dob: { x: 200, y: 705, size: 22 },
				b2_address: { x: 199.38, y: 687, size: 19 },
				b2_mobile: { x: 198.75, y: 667, size: 22 },
				b2_tin: { x: 200, y: 649, size: 22 },

				bank_title_bn: { x: 200, y: 622, size: 22 },
				bank_title_en: { x: 420, y: 622, size: 22 },
				bank_acc: { x: 200, y: 602, size: 22 },
				bank_branch: { x: 405.63, y: 602, size: 20 },
				bank_routing: { x: 200, y: 578, size: 20 },

				bank_type: { x: 450, y: 577, size: 20 },

				pay_type: { x: 200, y: 437, size: 20 },
				cheque_no: { x: 198.88, y: 419, size: 20 },
				cheque_date: { x: 280.13, y: 419, size: 20 },
				cheque_bank: { x: 412, y: 419, size: 18 },

				b1_type: { x: 200, y: 477, size: 20 },
				b2_type: { x: 445, y: 477, size: 20 },

				// Adult nominees ,  ,   ,
				n1_name: { x: 198.13, y: 399, size: 18 },
				n1_nid: { x: 200, y: 377, size: 20 },
				n1_dob: { x: 200, y: 354, size: 20 },
				n1_address: { x: 199.38, y: 332, size: 16, maxWidth:250},
				n1_share: { x: 199.38, y: 299, size: 20 },
				n1_mobile: { x: 199.38, y: 285, size: 20 },
				n1_relation: { x: 199.38, y: 269, size: 20 },

				n2_name: { x: 461.88, y: 399, size: 18 },
				n2_nid: { x: 441.25, y: 377, size: 20 },
				n2_dob: { x: 440.63, y: 354, size: 20 },
				n2_address: { x: 441.25, y: 332, size: 16, maxWidth:250},
				n2_share: { x: 440.63, y: 299, size: 20 },
				n2_mobile: { x: 441.25, y: 285, size: 20 },
				n2_relation: { x: 441.25, y: 269, size: 20 },

				// Minor/org block ,  ,   ,
				minor_name: { x: 198.75, y: 234, size: 18 },
				minor_address: { x: 198.75, y: 202, size: 18 },
				minor_share: { x: 198.75, y: 174, size: 18 },
				attestor_nid: { x: 455, y: 234, size: 18 },
				attestor_dob: { x: 505.63, y: 199, size: 18 },
				minor_mobile: { x: 467.5, y: 172, size: 18 },

				// ✅ Pension mapping, (UNCHANGED) ,   ,
				ppo: { x: 195.13, y: 556, size: 18 },
				ppo_date: { x: 198.88, y: 547, size: 16 },
				gratuity: { x: 401.38, y: 555, size: 22 },
				deceased_family: { x: 333.25, y: 517, size: 18 },
				deceased_dob: { x: 99.5, y: 517, size: 20 },
				gpf_balance: { x: 381.25, y: 497, size: 20 },
				ppo_copy: { x: 197.5, y: 497, size: 22 },

            };

            function drawBanglaAsImageAt(x, y, size, text) {
              if (!text && text !== 0) return Promise.resolve();
              const pngUrl = textToPngDataUrl(String(text), size);
              return fetch(pngUrl)
                .then((r) => r.arrayBuffer())
                .then((bytes) => pdfDoc.embedPng(bytes))
                .then((img) => {
                  const scale = 0.55;
                  page.drawImage(img, {
                    x,
                    y,
                    width: img.width * scale,
                    height: img.height * scale,
                  });
                });
            }

            function drawBanglaAsImage(key, text) {
			  if (!text && text !== 0) return Promise.resolve();
			  const m = map[key];
			  if (!m) return Promise.resolve();

			  const pngUrl = textToPngDataUrl(String(text), m.size, m.maxWidth || null);

			  return fetch(pngUrl)
				.then((r) => r.arrayBuffer())
				.then((bytes) => pdfDoc.embedPng(bytes))
				.then((img) => {
				  const scale = 0.55;
				  page.drawImage(img, {
					x: m.x,
					y: m.y,
					width: img.width * scale,
					height: img.height * scale,
				  });
				});
			}


            function drawTickAt(key, absX) {
              const m = map[key];
              if (!m) return Promise.resolve();
              return drawBanglaAsImageAt(absX, m.y, m.size, "✔");
            }

            function normalizeBangla(v) {
              return String(v || "").replace(/\s+/g, " ").trim();
            }
            function isShahar(v) {
              v = normalizeBangla(v);
              return v.includes("শহর");
            }
            function isCholti(v) {
              v = normalizeBangla(v);
              return v.includes("চলতি");
            }
            function isCheque(v) {
              v = normalizeBangla(v);
              return v.includes("চেক");
            }

            const amountWords = banglaNumberToWords(data.amount);
            const amountWordsFull = amountWords ? amountWords + " টাকা" : "";

            let chain = Promise.resolve()
              .then(() => drawBanglaAsImage("schemeType", data.schemeType))
              .then(() => drawBanglaAsImage("amount", toBanglaDigits(data.amount)))
              .then(() => drawBanglaAsImage("amountWords", amountWordsFull))

              .then(() => drawBanglaAsImage("b1_name", data.b1.name))
              .then(() => drawBanglaAsImage("b1_nid", toBanglaDigits(data.b1.nid)))
              .then(() => drawBanglaAsImage("b1_dob", toBanglaDigits(data.b1.dob)))
              .then(() => drawBanglaAsImage("b1_address", data.b1.address))
              .then(() => drawBanglaAsImage("b1_mobile", toBanglaDigits(data.b1.mobile)))
              .then(() => drawBanglaAsImage("b1_tin", toBanglaDigits(data.b1.tin)));

            // Buyer-2
            if (data.buyerCount === "2") {
              chain = chain
                .then(() => drawBanglaAsImage("b2_name", data.b2.name))
                .then(() => drawBanglaAsImage("b2_nid", toBanglaDigits(data.b2.nid)))
                .then(() => drawBanglaAsImage("b2_dob", toBanglaDigits(data.b2.dob)))
                .then(() => drawBanglaAsImage("b2_address", data.b2.address))
                .then(() => drawBanglaAsImage("b2_mobile", toBanglaDigits(data.b2.mobile)))
                .then(() => drawBanglaAsImage("b2_tin", toBanglaDigits(data.b2.tin)));
            }

            // Bank info
            chain = chain
              .then(() => drawBanglaAsImage("bank_title_bn", data.bank.title_bn))
              .then(() => drawBanglaAsImage("bank_title_en", data.bank.title_en))
              .then(() => drawBanglaAsImage("bank_acc", data.bank.acc))
              .then(() => drawBanglaAsImage("bank_branch", data.bank.branch))
              .then(() => drawBanglaAsImage("bank_routing", toBanglaDigits(data.bank.routing)));

            // Bank type tick
            if (data.bank && data.bank.type) {
              const tickX = isCholti(data.bank.type) ? 525 : 450;
              chain = chain.then(() => drawTickAt("bank_type", tickX));
            }

            // Buyer type tick
            if (data.buyerType && data.buyerType.b1) {
              const tickX = isShahar(data.buyerType.b1) ? 275 : 200;
              chain = chain.then(() => drawTickAt("b1_type", tickX));
            }

            if (data.buyerCount === "2" && data.buyerType && data.buyerType.b2) {
              const tickX = isShahar(data.buyerType.b2) ? 520 : 445;
              chain = chain.then(() => drawTickAt("b2_type", tickX));
            }

            // Payment tick
            if (data.payment && data.payment.type) {
              const tickX = isCheque(data.payment.type) ? 252 : 200;
              chain = chain.then(() => drawTickAt("pay_type", tickX));

              if (isCheque(data.payment.type)) {
                chain = chain
                  .then(() => drawBanglaAsImage("cheque_no", toBanglaDigits(data.payment.cheque_no)))
                  .then(() => drawBanglaAsImage("cheque_date", toBanglaDigits(data.payment.cheque_date)))
                  .then(() => drawBanglaAsImage("cheque_bank", data.payment.cheque_bank));
              }
            }

            // ✅ Dynamic nominee PDF fill
            if (data.nominee && data.nominee.dynamicNominees && data.nominee.dynamicNominees.length) {
              const noms = data.nominee.dynamicNominees;
              const adults = noms.filter((n) => n.type === "adult");
              const minors = noms.filter((n) => n.type === "minor");

              // Adult nominee-1
              if (adults[0]) {
                chain = chain
                  .then(() => drawBanglaAsImage("n1_name", adults[0].name))
                  .then(() => drawBanglaAsImage("n1_nid", toBanglaDigits(adults[0].nid)))
                  .then(() => drawBanglaAsImage("n1_dob", toBanglaDigits(adults[0].dob)))
                  .then(() => drawBanglaAsImage("n1_address", adults[0].address))
                  .then(() => drawBanglaAsImage("n1_share", adults[0].share))
                  .then(() => drawBanglaAsImage("n1_mobile", toBanglaDigits(adults[0].mobile)))
                  .then(() => drawBanglaAsImage("n1_relation", adults[0].relation));
              }

              // Adult nominee-2
              if (adults[1]) {
                chain = chain
                  .then(() => drawBanglaAsImage("n2_name", adults[1].name))
                  .then(() => drawBanglaAsImage("n2_nid", toBanglaDigits(adults[1].nid)))
                  .then(() => drawBanglaAsImage("n2_dob", toBanglaDigits(adults[1].dob)))
                  .then(() => drawBanglaAsImage("n2_address", adults[1].address))
                  .then(() => drawBanglaAsImage("n2_share", adults[1].share))
                  .then(() => drawBanglaAsImage("n2_mobile", toBanglaDigits(adults[1].mobile)))
                  .then(() => drawBanglaAsImage("n2_relation", adults[1].relation));
              }

              // Minor nominee block (supports up to 2 combined)
              if (minors[0]) {
                let minorName = minors[0].name;
                let minorAddress = minors[0].address;
                let minorShare = minors[0].share;
                let minorMobile = minors[0].mobile;

                if (minors[1]) {
                  minorName += "\n" + minors[1].name;
                  minorAddress += "\n" + minors[1].address;
                  minorShare += " + " + minors[1].share;
                  minorMobile += " / " + minors[1].mobile;
                }

                chain = chain
                  .then(() => drawBanglaAsImage("minor_name", minorName))
                  .then(() => drawBanglaAsImage("minor_address", minorAddress))
                  .then(() => drawBanglaAsImage("minor_share", minorShare))
                  .then(() => drawBanglaAsImage("attestor_nid", toBanglaDigits(minors[0].attestor_nid)))
                  .then(() => drawBanglaAsImage("attestor_dob", toBanglaDigits(minors[0].attestor_dob)))
                  .then(() => drawBanglaAsImage("minor_mobile", toBanglaDigits(minorMobile)));
              }
            }

            // ✅ Pensioner extra (your missing part restored)
            if (data.schemeType === "পেনশনার সঞ্চয়পত্র" && data.pension) {
              chain = chain
                .then(() => drawBanglaAsImage("ppo", data.pension.ppo))
                .then(() => drawBanglaAsImage("ppo_date", toBanglaDigits(data.pension.ppo_date)))
                .then(() => drawBanglaAsImage("gratuity", toBanglaDigits(data.pension.gratuity)))
                .then(() => drawBanglaAsImage("deceased_family", data.pension.deceased_family))
                .then(() => drawBanglaAsImage("deceased_dob", toBanglaDigits(data.pension.deceased_dob)))
                .then(() => drawBanglaAsImage("gpf_balance", toBanglaDigits(data.pension.gpf_balance)))
                .then(() => drawBanglaAsImage("ppo_copy", data.pension.ppo_copy ? "✔" : ""));
            }

            return chain.then(() => pdfDoc.save());
          })
          .then(function (outBytes) {
            const blob = new Blob([outBytes], { type: "application/pdf" });
            const url = URL.createObjectURL(blob);

            if (lastGeneratedBlobUrl) URL.revokeObjectURL(lastGeneratedBlobUrl);
            lastGeneratedBlobUrl = url;

            const a = document.createElement("a");
            a.href = url;
            a.download = "sanchaypatra-filled.pdf";
            a.click();
          })
          .catch(function (err) {
            console.error(err);
            alert("❌ Error: " + err.message);
          });

      } catch (err) {
        console.error(err);
        alert("❌ Error: " + err.message);
      }
    });

  document.getElementById("openCoordTool") &&
    document.getElementById("openCoordTool").addEventListener("click", function () {
      window.open("coord-tool.html", "_blank");
    });
});

/* ================================
   Helpers
=================================== */

// Canvas -> PNG (Supports Bengali shaping)
function textToPngDataUrl(text, fontSize = 12, maxWidthPx = null) {
  const canvas = document.createElement("canvas");
  const ctx = canvas.getContext("2d");

  const padding = 6;
  ctx.font = `${fontSize}px "Noto Sans Bengali", Arial`;

  // ✅ If no maxWidth => old behavior (single line)
  if (!maxWidthPx) {
    const metrics = ctx.measureText(text);

    canvas.width = Math.ceil(metrics.width + padding * 2);
    canvas.height = Math.ceil(fontSize * 1.7 + padding * 2);

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.font = `${fontSize}px "Noto Sans Bengali", Arial`;
    ctx.fillStyle = "#000";
    ctx.textBaseline = "top";
    ctx.fillText(text, padding, padding);

    return canvas.toDataURL("image/png");
  }

  // ✅ WRAP MODE (only for nominee address)
  function wrapLines(str) {
    const words = String(str).split(" ");
    let lines = [];
    let line = "";

    for (let w of words) {
      const testLine = line ? line + " " + w : w;
      const width = ctx.measureText(testLine).width;

      if (width > maxWidthPx) {
        if (line) lines.push(line);
        line = w;
      } else {
        line = testLine;
      }
    }
    if (line) lines.push(line);

    return lines;
  }

  const lines = wrapLines(text);

  canvas.width = maxWidthPx + padding * 2;
  canvas.height = Math.ceil(lines.length * fontSize * 1.3 + padding * 2);

  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ctx.font = `${fontSize}px "Noto Sans Bengali", Arial`;
  ctx.fillStyle = "#000";
  ctx.textBaseline = "top";

  lines.forEach((line, i) => {
    ctx.fillText(line, padding, padding + i * fontSize * 1.3);
  });

  return canvas.toDataURL("image/png");
}

function toBanglaDigits(str) {
  const map = { "0":"০","1":"১","2":"২","3":"৩","4":"৪","5":"৫","6":"৬","7":"৭","8":"৮","9":"৯" };
  return String(str || "").replace(/[0-9]/g, d => map[d]);
}

function banglaNumberToWords(num) {
  num = parseInt(num, 10);
  if (isNaN(num) || num <= 0) return "";

  const ones = [
    "", "এক", "দুই", "তিন", "চার", "পাঁচ", "ছয়", "সাত", "আট", "নয়",
    "দশ", "এগারো", "বারো", "তেরো", "চৌদ্দ", "পনেরো", "ষোল", "সতেরো", "আঠারো", "উনিশ",
    "বিশ", "একুশ", "বাইশ", "তেইশ", "চব্বিশ", "পঁচিশ", "ছাব্বিশ", "সাতাশ", "আটাশ", "ঊনত্রিশ",
    "ত্রিশ", "একত্রিশ", "বত্রিশ", "তেত্রিশ", "চৌত্রিশ", "পঁয়ত্রিশ", "ছত্রিশ", "সাঁইত্রিশ", "আটত্রিশ", "ঊনচল্লিশ",
    "চল্লিশ", "একচল্লিশ", "বিয়াল্লিশ", "তেতাল্লিশ", "চুয়াল্লিশ", "পঁয়তাল্লিশ", "ছেচল্লিশ", "সাতচল্লিশ", "আটচল্লিশ", "ঊনপঞ্চাশ",
    "পঞ্চাশ", "একান্ন", "বায়ান্ন", "তেপ্পান্ন", "চুয়ান্ন", "পঞ্চান্ন", "ছাপ্পান্ন", "সাতান্ন", "আটান্ন", "ঊনষাট",
    "ষাট", "একষট্টি", "বাষট্টি", "তেষট্টি", "চৌষট্টি", "পঁয়ষট্টি", "ছেষট্টি", "সাতষট্টি", "আটষট্টি", "ঊনসত্তর",
    "সত্তর", "একাত্তর", "বাহাত্তর", "তিয়াত্তর", "চুয়াত্তর", "পঁচাত্তর", "ছিয়াত্তর", "সাতাত্তর", "আটাত্তর", "ঊনআশি",
    "আশি", "একাশি", "বিরাশি", "তিরাশি", "চুরাশি", "পঁচাশি", "ছিয়াশি", "সাতাশি", "আটাশি", "ঊননব্বই",
    "নব্বই", "একানব্বই", "বিরানব্বই", "তিরানব্বই", "চুরানব্বই", "পঁচুরানব্বই", "ছিয়ানব্বই", "সাতানব্বই", "আটানব্বই", "নিরানব্বই"
  ];

  function twoDigits(n) {
    return ones[n] || "";
  }

  let words = [];
  const কোটি = Math.floor(num / 10000000);
  num = num % 10000000;
  const লক্ষ = Math.floor(num / 100000);
  num = num % 100000;
  const হাজার = Math.floor(num / 1000);
  num = num % 1000;
  const শত = Math.floor(num / 100);
  num = num % 100;
  const rest = num;

  if (কোটি) words.push(twoDigits(কোটি) + " কোটি");
  if (লক্ষ) words.push(twoDigits(লক্ষ) + " লক্ষ");
  if (হাজার) words.push(twoDigits(হাজার) + " হাজার");
  if (শত) words.push(ones[শত] + " শত");
  if (rest) words.push(twoDigits(rest));

  return words.join(" ").replace(/\s+/g, " ").trim();
}

/* ================================
   Collect Form Data (FULL)
=================================== */

function collectFormData() {
  const nomineeCount = document.getElementById("nomineeCount")?.value || "1";
  const nomAdultChecked = document.getElementById("nomAdult")?.checked || false;
  const nomMinorChecked = document.getElementById("nomMinor")?.checked || false;

  let nomineeTypes = [];

  if (nomineeCount === "1") {
    if (nomAdultChecked && !nomMinorChecked) nomineeTypes = ["adult"];
    else if (!nomAdultChecked && nomMinorChecked) nomineeTypes = ["minor"];
    else if (nomAdultChecked && nomMinorChecked) nomineeTypes = ["adult"];
    else nomineeTypes = ["adult"];
  }

  if (nomineeCount === "2") {
    if (nomAdultChecked && !nomMinorChecked) nomineeTypes = ["adult", "adult"];
    else if (!nomAdultChecked && nomMinorChecked) nomineeTypes = ["minor", "minor"];
    else if (nomAdultChecked && nomMinorChecked) nomineeTypes = ["adult", "minor"];
    else nomineeTypes = ["adult", "adult"];
  }

  const dynamicNominees = nomineeTypes.map((t, idx) => {
    const i = idx + 1;
    if (t === "adult") {
      return {
        type: "adult",
        name: document.getElementById(`n${i}_name`)?.value || "",
        nid: document.getElementById(`n${i}_nid`)?.value || "",
        dob: document.getElementById(`n${i}_dob`)?.value || "",
        address: document.getElementById(`n${i}_address`)?.value || "",
        share: document.getElementById(`n${i}_share`)?.value || "",
        mobile: document.getElementById(`n${i}_mobile`)?.value || "",
        relation: document.getElementById(`n${i}_relation`)?.value || "",
      };
    } else {
      return {
        type: "minor",
        name: document.getElementById(`m${i}_name`)?.value || "",
        address: document.getElementById(`m${i}_address`)?.value || "",
        share: document.getElementById(`m${i}_share`)?.value || "",
        mobile: document.getElementById(`m${i}_mobile`)?.value || "",
        attestor_nid: document.getElementById(`m${i}_attestor_nid`)?.value || "",
        attestor_dob: document.getElementById(`m${i}_attestor_dob`)?.value || "",
      };
    }
  });

  return {
    schemeType: document.getElementById("schemeType")?.value || "",
    amount: document.getElementById("amount")?.value || "",
    buyerCount: document.getElementById("buyerCount")?.value || "1",

    b1: {
      name: document.getElementById("b1_name")?.value || "",
      nid: document.getElementById("b1_nid")?.value || "",
      dob: document.getElementById("b1_dob")?.value || "",
      address: document.getElementById("b1_address")?.value || "",
      mobile: document.getElementById("b1_mobile")?.value || "",
      tin: document.getElementById("b1_tin")?.value || "",
    },

    b2: {
      name: document.getElementById("b2_name")?.value || "",
      nid: document.getElementById("b2_nid")?.value || "",
      dob: document.getElementById("b2_dob")?.value || "",
      address: document.getElementById("b2_address")?.value || "",
      mobile: document.getElementById("b2_mobile")?.value || "",
      tin: document.getElementById("b2_tin")?.value || "",
    },

    bank: {
      title_bn: document.getElementById("bank_title_bn")?.value || "",
      title_en: document.getElementById("bank_title_en")?.value || "",
      acc: document.getElementById("bank_acc")?.value || "",
      branch: document.getElementById("bank_branch")?.value || "",
      routing: document.getElementById("bank_routing")?.value || "",
      type: document.getElementById("bank_type")?.value || "",
    },

    payment: {
      type: document.getElementById("pay_type")?.value || "",
      cheque_no: document.getElementById("cheque_no")?.value || "",
      cheque_date: document.getElementById("cheque_date")?.value || "",
      cheque_bank: document.getElementById("cheque_bank")?.value || "",
    },

    buyerType: {
      b1: document.getElementById("b1_type")?.value || "",
      b2: document.getElementById("b2_type")?.value || "",
    },

    nominee: {
      dynamicNominees,
    },

    // ✅ Pension data restored (important)
    pension: {
      ppo: document.getElementById("ppo")?.value || "",
      ppo_date: document.getElementById("ppo_date")?.value || "",
      gratuity: document.getElementById("gratuity")?.value || "",
      deceased_family: document.getElementById("deceased_family")?.value || "",
      deceased_dob: document.getElementById("deceased_dob")?.value || "",
      gpf_balance: document.getElementById("gpf_balance")?.value || "",
      ppo_copy: document.getElementById("ppo_copy")?.checked || false,
    },
  };
}
