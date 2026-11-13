(() => {
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => [...el.querySelectorAll(s)];
  const rm = (n) => "RM" + Math.round(n).toLocaleString("en-MY");
  const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  const waLink = (msg) => `https://wa.me/${TETAPAN.whatsapp}?text=${encodeURIComponent(msg)}`;
  const luas = (p) => `${p.keluasan.toLocaleString("en-MY")} ${p.unit === "ekar" ? "ekar" : "kp"}`;
  const hargaHtml = (p, big) =>
    `${rm(p.harga)}${p.tujuan === "sewa" ? `<span class="${big ? "text-base" : "text-sm"} font-medium text-zinc-400"> /bulan</span>` : ""}`;

  // ---------- Placeholder bila tiada gambar ----------
  const tona = {
    "Rumah Teres": "from-emerald-100 to-teal-50", Banglo: "from-amber-100 to-orange-50", "Semi-D": "from-sky-100 to-indigo-50",
    Kondominium: "from-violet-100 to-fuchsia-50", Apartment: "from-rose-100 to-pink-50", Tanah: "from-lime-100 to-green-50", Kedai: "from-orange-100 to-yellow-50",
  };
  const ikon = {
    Tanah: '<path d="M3 20h18M5 20l4-8 3 4 3-6 4 10"/>',
    Kedai: '<path d="M4 9h16l-1-5H5L4 9Zm0 0v11h16V9M9 20v-6h6v6"/>',
    Kondominium: '<path d="M5 21V4h10v17M15 9h4v12M8 8h2m-2 4h2m-2 4h2"/>',
    Apartment: '<path d="M5 21V4h10v17M15 9h4v12M8 8h2m-2 4h2m-2 4h2"/>',
  };
  const placeholder = (p, cls = "") => `
    <div class="grid h-full w-full place-items-center bg-gradient-to-br ${tona[p.jenis] || "from-zinc-100 to-zinc-50"} ${cls}">
      <svg class="h-14 w-14 text-zinc-900/20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
        ${ikon[p.jenis] || '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z"/>'}</svg>
    </div>`;
  const media = (p, i = 0, cls = "") =>
    p.gambar && p.gambar.length
      ? `<img src="${esc(p.gambar[i])}" alt="${esc(p.tajuk)}" loading="lazy" class="h-full w-full object-cover ${cls}">`
      : placeholder(p, cls);

  // ---------- Kegemaran (disimpan dalam pelayar) ----------
  let fav = new Set();
  try { fav = new Set(JSON.parse(localStorage.getItem("fav") || "[]")); } catch {}
  const simpanFav = () => { try { localStorage.setItem("fav", JSON.stringify([...fav])); } catch {} };
  const hati = (on) => `<svg class="h-5 w-5" viewBox="0 0 24 24" fill="${on ? "#f43f5e" : "rgba(0,0,0,.25)"}" stroke="#fff" stroke-width="2"><path d="M12 21s-7.5-4.6-9.5-9.3C1 8 3.4 4.5 7 4.5c2 0 3.5 1 5 2.8 1.5-1.8 3-2.8 5-2.8 3.6 0 6 3.5 4.5 7.2C19.5 16.4 12 21 12 21Z"/></svg>`;

  // ---------- Tetapan laman ----------
  document.title = TETAPAN.namaSyarikat;
  $$("[data-nama]").forEach((e) => (e.textContent = TETAPAN.namaSyarikat));
  $("[data-slogan]").textContent = TETAPAN.slogan;
  $("[data-subslogan]").textContent = TETAPAN.subslogan || "";
  $$("[data-wa-umum]").forEach((a) => (a.href = waLink(`Salam, saya ingin bertanya tentang hartanah ${TETAPAN.namaSyarikat}.`)));
  $("#tahun").textContent = new Date().getFullYear();
  $("#stat-tersedia").textContent = SENARAI.filter((p) => p.status === "tersedia").length;

  // ---------- Penapis ----------
  const state = { tujuan: "semua", jenis: "" };
  [...new Set(SENARAI.map((p) => p.negeri))].sort().forEach((n) => $("#negeri").add(new Option(n, n)));
  const jenisList = ["", ...new Set(SENARAI.map((p) => p.jenis))];
  $("#jenis-chips").innerHTML = jenisList.map((j) => `<button data-jenis="${esc(j)}" class="chip">${j ? esc(j) : "Semua jenis"}</button>`).join("");

  function syncButtons() {
    $$(".tujuan").forEach((b) => {
      const on = b.dataset.tujuan === state.tujuan;
      b.className = `tujuan rounded-full px-4 py-1.5 text-sm font-semibold transition ${on ? "bg-zinc-900 text-white" : "text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900"}`;
    });
    $$("#jenis-chips button").forEach((b) => b.classList.toggle("chip-on", b.dataset.jenis === state.jenis));
  }
  $$(".tujuan").forEach((b) => b.addEventListener("click", () => { state.tujuan = b.dataset.tujuan; render(); }));
  $("#jenis-chips").addEventListener("click", (e) => { const b = e.target.closest("button"); if (b) { state.jenis = b.dataset.jenis; render(); } });
  ["#q", "#negeri", "#harga", "#susun"].forEach((s) => $(s).addEventListener("input", render));
  $("#reset").addEventListener("click", () => {
    state.tujuan = "semua"; state.jenis = "";
    $("#q").value = ""; $("#negeri").value = ""; $("#harga").value = "";
    render();
  });

  function tapis() {
    const q = $("#q").value.trim().toLowerCase();
    const negeri = $("#negeri").value;
    const [min, max] = ($("#harga").value || "-").split("-").map((x) => (x === "" ? null : +x));
    let out = SENARAI.filter((p) =>
      (state.tujuan === "semua" || p.tujuan === state.tujuan) &&
      (!state.jenis || p.jenis === state.jenis) && (!negeri || p.negeri === negeri) &&
      (min == null || p.harga >= min) && (max == null || p.harga < max) &&
      (!q || [p.tajuk, p.lokasi, p.negeri, p.jenis, p.penerangan, ...(p.ciri || [])].join(" ").toLowerCase().includes(q))
    );
    const kp = (p) => (p.unit === "ekar" ? p.keluasan * 43560 : p.keluasan);
    const s = $("#susun").value;
    if (s === "murah") out.sort((a, b) => a.harga - b.harga);
    if (s === "mahal") out.sort((a, b) => b.harga - a.harga);
    if (s === "luas") out.sort((a, b) => kp(b) - kp(a));
    return out.sort((a, b) => (a.status === "terjual") - (b.status === "terjual"));
  }

  const labelStatus = (p) =>
    p.status === "terjual" ? (p.tujuan === "sewa" ? "Sudah disewa" : "Terjual") : p.status === "tempahan" ? "Ditempah" : "";

  function kad(p) {
    const st = labelStatus(p);
    const dim = p.status === "terjual" ? "opacity-60 grayscale" : "";
    return `<article class="group cursor-pointer" data-id="${esc(p.id)}" tabindex="0">
      <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-zinc-100 ${dim}">
        <div class="h-full w-full transition duration-500 group-hover:scale-105">${media(p)}</div>
        <div class="absolute left-3 top-3 flex gap-1.5">
          <span class="rounded-full bg-white/95 px-2.5 py-1 text-xs font-semibold shadow-sm">${p.tujuan === "jual" ? "Dijual" : "Disewa"}</span>
          ${st ? `<span class="rounded-full bg-zinc-900/90 px-2.5 py-1 text-xs font-semibold text-white">${st}</span>` : ""}
        </div>
        <button data-fav="${esc(p.id)}" aria-label="Simpan" class="absolute right-3 top-3 transition hover:scale-110">${hati(fav.has(p.id))}</button>
        ${p.gambar && p.gambar.length > 1 ? `<span class="absolute bottom-3 right-3 rounded-full bg-black/50 px-2 py-0.5 text-xs font-medium text-white">1/${p.gambar.length}</span>` : ""}
      </div>
      <div class="mt-3 flex items-start justify-between gap-3">
        <h3 class="line-clamp-1 font-semibold">${esc(p.tajuk)}</h3>
      </div>
      <p class="text-sm text-zinc-500">${esc(p.lokasi)}, ${esc(p.negeri)}</p>
      <p class="mt-0.5 text-sm text-zinc-500">${[p.bilik ? `${p.bilik} bilik` : "", p.bilikAir ? `${p.bilikAir} bilik air` : "", luas(p)].filter(Boolean).join(" · ")}</p>
      <p class="mt-2 text-lg font-bold">${hargaHtml(p)}</p>
    </article>`;
  }

  function render() {
    syncButtons();
    const list = tapis();
    $("#kiraan").textContent = `${list.length} hartanah dijumpai`;
    $("#kosong").classList.toggle("hidden", list.length > 0);
    $("#grid").innerHTML = list.map(kad).join("");
  }

  $("#grid").addEventListener("click", (e) => {
    const f = e.target.closest("[data-fav]");
    if (f) {
      e.stopPropagation();
      const id = f.dataset.fav;
      fav.has(id) ? fav.delete(id) : fav.add(id);
      simpanFav();
      f.innerHTML = hati(fav.has(id));
      return;
    }
    const c = e.target.closest("[data-id]");
    if (c) location.hash = c.dataset.id;
  });
  $("#grid").addEventListener("keydown", (e) => { const c = e.target.closest("[data-id]"); if (c && e.key === "Enter") location.hash = c.dataset.id; });

  // ---------- Kalkulator pinjaman ----------
  const ansuran = (harga, depositPct, kadar, tahun) => {
    const P = harga * (1 - depositPct / 100), r = kadar / 100 / 12, n = tahun * 12;
    return r ? (P * r) / (1 - Math.pow(1 + r, -n)) : P / n;
  };
  const kalkulator = (p) => `
    <div class="rounded-2xl border border-zinc-200 p-5">
      <p class="font-semibold">Anggaran bayaran bulanan</p>
      <p class="mt-1 text-3xl font-extrabold tracking-tight" id="k-hasil"></p>
      <div class="mt-4 grid grid-cols-3 gap-3 text-xs font-semibold text-zinc-400">
        <label>Deposit (%)<input id="k-dep" type="number" min="0" max="90" value="10" class="mt-1 w-full rounded-lg border border-zinc-200 px-2 py-1.5 text-sm text-zinc-900 outline-none focus:border-zinc-400"></label>
        <label>Faedah (%)<input id="k-kadar" type="number" min="0" step="0.05" value="4.2" class="mt-1 w-full rounded-lg border border-zinc-200 px-2 py-1.5 text-sm text-zinc-900 outline-none focus:border-zinc-400"></label>
        <label>Tempoh (thn)<input id="k-thn" type="number" min="1" max="35" value="35" class="mt-1 w-full rounded-lg border border-zinc-200 px-2 py-1.5 text-sm text-zinc-900 outline-none focus:border-zinc-400"></label>
      </div>
      <p class="mt-3 text-xs text-zinc-400">Anggaran sahaja. Kadar sebenar bergantung kepada bank.</p>
    </div>`;

  // ---------- Paparan butiran ----------
  const dlg = $("#detail");
  function buka(id) {
    const p = SENARAI.find((x) => x.id === id);
    if (!p) return;
    const n = (p.gambar || []).length;
    const fakta = [
      ["Jenis", p.jenis], ["Keluasan", luas(p)], p.bilik ? ["Bilik tidur", p.bilik] : null,
      p.bilikAir ? ["Bilik air", p.bilikAir] : null, ["Pegangan", p.pegangan], ["Rujukan", p.id],
    ].filter(Boolean);
    const st = labelStatus(p);
    const tersedia = p.status !== "terjual";
    dlg.innerHTML = `
      <div class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-100 bg-white/90 px-4 py-3 backdrop-blur sm:px-6">
        <button data-tutup class="grid h-9 w-9 place-items-center rounded-full hover:bg-zinc-100" aria-label="Tutup">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg></button>
        <button data-kongsi class="rounded-full px-3 py-1.5 text-sm font-semibold underline-offset-4 hover:bg-zinc-100">Kongsi</button>
      </div>
      <div class="px-4 pb-8 pt-4 sm:px-6">
        <div class="relative aspect-[16/10] overflow-hidden rounded-2xl bg-zinc-100" id="d-utama">${media(p)}</div>
        ${n > 1 ? `<div class="no-scrollbar mt-3 flex gap-2 overflow-x-auto">${p.gambar.map((g, i) => `<button data-g="${i}" class="h-16 w-24 shrink-0 overflow-hidden rounded-xl ring-2 ${i ? "ring-transparent" : "ring-zinc-900"}"><img src="${esc(g)}" alt="" class="h-full w-full object-cover"></button>`).join("")}</div>` : ""}
        <div class="mt-6 grid gap-8 md:grid-cols-[1fr_300px]">
          <div>
            <div class="flex flex-wrap gap-1.5">
              <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold">${p.tujuan === "jual" ? "Dijual" : "Disewa"}</span>
              ${st ? `<span class="rounded-full bg-zinc-900 px-2.5 py-1 text-xs font-semibold text-white">${st}</span>` : ""}
            </div>
            <h2 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">${esc(p.tajuk)}</h2>
            <p class="mt-1 text-zinc-500">${esc(p.lokasi)}, ${esc(p.negeri)}</p>
            <dl class="mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-200 sm:grid-cols-3">
              ${fakta.map(([k, v]) => `<div class="bg-white p-4"><dt class="text-xs font-medium text-zinc-400">${k}</dt><dd class="mt-0.5 font-semibold">${esc(v)}</dd></div>`).join("")}
            </dl>
            <h3 class="mt-8 font-semibold">Tentang hartanah ini</h3>
            <p class="mt-2 leading-relaxed text-zinc-600">${esc(p.penerangan)}</p>
            ${(p.ciri || []).length ? `<h3 class="mt-8 font-semibold">Ciri-ciri</h3>
              <ul class="mt-3 grid gap-2 sm:grid-cols-2">${p.ciri.map((c) => `<li class="flex items-center gap-2 text-zinc-600"><svg class="h-4 w-4 text-brand-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 5 5 9-10"/></svg>${esc(c)}</li>`).join("")}</ul>` : ""}
            <h3 class="mt-8 font-semibold">Lokasi</h3>
            <div class="mt-3 aspect-[16/9] overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100">
              <iframe title="Peta lokasi" loading="lazy" class="h-full w-full" referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps?q=${encodeURIComponent(p.peta || `${p.lokasi}, ${p.negeri}, Malaysia`)}&z=14&output=embed"></iframe>
            </div>
            <p class="mt-2 text-xs text-zinc-400">Lokasi anggaran. Alamat tepat diberi semasa temujanji lawatan.</p>
          </div>
          <aside class="space-y-4 md:sticky md:top-20 md:self-start">
            <div class="rounded-2xl border border-zinc-200 p-5 shadow-[0_8px_30px_rgb(0,0,0,0.05)]">
              <p class="text-3xl font-extrabold tracking-tight">${hargaHtml(p, true)}</p>
              ${tersedia ? `
                <a target="_blank" rel="noopener" href="${waLink(`Salam, saya berminat dengan "${p.tajuk}" (Ruj: ${p.id}). Masih tersedia? ${location.href.split("#")[0]}#${p.id}`)}"
                   class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 py-3 font-semibold text-white transition hover:bg-brand-700">Tanya melalui WhatsApp</a>
                ${TETAPAN.telefon ? `<a href="tel:${esc(TETAPAN.telefon)}" class="mt-2 flex w-full items-center justify-center rounded-xl border border-zinc-200 py-3 font-semibold transition hover:bg-zinc-50">Telefon</a>` : ""}
                <a target="_blank" rel="noopener" href="${waLink(`Salam, saya ingin membuat temujanji untuk melihat "${p.tajuk}" (Ruj: ${p.id}).`)}"
                   class="mt-2 flex w-full items-center justify-center rounded-xl py-2 text-sm font-semibold text-zinc-500 underline-offset-4 hover:underline">Tempah lawatan</a>`
                : `<p class="mt-4 text-sm text-zinc-500">Hartanah ini sudah tidak tersedia.</p>`}
            </div>
            ${p.tujuan === "jual" && tersedia ? kalkulator(p) : ""}
          </aside>
        </div>
      </div>
      ${tersedia ? `<div class="sticky bottom-0 flex items-center justify-between gap-3 border-t border-zinc-100 bg-white/95 px-4 py-3 backdrop-blur md:hidden">
        <p class="text-lg font-extrabold">${hargaHtml(p)}</p>
        <a target="_blank" rel="noopener" href="${waLink(`Salam, saya berminat dengan "${p.tajuk}" (Ruj: ${p.id}). Masih tersedia? ${location.href.split("#")[0]}#${p.id}`)}"
           class="rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white">WhatsApp</a></div>` : ""}`;

    $$("[data-g]", dlg).forEach((b) => (b.onclick = () => {
      $("#d-utama", dlg).innerHTML = media(p, +b.dataset.g);
      $$("[data-g]", dlg).forEach((x) => x.classList.replace("ring-zinc-900", "ring-transparent"));
      b.classList.replace("ring-transparent", "ring-zinc-900");
    }));
    if ($("#k-hasil", dlg)) {
      const kira = () => ($("#k-hasil", dlg).textContent = rm(ansuran(p.harga, +$("#k-dep", dlg).value, +$("#k-kadar", dlg).value, +$("#k-thn", dlg).value || 1)) + " /bln");
      $$("#k-dep,#k-kadar,#k-thn", dlg).forEach((i) => i.addEventListener("input", kira));
      kira();
    }
    $("[data-tutup]", dlg).onclick = () => tutup();
    $("[data-kongsi]", dlg).onclick = async (e) => {
      const url = location.href;
      try {
        if (navigator.share) await navigator.share({ title: p.tajuk, url });
        else { await navigator.clipboard.writeText(url); e.target.textContent = "Pautan disalin ✓"; }
      } catch {}
    };
    if (!dlg.open) dlg.showModal();
    dlg.scrollTop = 0;
    document.body.style.overflow = "hidden";
  }
  function tutup() { history.pushState("", "", location.pathname + location.search); dlg.close(); }
  dlg.addEventListener("close", () => { document.body.style.overflow = ""; if (location.hash) history.pushState("", "", location.pathname + location.search); });
  dlg.addEventListener("click", (e) => { if (e.target === dlg) tutup(); });

  // Setiap hartanah ada pautan sendiri: laman.com/#H001
  const ikutHash = () => { const id = decodeURIComponent(location.hash.slice(1)); id ? buka(id) : dlg.open && dlg.close(); };
  window.addEventListener("hashchange", ikutHash);

  // ---------- Hartanah pilihan ----------
  const pilihan = SENARAI.filter((p) => p.pilihan && p.status !== "terjual").slice(0, 3);
  if (!pilihan.length) $("#pilihan-wrap").remove();
  else $("#pilihan").innerHTML = pilihan.map((p, i) => `
    <article data-id="${esc(p.id)}" tabindex="0" class="group relative cursor-pointer overflow-hidden rounded-3xl bg-zinc-100 ${i === 0 ? "aspect-[4/3] lg:row-span-2 lg:aspect-auto" : "aspect-[16/9] lg:aspect-auto lg:min-h-[240px]"}">
      <div class="absolute inset-0 transition duration-700 group-hover:scale-105">${media(p)}</div>
      <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
      <span class="absolute left-4 top-4 rounded-full bg-white/95 px-2.5 py-1 text-xs font-semibold">${p.tujuan === "jual" ? "Dijual" : "Disewa"}</span>
      <div class="absolute inset-x-0 bottom-0 p-5 text-white sm:p-6">
        <p class="text-sm text-white/80">${esc(p.lokasi)}, ${esc(p.negeri)}</p>
        <h3 class="mt-1 font-bold ${i === 0 ? "text-2xl sm:text-3xl" : "text-lg"}">${esc(p.tajuk)}</h3>
        <p class="mt-2 text-lg font-bold">${rm(p.harga)}${p.tujuan === "sewa" ? '<span class="text-sm font-medium text-white/70"> /bulan</span>' : ""}</p>
      </div>
    </article>`).join("");
  $("#pilihan")?.addEventListener("click", (e) => { const c = e.target.closest("[data-id]"); if (c) location.hash = c.dataset.id; });

  // ---------- Cari ikut kawasan ----------
  const kawasan = {};
  SENARAI.filter((p) => p.status !== "terjual").forEach((p) => {
    const k = `${p.lokasi}|${p.negeri}`;
    kawasan[k] = (kawasan[k] || 0) + 1;
  });
  const tonaKawasan = ["from-emerald-100 to-teal-50", "from-sky-100 to-indigo-50", "from-amber-100 to-orange-50", "from-violet-100 to-fuchsia-50", "from-rose-100 to-pink-50", "from-lime-100 to-green-50"];
  $("#kawasan-grid").innerHTML = Object.entries(kawasan).sort((a, b) => b[1] - a[1]).map(([k, n], i) => {
    const [lokasi, negeri] = k.split("|");
    return `<button data-kawasan="${esc(lokasi)}" class="group flex aspect-square flex-col justify-end rounded-3xl bg-gradient-to-br ${tonaKawasan[i % tonaKawasan.length]} p-5 text-left transition hover:-translate-y-1">
      <span class="text-lg font-bold">${esc(lokasi)}</span>
      <span class="text-sm text-zinc-500">${esc(negeri)}</span>
      <span class="mt-3 inline-flex w-fit rounded-full bg-white/80 px-2.5 py-1 text-xs font-semibold">${n} hartanah →</span>
    </button>`;
  }).join("");
  $("#kawasan-grid").addEventListener("click", (e) => {
    const b = e.target.closest("[data-kawasan]");
    if (!b) return;
    state.tujuan = "semua"; state.jenis = "";
    $("#negeri").value = ""; $("#harga").value = "";
    $("#q").value = b.dataset.kawasan;
    render();
    $("#senarai").scrollIntoView({ behavior: "smooth" });
  });

  // ---------- Borang hubungi -> WhatsApp ----------
  $("#borang").addEventListener("submit", (e) => {
    e.preventDefault();
    const f = new FormData(e.target);
    const msg = `Salam, saya ${f.get("nama")} (${f.get("telefon")}). Saya ingin ${f.get("minat")}.` + (f.get("mesej") ? `\n\n${f.get("mesej")}` : "");
    window.open(waLink(msg), "_blank", "noopener");
  });

  render();
  ikutHash();
})();
