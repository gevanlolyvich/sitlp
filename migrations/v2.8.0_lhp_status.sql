-- ============================================================================
-- Migration v2.8.0 - Status TL LHP disamakan dengan status tindak lanjut SPI
-- ============================================================================
-- Tanggal: 2026-09-25
-- Deskripsi:
--   1. Tambah status 'Proses' (tidak butuh skema berubah, kolom status VARCHAR).
--   2. Samakan penamaan status TL LHP dengan SPI (memakai spasi):
--      - 'Belum Ditindaklanjuti'      -> 'Belum Ditindak Lanjut'
--      - 'Tidak Dapat Ditindaklanjuti' -> 'Tidak Dapat Ditindak Lanjut'
-- ============================================================================

UPDATE lhp_tl SET status = 'Belum Ditindak Lanjut' WHERE status = 'Belum Ditindaklanjuti';
UPDATE lhp_tl SET status = 'Tidak Dapat Ditindak Lanjut' WHERE status = 'Tidak Dapat Ditindaklanjuti';