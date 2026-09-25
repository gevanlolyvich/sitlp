-- ============================================================================
-- Migration v2.6.0 - Role KOMITE_AUDIT
-- ============================================================================
-- Tanggal: 2026-09-25
-- Deskripsi:
--   1. Tambah role KOMITE_AUDIT pada ENUM users.role
--      (akses menu setara DIREKSI/KOMISARIS: Dashboard Monitoring SIA + Export Excel)
-- ============================================================================

ALTER TABLE users
    MODIFY COLUMN role ENUM('ADMIN','KEPALA_SIA','AUDITOR','AUDITEE','DIREKSI','KOMISARIS','KOMITE_AUDIT') NULL DEFAULT NULL;