(() => {
  const $ = (s) => document.querySelector(s);
  const rm = (n) => "RM" + n.toLocaleString("en-MY");
  const warna = {
    "Rumah Teres": ["#2f7d62", "#174a3a"], Banglo: ["#7a5c2e", "#3f2f17"],
    "Semi-D": ["#3c6e8f", "#1d3a4d"], Kondominium: ["#5a4f8a", "#2c2650"],
    Apartment: ["#8a4f6a", "#4a2638"], Tanah: ["#6b8a3a", "#34461a"], Kedai: ["#a0612b", "#55300f"],
  };
  const ikon = { Tanah: "🌳", Kedai: "🏪", Kondominium: "🏢", Apartment: "🏢" };
  const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));

  function gambarBg(p, i = 0) {
    if (p.gambar && p.gambar.length) return { style: `background-image:url('${encodeURI(p.gambar[i])}')`, html: "" };
    const [a, b] = warna[p.jenis] || ["#3d5a50", "#1e2f29"];
    return { style: `background:linear-gradient(135deg,${a},${b})`, html: `<div>${ikon[p.jenis] || "🏠"}<span>${esc(p.jenis)}</span></div>`, ph: true };
  }
  const hargaTxt = (p) => rm(p.harga) + (p.tujuan === "sewa" ? " <small>/ bulan</small>" : "");
  const luas = (p) => `${p.keluasan.toLocaleString("en-MY")} ${p.unit === "ekar" ? "ekar" : "kp"}`;
  const waLink = (msg) => `https://wa.me/${TETAPAN.whatsapp}?text=${encodeURIComponent(msg)}`;

  // Tetapan
  document.title = TETAPAN.namaSyarikat;
  $("#nama-syarikat").textContent = TETAPAN.namaSyarikat;
  $("#nama-footer").textContent = TETAPAN.namaSyarikat;
  $("#slogan").textContent = TETAPAN.slogan;
  $("#tahun").textContent = new Date().getFullYear();
  $("#wa-header").href = waLink(`Salam, saya berminat dengan hartanah ${TETAPAN.namaSyarikat}.`);

  // Isi pilihan penapis
  const isi = (sel, vals) => [...new Set(vals)].sort().forEach((v) => sel.add(new Option(v, v)));
  isi($("#jenis"), SENARAI.map((p) => p.jenis));
  isi($("#negeri"), SENARAI.map((p) => p.negeri));

  let tujuan = "semua";
  document.querySelectorAll(".tab").forEach((t) =>
    t.addEventListener("click", () => {
      document.querySelectorAll(".tab").forEach((x) => x.classList.toggle("active", x === t));
      tujuan = t.dataset.tujuan;
      render();
    })
  );
  ["#q", "#jenis", "#negeri", "#harga", "#susun"].forEach((s) => $(s).addEventListener("input", render));

  function tapis() {
    const q = $("#q").value.trim().toLowerCase();
    const jenis = $("#jenis").value, negeri = $("#negeri").value;
    const [min, max] = ($("#harga").value || "-").split("-").map((x) => (x === "" ? null : +x));
    let out = SENARAI.filter((p) =>
      (tujuan === "semua" || p.tujuan === tujuan) &&
      (!jenis || p.jenis === jenis) && (!negeri || p.negeri === negeri) &&
      (min == null || p.harga >= min) && (max == null || p.harga < max) &&
      (!q || [p.tajuk, p.lokasi, p.negeri, p.jenis, p.penerangan, ...(p.ciri || [])].join(" ").toLowerCase().includes(q))
    );
    const s = $("#susun").value;
    if (s === "murah") out = [...out].sort((a, b) => a.harga - b.harga);
    if (s === "mahal") out = [...out].sort((a, b) => b.harga - a.harga);
    // Yang terjual sentiasa di bawah
    return out.sort((a, b) => (a.status === "terjual") - (b.status === "terjual"));
  }

  function render() {
    const list = tapis();
    $("#kiraan").textContent = `${list.length} hartanah dijumpai`;
    $("#kosong").hidden = list.length > 0;
    $("#grid").innerHTML = list.map((p) => {
      const g = gambarBg(p);
      const st = p.status !== "tersedia" ? `<div class="status ${p.status}">${p.status === "terjual" ? (p.tujuan === "sewa" ? "Disewa" : "Terjual") : "Ditempah"}</div>` : "";
      return `<article class="card" tabindex="0" data-id="${esc(p.id)}">
        <div class="thumb ${g.ph ? "ph" : ""}" style="${g.style}">${g.html}
          <span class="badge b-${p.tujuan}">${p.tujuan === "jual" ? "Dijual" : "Disewa"}</span>${st}</div>
        <div class="body">
          <div class="price">${hargaTxt(p)}</div>
          <p class="title">${esc(p.tajuk)}</p>
          <p class="loc">📍 ${esc(p.lokasi)}, ${esc(p.negeri)}</p>
          <div class="specs">${p.bilik ? `<span>🛏 ${p.bilik}</span>` : ""}${p.bilikAir ? `<span>🛁 ${p.bilikAir}</span>` : ""}<span>📐 ${luas(p)}</span></div>
        </div></article>`;
    }).join("");
  }

  const dlg = $("#detail");
  function buka(id) {
    const p = SENARAI.find((x) => x.id === id);
    if (!p) return;
    let i = 0;
    const n = (p.gambar || []).length;
    const fakta = [
      ["Jenis", p.jenis], ["Pegangan", p.pegangan], ["Keluasan", luas(p)],
      p.bilik ? ["Bilik tidur", p.bilik] : null, p.bilikAir ? ["Bilik air", p.bilikAir] : null, ["Rujukan", p.id],
    ].filter(Boolean);
    const g = gambarBg(p);
    dlg.innerHTML = `<button class="close" aria-label="Tutup">✕</button>
      <div class="d-img ${g.ph ? "ph" : ""}" style="${g.style}">${g.html}
        ${n > 1 ? `<div class="d-nav"><button data-d="-1" aria-label="Sebelum">‹</button><button data-d="1" aria-label="Seterusnya">›</button></div>` : ""}</div>
      <div class="d-body">
        <div class="price">${hargaTxt(p)}</div>
        <h2>${esc(p.tajuk)}</h2>
        <p class="loc">📍 ${esc(p.lokasi)}, ${esc(p.negeri)}</p>
        <div class="facts">${fakta.map(([k, v]) => `<div class="fact"><b>${k}</b>${esc(v)}</div>`).join("")}</div>
        <p>${esc(p.penerangan)}</p>
        <div class="chips">${(p.ciri || []).map((c) => `<span class="chip">${esc(c)}</span>`).join("")}</div>
        ${p.status === "terjual" ? `<p class="loc">Hartanah ini sudah tidak tersedia.</p>` :
          `<a class="btn-wa" target="_blank" rel="noopener" href="${waLink(`Salam, saya berminat dengan "${p.tajuk}" (Ruj: ${p.id}). Masih tersedia?`)}">💬 Tanya melalui WhatsApp</a>`}
      </div>`;
    dlg.querySelector(".close").onclick = () => dlg.close();
    dlg.querySelectorAll(".d-nav button").forEach((b) => (b.onclick = () => {
      i = (i + +b.dataset.d + n) % n;
      dlg.querySelector(".d-img").style.backgroundImage = `url('${encodeURI(p.gambar[i])}')`;
    }));
    dlg.showModal();
  }
  dlg.addEventListener("click", (e) => { if (e.target === dlg) dlg.close(); });
  $("#grid").addEventListener("click", (e) => { const c = e.target.closest(".card"); if (c) buka(c.dataset.id); });
  $("#grid").addEventListener("keydown", (e) => { const c = e.target.closest(".card"); if (c && e.key === "Enter") buka(c.dataset.id); });

  render();
})();
