-- ============================================================================
-- Migration v2.5.0 - Unit pada TL LHP
-- ============================================================================
-- Tanggal: 2026-09-22
-- Deskripsi:
--   1. Tambah kolom unit_id pada tabel lhp agar TL LHP (BPK/BPKP/KAP)
--      dapat difilter berdasarkan unit di Dashboard Monitoring SIA.
--   2. Data lama dibiarkan NULL (belum diketahui unitnya) dan ditampilkan "-".
-- ============================================================================

ALTER TABLE lhp
    ADD COLUMN unit_id INT NULL AFTER nilai;

ALTER TABLE lhp
    ADD CONSTRAINT fk_lhp_unit
    FOREIGN KEY (unit_id) REFERENCES unit_kerja(id) ON DELETE SET NULL;