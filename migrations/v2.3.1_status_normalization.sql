-- ============================================================================
-- Migration v2.3.1 - Normalisasi status tindak lanjut ke camelCase
-- ============================================================================
-- Tanggal: 2026-09-17
-- Deskripsi:
--   Jaring pengaman: jika terdapat data lama dengan status uppercase
--   (SESUAI / SELESAI / PROSES / BELUM SESUAI / dst) pada kolom status
--   modul tindak lanjut, normalisasi ke format camelCase.
--   Tidak menyentuh status domain lain: TW1..TW4, OPEN, RENCANA, BERJALAN,
--   SELESAI pada modul PKPT / pemeriksaan / temuan.
-- ============================================================================

UPDATE audit_tindak_lanjut
SET status = CASE
    WHEN status = 'SESUAI' THEN 'Sesuai'
    WHEN status = 'SELESAI' THEN 'Sesuai'
    WHEN status = 'PROSES' THEN 'Proses'
    WHEN status = 'BELUM SESUAI' THEN 'Belum Sesuai'
    WHEN status = 'BELUM DITINDAK LANJUT' THEN 'Belum Ditindak Lanjut'
    WHEN status = 'TIDAK DAPAT DITINDAK LANJUT' THEN 'Tidak Dapat Ditindak Lanjut'
    ELSE status
END;

UPDATE audit_tindak_lanjut_log
SET status_baru = CASE
    WHEN status_baru = 'SESUAI' THEN 'Sesuai'
    WHEN status_baru = 'SELESAI' THEN 'Sesuai'
    WHEN status_baru = 'PROSES' THEN 'Proses'
    WHEN status_baru = 'BELUM SESUAI' THEN 'Belum Sesuai'
    WHEN status_baru = 'BELUM DITINDAK LANJUT' THEN 'Belum Ditindak Lanjut'
    WHEN status_baru = 'TIDAK DAPAT DITINDAK LANJUT' THEN 'Tidak Dapat Ditindak Lanjut'
    ELSE status_baru
END;

UPDATE audit_tindak_lanjut_log
SET status_lama = CASE
    WHEN status_lama = 'SESUAI' THEN 'Sesuai'
    WHEN status_lama = 'SELESAI' THEN 'Sesuai'
    WHEN status_lama = 'PROSES' THEN 'Proses'
    WHEN status_lama = 'BELUM SESUAI' THEN 'Belum Sesuai'
    WHEN status_lama = 'BELUM DITINDAK LANJUT' THEN 'Belum Ditindak Lanjut'
    WHEN status_lama = 'TIDAK DAPAT DITINDAK LANJUT' THEN 'Tidak Dapat Ditindak Lanjut'
    ELSE status_lama
END;