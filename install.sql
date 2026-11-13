-- ============================================================
--  Hartanah Marketplace — skema pangkalan data MySQL / MariaDB
--  Import fail ini melalui phpMyAdmin (tab "Import").
-- ============================================================
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama          VARCHAR(100) NOT NULL,
  emel          VARCHAR(190) NOT NULL UNIQUE,
  kata_laluan   VARCHAR(255) NOT NULL,          -- password_hash()
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  kunci  VARCHAR(50) PRIMARY KEY,
  nilai  TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS properties (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ruj         VARCHAR(20)  NOT NULL UNIQUE,      -- contoh H001
  tajuk       VARCHAR(200) NOT NULL,
  tujuan      ENUM('jual','sewa') NOT NULL,
  jenis       VARCHAR(50)  NOT NULL,
  harga       DECIMAL(12,2) NOT NULL,
  lokasi      VARCHAR(100) NOT NULL,
  negeri      VARCHAR(50)  NOT NULL,
  bilik       TINYINT UNSIGNED NOT NULL DEFAULT 0,
  bilik_air   TINYINT UNSIGNED NOT NULL DEFAULT 0,
  keluasan    DECIMAL(12,2) NOT NULL DEFAULT 0,
  unit        ENUM('kp','ekar') NOT NULL DEFAULT 'kp',
  pegangan    VARCHAR(50)  NOT NULL DEFAULT '',
  status      ENUM('tersedia','tempahan','terjual') NOT NULL DEFAULT 'tersedia',
  pilihan     TINYINT(1)   NOT NULL DEFAULT 0,
  peta        VARCHAR(200) NOT NULL DEFAULT '',
  penerangan  TEXT NOT NULL,
  ciri        TEXT NOT NULL,                     -- satu ciri setiap baris
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (status), INDEX (tujuan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS property_images (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  property_id  INT UNSIGNED NOT NULL,
  fail         VARCHAR(255) NOT NULL,            -- nama fail dalam folder uploads/
  susunan      INT NOT NULL DEFAULT 0,
  FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO settings (kunci, nilai) VALUES
  ('nama_syarikat', 'Hartanah Kita'),
  ('slogan', 'Cari rumah idaman anda.'),
  ('subslogan', 'Rumah, tanah dan kedai untuk dijual atau disewa — urusan terus dengan pemilik, tanpa orang tengah.'),
  ('whatsapp', '60123456789'),
  ('telefon', '+60123456789');
