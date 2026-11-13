// ============================================================
//  SENARAI HARTANAH — edit fail ini untuk tambah / buang iklan
// ============================================================
//  Setiap iklan ialah satu { ... } dalam senarai di bawah.
//  - tujuan : "jual" atau "sewa"
//  - jenis  : "Rumah Teres", "Banglo", "Semi-D", "Kondominium",
//             "Apartment", "Tanah", "Kedai" (atau apa-apa)
//  - harga  : nombor sahaja (RM). Untuk sewa, harga sebulan.
//  - gambar : senarai link gambar, contoh "gambar/rumah1.jpg".
//             Biarkan [] kalau belum ada gambar.
//  - status : "tersedia", "tempahan" atau "terjual"
// ============================================================

const TETAPAN = {
  namaSyarikat: "Hartanah Kita",
  slogan: "Cari rumah, tanah & kedai idaman anda",
  whatsapp: "60123456789", // nombor WhatsApp, format 60xxxxxxxxx tanpa + atau -
  emel: "",
};

const SENARAI = [
  {
    id: "H001",
    tajuk: "Rumah Teres 2 Tingkat, Taman Aman",
    tujuan: "jual",
    jenis: "Rumah Teres",
    harga: 450000,
    lokasi: "Kuantan",
    negeri: "Pahang",
    bilik: 4, bilikAir: 3, keluasan: 1650, unit: "kp",
    pegangan: "Freehold",
    status: "tersedia",
    gambar: [],
    penerangan:
      "Rumah teres 2 tingkat lot tepi, telah diubah suai. Dapur extend, auto gate, grill penuh. Berhampiran sekolah, masjid dan pasar raya.",
    ciri: ["Lot tepi", "Auto gate", "Dapur extend", "Dekat sekolah"],
  },
  {
    id: "H002",
    tajuk: "Kondominium 3 Bilik, Fully Furnished",
    tujuan: "sewa",
    jenis: "Kondominium",
    harga: 1800,
    lokasi: "Cyberjaya",
    negeri: "Selangor",
    bilik: 3, bilikAir: 2, keluasan: 1100, unit: "kp",
    pegangan: "Freehold",
    status: "tersedia",
    gambar: [],
    penerangan:
      "Unit tingkat tinggi dengan pemandangan tasik. Lengkap perabot, aircond semua bilik, mesin basuh dan peti sejuk. 2 petak parkir.",
    ciri: ["Fully furnished", "Kolam renang", "Gym", "Keselamatan 24 jam", "2 parkir"],
  },
  {
    id: "H003",
    tajuk: "Banglo Setingkat Atas Tanah 1/2 Ekar",
    tujuan: "jual",
    jenis: "Banglo",
    harga: 780000,
    lokasi: "Temerloh",
    negeri: "Pahang",
    bilik: 5, bilikAir: 4, keluasan: 21780, unit: "kp",
    pegangan: "Freehold",
    status: "tempahan",
    gambar: [],
    penerangan:
      "Banglo luas dengan halaman besar, sesuai untuk keluarga besar. Ada pokok buah-buahan matang. Akses jalan tar terus ke pintu pagar.",
    ciri: ["Tanah luas", "Pokok buah", "Garaj 3 kereta"],
  },
  {
    id: "H004",
    tajuk: "Tanah Pertanian 2 Ekar Tepi Jalan",
    tujuan: "jual",
    jenis: "Tanah",
    harga: 220000,
    lokasi: "Bentong",
    negeri: "Pahang",
    bilik: 0, bilikAir: 0, keluasan: 2, unit: "ekar",
    pegangan: "Geran Pertanian",
    status: "tersedia",
    gambar: [],
    penerangan:
      "Tanah rata, sesuai untuk dusun durian atau homestay. Bekalan elektrik dan air berdekatan. Geran individu.",
    ciri: ["Tepi jalan", "Tanah rata", "Geran individu"],
  },
  {
    id: "H005",
    tajuk: "Lot Kedai 2 Tingkat, Pusat Bandar",
    tujuan: "sewa",
    jenis: "Kedai",
    harga: 3500,
    lokasi: "Kota Bharu",
    negeri: "Kelantan",
    bilik: 0, bilikAir: 2, keluasan: 2800, unit: "kp",
    pegangan: "Leasehold",
    status: "tersedia",
    gambar: [],
    penerangan:
      "Lot kedai di kawasan tumpuan ramai, sesuai untuk kafe, klinik atau pejabat. Tingkat atas boleh dijadikan stor atau pejabat.",
    ciri: ["Kawasan sibuk", "Parkir awam", "Sesuai kafe/klinik"],
  },
  {
    id: "H006",
    tajuk: "Semi-D 2 Tingkat, Taman Baru",
    tujuan: "jual",
    jenis: "Semi-D",
    harga: 680000,
    lokasi: "Seremban",
    negeri: "Negeri Sembilan",
    bilik: 5, bilikAir: 4, keluasan: 2400, unit: "kp",
    pegangan: "Freehold",
    status: "terjual",
    gambar: [],
    penerangan:
      "Semi-D dalam kawasan berpagar dan berpengawal. Siling tinggi, lantai jubin penuh. Dekat lebuh raya.",
    ciri: ["Gated & guarded", "Dekat lebuh raya", "Siling tinggi"],
  },
  {
    id: "H007",
    tajuk: "Apartment 3 Bilik Untuk Disewa",
    tujuan: "sewa",
    jenis: "Apartment",
    harga: 900,
    lokasi: "Kuantan",
    negeri: "Pahang",
    bilik: 3, bilikAir: 2, keluasan: 850, unit: "kp",
    pegangan: "Leasehold",
    status: "tersedia",
    gambar: [],
    penerangan:
      "Apartment separa perabot, tingkat 3 dengan lif. Berhampiran universiti dan hospital. Sesuai pelajar atau keluarga kecil.",
    ciri: ["Partially furnished", "Ada lif", "Dekat universiti"],
  },
];
