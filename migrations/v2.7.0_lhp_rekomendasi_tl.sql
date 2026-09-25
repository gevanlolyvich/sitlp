-- ============================================================================
-- Migration v2.7.0 - TL LHP Multiple: Rekomendasi, TL, Unit, Bukti
-- ============================================================================
-- Tanggal: 2026-09-25
-- Deskripsi:
--   1. Hapus semua data lama lhp (sesuai keputusan: diinput ulang).
--   2. Hapus kolom lama yang tidak terpakai lagi dari tabel lhp:
--      unit_id, jml_rekomendasi, uraian_rekomendasi, jml_tl, uraian_tl,
--      bukti_file, hasil_sesuai, hasil_belum_sesuai, hasil_belum_tl,
--      hasil_tidak_tl. (hasil pemantauan kini dihitung otomatis dari status TL)
--   3. Buat tabel anak:
--      - lhp_rekomendasi : beberapa rekomendasi per LHP
--      - lhp_tl          : beberapa tindak lanjut per rekomendasi (dengan status)
--      - lhp_tl_bukti    : beberapa file bukti per tindak lanjut (opsional)
--      - lhp_unit        : beberapa unit/entitas diperiksa per LHP
-- ============================================================================

DELETE FROM lhp;

-- DROP FOREIGN KEY dipisah: MariaDB gagal bila digabung dengan klausa lain
ALTER TABLE lhp
    DROP FOREIGN KEY fk_lhp_unit;

ALTER TABLE lhp
    DROP COLUMN unit_id,
    DROP COLUMN jml_rekomendasi,
    DROP COLUMN uraian_rekomendasi,
    DROP COLUMN jml_tl,
    DROP COLUMN uraian_tl,
    DROP COLUMN bukti_file,
    DROP COLUMN hasil_sesuai,
    DROP COLUMN hasil_belum_sesuai,
    DROP COLUMN hasil_belum_tl,
    DROP COLUMN hasil_tidak_tl;

CREATE TABLE lhp_rekomendasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lhp_id INT NOT NULL,
    no INT NOT NULL DEFAULT 1,
    uraian TEXT NOT NULL,
    CONSTRAINT fk_rek_lhp FOREIGN KEY (lhp_id) REFERENCES lhp(id) ON DELETE CASCADE
);

CREATE TABLE lhp_tl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rekomendasi_id INT NOT NULL,
    no INT NOT NULL DEFAULT 1,
    uraian TEXT NOT NULL,
    status VARCHAR(40) NOT NULL,
    CONSTRAINT fk_tl_rek FOREIGN KEY (rekomendasi_id) REFERENCES lhp_rekomendasi(id) ON DELETE CASCADE
);

CREATE TABLE lhp_tl_bukti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tl_id INT NOT NULL,
    file VARCHAR(255) NOT NULL,
    CONSTRAINT fk_bukti_tl FOREIGN KEY (tl_id) REFERENCES lhp_tl(id) ON DELETE CASCADE
);

CREATE TABLE lhp_unit (
    lhp_id INT NOT NULL,
    unit_id INT NOT NULL,
    PRIMARY KEY (lhp_id, unit_id),
    CONSTRAINT fk_lhpunit_lhp FOREIGN KEY (lhp_id) REFERENCES lhp(id) ON DELETE CASCADE,
    CONSTRAINT fk_lhpunit_unit FOREIGN KEY (unit_id) REFERENCES unit_kerja(id) ON DELETE CASCADE
);