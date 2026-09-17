-- ============================================================================
-- Migration v2.2.0 - Nilai Penyerahan / Penyetoran Uang
-- ============================================================================
-- Tanggal: 2026-09-15
-- Deskripsi:
--   1. Tambah kolom nilai_penyerahan pada audit_tindak_lanjut
--      (uang yang disetor / nilai aset yang diserahkan ke kas negara/daerah)
--   2. Tambah kolom nilai_penyerahan pada audit_tindak_lanjut_log
--      (riwayat nilai saat verifikasi)
-- ============================================================================

ALTER TABLE audit_tindak_lanjut
    ADD COLUMN nilai_penyerahan DECIMAL(18,2) DEFAULT NULL
    COMMENT 'Nilai penyerahan aset atau penyetoran uang ke kas negara/daerah'
    AFTER catatan_spi;

ALTER TABLE audit_tindak_lanjut_log
    ADD COLUMN nilai_penyerahan DECIMAL(18,2) DEFAULT NULL
    COMMENT 'Nilai penyerahan aset atau penyetoran uang saat verifikasi'
    AFTER catatan_spi;

-- ============================================================================
-- Data lama: status Sesuai tanpa nilai tetap NULL (dianggap 0 pada export)
-- ============================================================================