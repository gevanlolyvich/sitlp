-- ============================================================================
-- Migration v2.0.0 - Audit System Enhancements
-- ============================================================================
-- Tanggal: 2026-07-20
-- Deskripsi: Perubahan besar pada sistem Audit meliputi:
--   1. Jenis Audit baru (Title Case, tambah Verifikasi)
--   2. Estimasi Hari & Tanggal Selesai otomatis
--   3. Status Temuan hanya OPEN
--   4. Status Tindak Lanjut baru (Proses, Sesuai, Belum Sesuai, dll)
--   5. Catatan SPI
--   6. Verifikasi dihapus, diganti status tindak lanjut
-- ============================================================================

-- ============================================================================
-- 1. UBAH ENUM JENIS AUDIT MENJADI VARCHAR
-- ============================================================================
-- Kolom sebelumnya ENUM('OPERASIONAL','KEUANGAN','KEPATUHAN','INVESTIGASI','KHUSUS')
-- yang tidak bisa menampung nilai "Operasional|Keuangan|Kepatuhan"

ALTER TABLE audit_program
    MODIFY COLUMN jenis_audit VARCHAR(100) DEFAULT 'Operasional|Keuangan|Kepatuhan';

-- ============================================================================
-- 2. TAMBAH KOLOM estimasi_hari DAN catatan_spi
-- ============================================================================

ALTER TABLE audit_pemeriksaan
    ADD COLUMN estimasi_hari INT DEFAULT 14 AFTER tanggal_selesai;

ALTER TABLE audit_tindak_lanjut
    ADD COLUMN catatan_spi TEXT DEFAULT NULL AFTER verifikasi_catatan;

-- ============================================================================
-- 3. UPDATE DATA LAMA: Set estimasi_hari berdasarkan selisih tanggal
-- ============================================================================

UPDATE audit_pemeriksaan
SET estimasi_hari = DATEDIFF(tanggal_selesai, tanggal_mulai)
WHERE estimasi_hari IS NULL
  AND tanggal_mulai IS NOT NULL
  AND tanggal_selesai IS NOT NULL;

-- ============================================================================
-- 4. UPDATE DATA TINDAK LANJUT: Mapping status lama ke baru
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

-- ============================================================================
-- 5. UPDATE VERIFIKASI STATUS: Mapping ke status baru
-- ============================================================================
-- DITERIMA -> Sesuai
-- DITOLAK  -> Belum Sesuai
-- BELUM    -> Proses

UPDATE audit_tindak_lanjut
SET status = 'Sesuai'
WHERE verifikasi_status = 'DITERIMA';

UPDATE audit_tindak_lanjut
SET status = 'Belum Sesuai'
WHERE verifikasi_status = 'DITOLAK';

-- ============================================================================
-- 6. UPDATE DATA TIM AUDIT: Peran lama ke baru
-- ============================================================================
-- Kolom sebelumnya ENUM('PENGENDALI','KETUA','ANGGOTA') 

ALTER TABLE audit_tim
    MODIFY COLUMN peran VARCHAR(50) DEFAULT 'Anggota';

UPDATE audit_tim SET peran = 'Anggota' WHERE peran = 'ANGGOTA';
UPDATE audit_tim SET peran = 'Pengendali' WHERE peran = 'PENGENDALI';
UPDATE audit_tim SET peran = 'Ketua' WHERE peran = 'KETUA';

-- ============================================================================
-- 8. UPDATE DATA TEMUAN: Status lama ke baru
-- ============================================================================
-- PROSES -> OPEN (karena hanya OPEN yang valid)
-- CLOSED -> OPEN

UPDATE audit_temuan
SET status = 'OPEN'
WHERE status IN ('PROSES', 'CLOSED');
