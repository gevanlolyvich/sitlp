-- ============================================================================
-- Migration v2.4.0 - updated_at pada Tindak Lanjut
-- ============================================================================
-- Tanggal: 2026-09-18
-- Deskripsi:
--   1. Tambah kolom updated_at yang terisi otomatis saat baris TL berubah
--      (perubahan status, edit, upload bukti) agar daftar TL dapat diurutkan
--      "terbaru berubah di atas" untuk semua role.
--   2. Backfill data lama dari created_at agar urutan tetap masuk akal.
--   Catatan: membutuhkan MySQL >= 5.6.5 (DATETIME auto-update).
-- ============================================================================

ALTER TABLE audit_tindak_lanjut
    ADD COLUMN updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

UPDATE audit_tindak_lanjut
SET updated_at = created_at
WHERE updated_at IS NULL;