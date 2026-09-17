-- ============================================================================
-- Migration v2.3.0 - Role KOMISARIS
-- ============================================================================
-- Tanggal: 2026-09-17
-- Deskripsi:
--   1. Tambah role KOMISARIS pada ENUM users.role
--      (akses menu setara DIREKSI: Dashboard Monitoring SIA + Export Excel)
-- ============================================================================

ALTER TABLE users
    MODIFY COLUMN role ENUM('ADMIN','KEPALA_SIA','AUDITOR','AUDITEE','DIREKSI','KOMISARIS') NULL DEFAULT NULL;