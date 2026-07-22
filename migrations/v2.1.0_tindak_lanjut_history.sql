-- ============================================================================
-- Migration v2.1.0 - Tindak Lanjut History & Workflow
-- ============================================================================
-- Tanggal: 2026-07-21
-- Deskripsi:
--   1. Ubah ENUM status audit_tindak_lanjut ke VARCHAR
--   2. Mapping data lama ke format baru
--   3. Tabel history log untuk tracking seluruh perubahan status & upload
-- ============================================================================

-- ============================================================================
-- 1. UBAH ENUM STATUS TINDAK LANJUT MENJADI VARCHAR
-- ============================================================================
-- Kolom sebelumnya ENUM('OPEN','PROSES','SELESAI')
-- yang tidak bisa menampung nilai baru seperti 'Belum Sesuai', 'Sesuai', dll

ALTER TABLE audit_tindak_lanjut
    MODIFY COLUMN status VARCHAR(50) DEFAULT 'Proses';

-- ============================================================================
-- 2. UPDATE DATA LAMA: Mapping status lama ke baru
-- ============================================================================
-- Old: OPEN    -> New: Proses
-- Old: PROSES  -> New: Proses
-- Old: SELESAI -> New: Sesuai

UPDATE audit_tindak_lanjut
SET status = 'Proses'
WHERE status IN ('OPEN', 'PROSES');

UPDATE audit_tindak_lanjut
SET status = 'Sesuai'
WHERE status = 'SELESAI';

-- Map verifikasi_status lama ke status baru (jika status masih kosong/tidak berubah)
UPDATE audit_tindak_lanjut
SET status = 'Sesuai'
WHERE verifikasi_status = 'DITERIMA' AND status NOT IN ('Sesuai','Proses','Belum Sesuai','Belum Ditindak Lanjut','Tidak Dapat Ditindak Lanjut');

UPDATE audit_tindak_lanjut
SET status = 'Belum Sesuai'
WHERE verifikasi_status = 'DITOLAK' AND status NOT IN ('Sesuai','Proses','Belum Sesuai','Belum Ditindak Lanjut','Tidak Dapat Ditindak Lanjut');

-- ============================================================================
-- 3. HAPUS KOLOM LAMA YANG SUDAH TIDAK DIGUNAKAN
-- ============================================================================

ALTER TABLE audit_tindak_lanjut
    DROP COLUMN verifikasi_status,
    DROP COLUMN verifikasi_catatan,
    DROP COLUMN verifikasi_oleh,
    DROP COLUMN verifikasi_tanggal;

-- ============================================================================
-- 4. TABEL HISTORY LOG
-- ============================================================================

CREATE TABLE IF NOT EXISTS audit_tindak_lanjut_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tindak_lanjut_id INT NOT NULL,
    aksi VARCHAR(50) NOT NULL DEFAULT 'verifikasi' COMMENT 'buat, upload_bukti, verifikasi',
    status_lama VARCHAR(50) DEFAULT NULL,
    status_baru VARCHAR(50) DEFAULT NULL,
    file_bukti VARCHAR(255) DEFAULT NULL,
    file_bukti_original VARCHAR(255) DEFAULT NULL,
    hasil_tindak_lanjut TEXT DEFAULT NULL,
    catatan_spi TEXT DEFAULT NULL,
    keterangan TEXT DEFAULT NULL,
    dibuat_oleh INT DEFAULT NULL,
    dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tindak_lanjut_id) REFERENCES audit_tindak_lanjut(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 5. MIGRASI DATA LAMA KE LOG TABLE
-- ============================================================================
-- Untuk setiap tindak lanjut yang sudah ada, buat history awal

INSERT INTO audit_tindak_lanjut_log (tindak_lanjut_id, aksi, status_baru, dibuat_oleh, dibuat_pada)
SELECT id, 'buat', 'Proses', created_by, created_at
FROM audit_tindak_lanjut;

-- Untuk data yang memiliki bukti file, buat entry upload
INSERT INTO audit_tindak_lanjut_log (tindak_lanjut_id, aksi, file_bukti, hasil_tindak_lanjut, dibuat_oleh, dibuat_pada)
SELECT id, 'upload_bukti', bukti_file, hasil_tindak_lanjut, created_by, created_at
FROM audit_tindak_lanjut
WHERE bukti_file IS NOT NULL AND bukti_file != '';

-- Untuk data yang statusnya sudah berubah dari Proses, buat entry verifikasi
INSERT INTO audit_tindak_lanjut_log (tindak_lanjut_id, aksi, status_lama, status_baru, catatan_spi, dibuat_oleh, dibuat_pada)
SELECT id, 'verifikasi', 'Proses', status, catatan_spi, created_by, created_at
FROM audit_tindak_lanjut
WHERE status != 'Proses';
