-- ============================================================================
-- Migration v2.3.2 - Normalisasi nilai status/risiko ke Title Case
-- ============================================================================
-- Tanggal: 2026-09-17
-- Deskripsi:
--   Ubah seluruh nilai all-caps menjadi Title Case (huruf depan besar, sisanya
--   kecil) pada kolom ENUM status / level_risiko / tingkat_risiko.
--   Strategi 3 langkah untuk kolom ENUM:
--     1. Perluas daftar ENUM (nilai lama + nilai Title Case baru)
--     2. UPDATE data lama ke Title Case
--     3. Rampingkan ENUM hanya berisi nilai Title Case
--   - TW1..TW4 pada triwulan TIDAK diubah.
--   - Role user (ADMIN, KEPALA_SIA, dst) TIDAK diubah.
--   - Tabel legacy tidak terpakai (temuan, disposisi_temuan, verifikasi_spi)
--     TIDAK diubah.
-- ============================================================================

-- ---------------------------------------------------------------
-- Step 1: Perluas ENUM (nilai lama + nilai baru Title Case)
-- ---------------------------------------------------------------
ALTER TABLE audit_program
    MODIFY COLUMN status
        ENUM('RENCANA','BERJALAN','SELESAI','Rencana','Berjalan','Selesai')
        NULL DEFAULT 'Rencana',
    MODIFY COLUMN level_risiko
        ENUM('RENDAH','SEDANG','TINGGI','Rendah','Sedang','Tinggi')
        NULL DEFAULT 'Sedang';

ALTER TABLE audit_pemeriksaan
    MODIFY COLUMN status
        ENUM('DRAFT','BERJALAN','SELESAI','Draft','Berjalan','Selesai')
        NULL DEFAULT 'Draft';

ALTER TABLE audit_temuan
    MODIFY COLUMN status
        ENUM('OPEN','PROSES','CLOSED','Open','Proses','Closed')
        NULL DEFAULT 'Open',
    MODIFY COLUMN tingkat_risiko
        ENUM('RENDAH','SEDANG','TINGGI','Rendah','Sedang','Tinggi')
        NULL DEFAULT 'Sedang';

-- ---------------------------------------------------------------
-- Step 2: Normalisasi data lama
-- ---------------------------------------------------------------
UPDATE audit_program
SET
    status = CASE status
        WHEN 'RENCANA'   THEN 'Rencana'
        WHEN 'BERJALAN'  THEN 'Berjalan'
        WHEN 'SELESAI'   THEN 'Selesai'
        ELSE status
    END,
    level_risiko = CASE level_risiko
        WHEN 'RENDAH' THEN 'Rendah'
        WHEN 'SEDANG' THEN 'Sedang'
        WHEN 'TINGGI' THEN 'Tinggi'
        ELSE level_risiko
    END,
    jenis_audit = CASE jenis_audit
        WHEN 'OPERASIONAL'                    THEN 'Operasional'
        WHEN 'KEUANGAN'                       THEN 'Keuangan'
        WHEN 'KEPATUHAN'                      THEN 'Kepatuhan'
        WHEN 'VERIFIKASI'                     THEN 'Verifikasi'
        WHEN 'INVESTIGASI'                    THEN 'Investigasi'
        WHEN 'KHUSUS'                         THEN 'Khusus'
        WHEN 'OPERASIONAL|KEUANGAN|KEPATUHAN' THEN 'Operasional|Keuangan|Kepatuhan'
        ELSE jenis_audit
    END;

UPDATE audit_pemeriksaan
SET
    status = CASE status
        WHEN 'DRAFT'     THEN 'Draft'
        WHEN 'BERJALAN'  THEN 'Berjalan'
        WHEN 'SELESAI'   THEN 'Selesai'
        ELSE status
    END,
    jenis_audit = CASE jenis_audit
        WHEN 'OPERASIONAL'                    THEN 'Operasional'
        WHEN 'KEUANGAN'                       THEN 'Keuangan'
        WHEN 'KEPATUHAN'                      THEN 'Kepatuhan'
        WHEN 'VERIFIKASI'                     THEN 'Verifikasi'
        WHEN 'INVESTIGASI'                    THEN 'Investigasi'
        WHEN 'KHUSUS'                         THEN 'Khusus'
        WHEN 'OPERASIONAL|KEUANGAN|KEPATUHAN' THEN 'Operasional|Keuangan|Kepatuhan'
        ELSE jenis_audit
    END;

UPDATE audit_temuan
SET
    status = CASE status
        WHEN 'OPEN'    THEN 'Open'
        WHEN 'PROSES'  THEN 'Proses'
        WHEN 'CLOSED'  THEN 'Closed'
        ELSE status
    END,
    tingkat_risiko = CASE tingkat_risiko
        WHEN 'RENDAH' THEN 'Rendah'
        WHEN 'SEDANG' THEN 'Sedang'
        WHEN 'TINGGI' THEN 'Tinggi'
        ELSE tingkat_risiko
    END;

-- ---------------------------------------------------------------
-- Step 3: Rampingkan ENUM hanya nilai Title Case
-- ---------------------------------------------------------------
ALTER TABLE audit_program
    MODIFY COLUMN status
        ENUM('Rencana','Berjalan','Selesai')
        NULL DEFAULT 'Rencana',
    MODIFY COLUMN level_risiko
        ENUM('Rendah','Sedang','Tinggi')
        NULL DEFAULT 'Sedang';

ALTER TABLE audit_pemeriksaan
    MODIFY COLUMN status
        ENUM('Draft','Berjalan','Selesai')
        NULL DEFAULT 'Draft';

ALTER TABLE audit_temuan
    MODIFY COLUMN status
        ENUM('Open','Proses','Closed')
        NULL DEFAULT 'Open',
    MODIFY COLUMN tingkat_risiko
        ENUM('Rendah','Sedang','Tinggi')
        NULL DEFAULT 'Sedang';