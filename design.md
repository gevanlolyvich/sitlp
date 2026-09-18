# SI SIA JAKTOUR — Design System & UI Redesign Specification

> Versi 1.0 · Aplikasi internal Sistem Informasi Satuan Internal Audit (SI SIA JAKTOUR)  
> PT Jakarta Tourisindo / Jakarta Experience Board (JXB)  
> Stack: PHP Native + Bootstrap + AdminLTE + Font Awesome

---

## 1. Tujuan

Memodernisasi antarmuka SI SIA JAKTOUR menjadi aplikasi audit internal yang terasa dibuat khusus untuk JXB: profesional, tenang, mudah dipindai, dan nyaman dipakai dalam waktu lama. Redesign berfokus pada lapisan visual dan pengalaman pengguna di atas stack yang ada.

Tidak mengubah database, query, business logic, autentikasi, otorisasi, routing, API, alur CRUD, validasi, maupun permission. Perubahan HTML hanya boleh dilakukan bila diperlukan untuk struktur visual atau aksesibilitas, tanpa mengubah `name`, `id`, action form, event, atau kontrak JavaScript yang telah ada.

## 2. Arah Visual dan Prinsip Desain

**Modern enterprise, clean corporate, data-oriented.** Tampilan harus terasa kredibel untuk pengawasan, pemeriksaan, dan pelaporan—bukan template dashboard generik.

1. **Jelas lebih dahulu.** Prioritaskan status, angka, tanggal, penanggung jawab, dan tindakan berikutnya.
2. **Biru memimpin.** Biru JXB adalah identitas dan aksi utama. Merah dan kuning hanya menandai perhatian, risiko, atau status tertentu—kecuali garis aksen tri-warna brand pada shell (topbar, login, beranda) yang memakai warna logo.
3. **Konsisten, bukan dekoratif.** Komponen dengan fungsi sama harus tampil sama di seluruh modul.
4. **Kepadatan terukur.** Tabel dan daftar boleh padat, tetapi tinggi baris, kolom, dan filter harus tetap mudah dibaca.
5. **Whitespace terencana.** Gunakan ruang kosong untuk memisahkan kelompok informasi, bukan banyak garis dan warna.
6. **Ringan untuk diimplementasikan.** Gunakan CSS override, utility Bootstrap, dan komponen AdminLTE yang sudah tersedia; hindari library UI baru kecuali benar-benar diperlukan.
7. **Aksesibel dan responsif.** Informasi penting tidak boleh hanya disampaikan oleh warna atau hanya dapat diakses dari desktop.

Hindari gradient mencolok, glassmorphism, ilustrasi dekoratif berlebihan, shadow gelap, border tebal, semua elemen berbentuk pil, dan penggunaan tiga warna brand sekaligus pada satu area.

## 3. Brand dan Sistem Warna

### 3.1 Peran warna

| Token              |       HEX | Peran utama           | Aturan penggunaan                                                            |
| ------------------ | --------: | --------------------- | ---------------------------------------------------------------------------- |
| `--jxb-blue-700`   | `#063F7A` | primary dark / header | Logo area, state aktif gelap, teks pada area terang bila perlu               |
| `--jxb-blue-600`   | `#075AA8` | primary               | Tombol utama, link, menu aktif, fokus                                        |
| `--jxb-blue-500`   | `#1674C6` | primary hover         | Hover dan chart series utama                                                 |
| `--jxb-blue-100`   | `#E8F2FC` | primary soft          | Latar menu aktif, info panel, badge informasi                                |
| `--jxb-red-600`    | `#C53030` | danger / critical     | Error, overdue, tindakan destruktif                                          |
| `--jxb-red-100`    | `#FDECEC` | danger soft           | Latar badge/pesan critical                                                   |
| `--jxb-yellow-600` | `#A66A00` | warning text          | Teks warning di atas latar terang; jangan gunakan kuning terang sebagai teks |
| `--jxb-yellow-400` | `#F4B400` | warning accent        | Ikon/perhatian terbatas, chart aksen                                         |
| `--jxb-yellow-100` | `#FFF6D8` | warning soft          | Latar warning/pending                                                        |
| `--success-600`    | `#18794E` | success               | Selesai, sesuai, tervalidasi                                                 |
| `--success-100`    | `#E8F7EE` | success soft          | Latar status sukses                                                          |
| `--neutral-900`    | `#172033` | teks utama            | Judul dan data penting                                                       |
| `--neutral-700`    | `#46526A` | teks sekunder         | Label, metadata                                                              |
| `--neutral-500`    | `#6E7B91` | teks tersier          | Placeholder, metadata lemah                                                  |
| `--neutral-300`    | `#D8DEE8` | border                | Batas halus komponen                                                         |
| `--neutral-100`    | `#F1F4F8` | surface muted         | Latar kontrol atau header tabel                                              |
| `--canvas`         | `#F6F8FB` | background aplikasi   | Latar halaman                                                                |
| `--surface`        | `#FFFFFF` | surface               | Card, tabel, modal                                                           |
| `--jxb-logo-blue`  | `#3C62AE` | aksen brand (logo)    | Segmen garis aksen brand; bukan untuk teks kecil                            |
| `--jxb-logo-red`   | `#EE3A27` | aksen brand (logo)    | Segmen garis aksen brand; merah status tetap `--jxb-red-600`                |
| `--jxb-logo-yellow`| `#FCB42C` | aksen brand (logo)    | Segmen garis aksen brand & indikator menu aktif sidebar                     |

Rasio praktis: sekitar 70% neutral/surface, 20% biru (termasuk sidebar biru gelap), maksimal 10% merah-kuning-hijau (termasuk garis aksen brand). Biru adalah satu-satunya warna untuk CTA utama. Merah tidak dipakai untuk tombol biasa; kuning bukan pengganti primary.

**Shell biru gelap.** Sidebar memakai biru JXB gelap `--jxb-blue-700` dengan teks putih agar aplikasi tidak terasa "kebanyakan putih", sementara area kerja (card, tabel, form) tetap putih agar data audit tetap terbaca. Tiga warna logo (`--jxb-logo-blue`, `--jxb-logo-red`, `--jxb-logo-yellow`) hanya muncul sebagai garis aksen tri-warna 3–4px pada topbar, card login, dan card beranda—bukan sebagai latar area besar.

### 3.2 Status semantik

| Status             | Warna                          | Contoh                          |
| ------------------ | ------------------------------ | ------------------------------- |
| Informasi / proses | biru soft + teks biru 700      | Berjalan, Ditugaskan            |
| Perlu perhatian    | kuning soft + teks kuning 600  | Menunggu, Mendekati jatuh tempo |
| Risiko / terlambat | merah soft + teks merah 600    | Terlambat, Ditolak, Kritis      |
| Selesai / valid    | hijau soft + teks hijau 600    | Selesai, Ditindaklanjuti        |
| Netral             | neutral 100 + teks neutral 700 | Draft, Nonaktif                 |

Jangan gunakan kombinasi `#F4B400` dengan teks putih. Untuk kontras, gunakan `#A66A00` di atas latar `#FFF6D8`.

## 4. Tipografi

Gunakan **Inter** bila dapat dimuat secara lokal/tepercaya; fallback: `"Segoe UI", Arial, sans-serif`. Jangan memaksakan font baru bila kebijakan aplikasi tidak mengizinkannya—Segoe UI sudah sesuai untuk antarmuka Windows enterprise.

| Peran           | Ukuran / line-height | Berat | Penggunaan                |
| --------------- | -------------------- | ----: | ------------------------- |
| Display KPI     | 28–32px / 1.2        |   700 | Angka KPI utama           |
| H1              | 24px / 1.3           |   700 | Judul halaman             |
| H2              | 20px / 1.35          |   700 | Judul section/modal       |
| H3 / card title | 16px / 1.4           |   600 | Judul card atau panel     |
| Body            | 14px / 1.5           |   400 | Konten standar            |
| Body compact    | 13px / 1.45          |   400 | Sel tabel dan metadata    |
| Label / button  | 12–14px / 1.3        |   600 | Label form, tombol, badge |
| Caption         | 12px / 1.4           |   400 | Bantuan, audit metadata   |

Gunakan sentence case, bukan ALL CAPS untuk judul dan tombol. Angka KPI memakai tabular figures bila font mendukungnya. Panjang judul dipotong dengan ellipsis hanya bila tersedia tooltip atau teks lengkap di detail.

## 5. Spacing, Ukuran, Radius, dan Shadow

### 5.1 Spacing scale

Gunakan kelipatan 4px: `4, 8, 12, 16, 20, 24, 32, 40, 48`.

- Jarak label ke input: 6–8px.
- Jarak antar field: 16px; dalam form panjang: 20–24px.
- Padding card: 20px desktop, 16px tablet/mobile.
- Jarak antar card: 16px desktop, 12px mobile.
- Jarak page title ke konten: 20–24px.
- Section besar: 32px.

### 5.2 Sizing

- Tinggi input dan tombol standar: 40px; compact table/filter: 36px.
- Target sentuh mobile minimum: 44 × 44px.
- Sidebar desktop: 256px (expanded), 72px (collapsed).
- Topbar: 64px desktop, 56px mobile.
- Lebar modal: small 420px, default 560px, large 800px, extra large 1080px; tetap beri margin 16px dari layar.

### 5.3 Radius dan elevasi

- Radius input, button, badge, dropdown: 6px.
- Radius card, modal: 10px.
- Radius avatar: 50%.
- Shadow card default: `0 1px 2px rgba(16, 24, 40, .06)`.
- Shadow overlay/modal: `0 16px 40px rgba(16, 24, 40, .16)`.

Border standar `1px solid var(--neutral-300)`. Jangan menumpuk border kuat dan shadow berat pada komponen yang sama.

## 6. Layout Responsif

| Breakpoint |       Lebar | Aturan utama                                                                                     |
| ---------- | ----------: | ------------------------------------------------------------------------------------------------ |
| Mobile     |   `< 576px` | Sidebar menjadi drawer; satu kolom; filter disembunyikan/ditumpuk; tabel dapat scroll horizontal |
| Tablet     | `576–991px` | Sidebar collapsed/drawer; grid dua kolom bila ruang cukup; topbar ringkas                        |
| Desktop    |   `≥ 992px` | Sidebar permanen; content grid 12 kolom; filter inline bila muat                                 |
| Wide       |  `≥ 1200px` | Maksimalkan ruang data; jangan memperlebar teks menjadi sulit dibaca                             |

Gunakan `.container-fluid` dengan padding konten 24px pada desktop, 20px tablet, dan 16px mobile. Di desktop, content area tetap fleksibel; jangan memberi max-width sempit untuk halaman tabel. Gunakan grid Bootstrap (`row`, `col-*`, `g-*`) dan jangan mengandalkan posisi absolut untuk layout inti.

### Struktur halaman standar

1. Breadcrumb ringkas (opsional bila hierarki > 1 tingkat).
2. Bar judul: judul, deskripsi singkat/metadata, action utama di kanan.
3. Context/status strip bila ada periode atau filter aktif.
4. Konten utama: KPI, card, list, atau tabel.
5. Footer aplikasi.

Pada mobile, action utama tetap terlihat; action sekunder pindahkan ke dropdown “Lainnya”. Jangan menyembunyikan aksi destruktif tanpa konfirmasi.

## 7. Shell Aplikasi: Sidebar, Topbar, Footer

### Sidebar

- Background biru JXB gelap `--jxb-blue-700` agar aplikasi tidak didominasi putih; teks menu putih/terang. Area kerja (card/tabel) tetap putih.
- Logo/wordmark berada di area tinggi 64px, dengan divider halus (putih transparan) di bawahnya.
- Item menu tinggi 44px, padding horizontal 12–16px, ikon 18px, gap 12px.
- Menu aktif: latar `rgba(255,255,255,.15)`, teks dan ikon putih, indikator kiri 3px `--jxb-logo-yellow`. Jangan memakai full blue solid untuk semua menu aktif.
- Menu hover: `rgba(255,255,255,.08)`; submenu diberi indent, bukan warna baru.
- Kelompok menu diberi label 11–12px `rgba(255,255,255,.45)`.
- Sidebar collapsed menampilkan ikon dengan tooltip; jangan hanya andalkan tooltip untuk navigasi penting di touch device.

### Topbar

- Background putih, tinggi 64px, dengan **garis aksen tri-warna brand 3px** (biru→merah→kuning) di tepi bawah sebagai pengganti border biasa.
- Kiri: toggle sidebar dan breadcrumb/halaman. Kanan: notifikasi, bantuan (bila ada), dan menu profil.
- Hindari topbar warna-warni; cukup satu garis aksen tri-warna.
- Notifikasi memiliki badge angka kecil dan panel dropdown yang dapat dibaca keyboard.

### Footer

Ringkas, 13px neutral 500, border atas halus. Isi: `© PT Jakarta Tourisindo / Jakarta Experience Board` dan versi aplikasi bila tersedia. Jangan memakan banyak ruang vertikal.

## 8. Visual Hierarchy

- Satu halaman hanya memiliki **satu primary action** yang jelas.
- H1 dan KPI penting memakai neutral 900; label memakai neutral 700/500.
- Gunakan warna untuk makna, bukan untuk membuat setiap panel berbeda.
- Data yang perlu diputuskan sekarang: tampil paling atas atau paling kiri.
- Metadata (pembuat, waktu, ID) lebih kecil dan lebih redup, tetapi masih kontras.
- Beri judul section, ringkasan, dan action dekat dengan objek yang dipengaruhi.

## 9. Dashboard, KPI, dan Status Card

### KPI card

- Grid: 4 kolom desktop, 2 tablet, 1 mobile; sesuaikan jumlah kartu agar tidak ada kartu terlalu sempit.
- Card putih, padding 20px, border halus, radius 10px.
- Isi: label 12–13px, angka 28–32px, perubahan/periode 12px, ikon opsional dalam kotak 36px berwarna soft.
- Jangan membuat seluruh card merah atau kuning. Gunakan aksen di ikon, badge status, atau border atas 3px hanya untuk KPI yang memerlukan perhatian.
- Klikable card diberi hover ringan (border biru muda/shadow sedikit lebih tinggi) serta fokus keyboard.

### Status/summary card

Gunakan untuk status audit, tenggat, atau progres. Tampilkan status badge, nilai atau jumlah, deskripsi maksimal dua baris, dan action konteks. Jika ada progres, gunakan progress bar tinggi 6px dengan track neutral 100 dan warna semantik.

### Dashboard Monitoring

- Baris pertama: total penugasan, berjalan, perlu perhatian, selesai.
- Baris kedua: tren periode (line/column), distribusi status (bar/donut terbatas), dan daftar “Perlu tindakan”.
- Daftar perlu tindakan menampilkan judul, unit kerja, due date, pemilik, serta badge risiko; urutkan terlambat lebih dulu.
- Sediakan filter periode global dan tampilkan periode aktif secara eksplisit.

## 10. Tabel dan List

- Tempatkan tabel dalam card/surface putih dengan border 1px; header tabel `--neutral-100` atau putih dengan border bawah tegas.
- Header 12px/600, neutral 700; cell 13–14px, tinggi baris 48–56px.
- Angka rata kanan; teks rata kiri; tanggal konsisten (`dd MMM yyyy` atau standar sistem); status memakai badge.
- Kolom aksi di kanan, lebar tetap, dengan tombol ikon berlabel tooltip. Aksi utama boleh terlihat, sisanya ada menu kebab.
- `thead` sticky hanya jika tabel panjang dan area scroll terdefinisi; jangan sticky bila mengganggu header halaman.
- Row hover `#F8FAFC`; selected memakai biru soft. Jangan gunakan zebra stripe kuat.
- Prioritaskan kolom penting di mobile. Kolom sekunder boleh disembunyikan dengan tampilan detail row atau `data-label`; tabel lebar boleh horizontal scroll dengan petunjuk visual.
- Tampilkan hasil (`1–25 dari 123`), sort state, dan empty state yang jelas.

Untuk daftar/card list, pisahkan item dengan divider neutral 300, padding vertikal 14–16px, bukan masing-masing item dengan card dan shadow.

## 11. Search dan Filter

- Search field memiliki ikon di kiri, placeholder spesifik: “Cari nomor audit, unit kerja, atau auditor”.
- Filter utama tampil inline desktop bila maksimal 3–4 kontrol; selebihnya masuk panel/dropdown “Filter”.
- Tampilkan filter aktif sebagai chips yang dapat ditutup, dengan aksi “Reset filter”.
- Filter harus mempertahankan nilai saat pagination/refresh sesuai perilaku aplikasi saat ini.
- Tombol “Terapkan” digunakan untuk filter yang mahal/kompleks; pencarian teks dapat debounced jika JavaScript saat ini mendukungnya.
- Jangan memakai placeholder sebagai satu-satunya label field.

## 12. Buttons dan Action

| Varian          | Tampilan                                    | Penggunaan                                  |
| --------------- | ------------------------------------------- | ------------------------------------------- |
| Primary         | biru 600, teks putih                        | Simpan, Tambah, Terapkan, Lanjutkan         |
| Secondary       | putih, border neutral 300, teks neutral 700 | Batal, Kembali, action pendamping           |
| Tertiary / link | transparan, teks biru 600                   | Aksi ringan dalam konten                    |
| Danger          | merah 600, teks putih                       | Hapus/penolakan yang dikonfirmasi           |
| Icon button     | 36–40px, border halus/ghost                 | Lihat, edit, more; wajib tooltip/aria-label |

Primary hanya satu per area keputusan. State wajib: default, hover, active, focus-visible (ring biru 3px), disabled (opacity dan cursor; jangan hanya warna), loading (spinner kecil dan teks tetap). Hindari tombol outline biru untuk semua aksi karena menyamarkan prioritas.

## 13. Forms

- Label selalu di atas input; tanda wajib `*` merah dan keterangan singkat di bawah bila perlu.
- Input background putih, border neutral 300, radius 6px, tinggi 40px. Focus: border biru 600 + ring `0 0 0 3px rgba(7,90,168,.15)`.
- Error: border merah 600, helper text merah, ikon opsional; jelaskan apa yang harus diperbaiki.
- Success/valid dipakai seperlunya; jangan membuat tiap input valid berwarna hijau.
- Field terkait ditempatkan dalam group dengan heading ringan. Form besar boleh memakai section/divider, bukan banyak card bersarang.
- Untuk date range gunakan dua input jelas: “Tanggal mulai” dan “Tanggal selesai”; jangan mengandalkan placeholder ambigu.
- Tombol aksi form sticky di bawah hanya untuk form sangat panjang, dengan background putih dan border atas.

## 14. Badge, Alert, Toast, Modal, Pagination

### Badge/status

Badge ringkas, tinggi 24–26px, padding 4px 8px, radius 999px diperbolehkan khusus badge. Gunakan latar soft dan teks semantik; tambahkan ikon kecil atau teks lengkap agar tidak hanya dibedakan warna.

### Alert dan toast

- Alert inline untuk dampak pada halaman/form: border kiri 3–4px, latar soft, ikon, judul singkat, deskripsi, dan close bila tidak kritis.
- Toast untuk konfirmasi sementara seperti “Perubahan berhasil disimpan”; tampil kanan atas/bawah, auto-dismiss 4–6 detik, dapat ditutup, tidak menutup action penting.
- Error gagal simpan harus tetap terlihat sebagai alert atau pesan dekat form, bukan toast yang cepat hilang saja.

### Modal

Gunakan untuk konfirmasi, detail ringkas, atau form pendek. Judul jelas, tombol close, body maksimal mudah discroll, footer dengan Batal di kiri/secondary dan aksi utama di kanan. Konfirmasi hapus menyebut objek yang akan dihapus dan tidak memakai tombol ambigu seperti “Ya”. Fokus keyboard dikunci di modal; `Esc` menutup kecuali proses kritis berjalan.

### Pagination

Gunakan tombol Previous/Next dan nomor halaman seperlunya. State aktif biru 600; disabled tetap jelas. Pada mobile, Prior/Next + informasi halaman lebih baik daripada deretan nomor panjang. Selalu pasangkan dengan page size dan total hasil bila backend sudah menyediakannya.

## 15. Empty, Loading, dan Error State

- **Empty awal:** ikon outline netral, judul “Belum ada data”, penjelasan singkat, dan CTA relevan (mis. “Tambah PKPT”) bila user memiliki akses.
- **No results:** tampilkan query/filter aktif serta aksi “Reset filter”. Jangan menggunakan empty state yang sama dengan belum ada data.
- **Loading:** skeleton untuk KPI, table row, atau card; hindari spinner layar penuh kecuali boot/loading proses yang memang memblokir.
- **Error:** jelaskan dampak dan langkah pemulihan: “Data pemeriksaan belum dapat dimuat. Coba muat ulang.” Sediakan Retry jika aman.
- **Permission:** bedakan dari error teknis: “Anda tidak memiliki akses ke halaman ini.”

## 16. Ikon dan Grafik

### Ikon

Gunakan Font Awesome yang sudah tersedia dengan gaya konsisten (solid untuk navigasi/action umum, regular bila tersedia untuk status ringan). Ukuran 16px inline, 18–20px navigation, 20–24px KPI. Ikon melengkapi teks, bukan menggantikannya pada tindakan penting. Selalu sediakan tooltip dan `aria-label` pada icon-only button.

### Grafik

- Gunakan grafik hanya untuk pertanyaan yang jelas: tren, perbandingan, distribusi, atau progres.
- Warna seri: biru 600 untuk utama; biru 500/soft untuk variasi; kuning 400 untuk perhatian; merah 600 hanya untuk risiko; hijau untuk selesai.
- Maksimal 4–5 warna kategori dalam satu grafik. Gunakan legenda yang dapat dibaca dan label langsung jika memungkinkan.
- Line chart untuk tren waktu; bar horizontal untuk ranking unit/temuan; stacked bar untuk komposisi status; donut hanya untuk 2–5 bagian dan harus menyertakan angka/legenda.
- Hindari 3D, gradient, pie dengan terlalu banyak irisan, dan mengandalkan tooltip sebagai satu-satunya sumber angka.
- Sediakan tabel/teks ringkasan alternatif untuk pembaca layar dan ekspor bila kebutuhan audit memerlukannya.

## 17. Panduan Halaman Spesifik

### PKPT

Header menampilkan tahun/periode, status persetujuan, dan action “Tambah PKPT” untuk pengguna berizin. Gunakan filter tahun, unit kerja, status, dan pencarian. Tabel memprioritaskan nomor/rencana audit, unit kerja, periode, PIC, status, dan aksi. Progres tahunan dapat tampil sebagai summary card, tetapi jangan menyembunyikan detail tabel.

### Pemeriksaan Audit

Tampilkan status lifecycle yang mudah dibaca: Draft → Persiapan → Berjalan → Pelaporan → Selesai. Gunakan step/status ringkas, bukan wizard yang memaksa navigasi bila workflow backend tidak demikian. Detail pemeriksaan memiliki ringkasan utama (nomor, unit, periode, auditor, status), tab atau section untuk ruang lingkup, temuan, dokumen, dan riwayat bila sudah ada.

### Monitoring Tindak Lanjut

Fokus pada tenggat dan pemilik. KPI: total rekomendasi, belum ditindaklanjuti, mendekati due date, terlambat, selesai. Daftar default diurutkan dari terlambat, lalu due date terdekat. Tampilkan due date sebagai teks plus badge/ikon status—bukan warna saja. Filter: periode, unit kerja, status, auditor/PIC.

### Audit Trail

Tampilan list/tabel chronological, terbaru di atas. Setiap baris memuat waktu, aktor, aksi, objek, ringkasan perubahan, dan sumber bila tersedia. Gunakan filter tanggal, user, modul, dan jenis aksi. Data perubahan panjang dibuka dalam detail drawer/modal agar tabel tidak melebar.

### Master User

Tabel mengutamakan nama, username/email, role, unit kerja, status aktif, terakhir masuk (bila tersedia), aksi. Status aktif/nonaktif harus teks + badge. Aksi sensitif seperti reset password, ubah role, dan nonaktifkan dipisahkan dari edit biasa dan memakai konfirmasi.

### Unit Kerja

Tampilkan struktur dengan tabel atau tree sederhana sesuai struktur data. Tabel: kode, nama unit, unit induk, status, aksi. Hindari tree UI rumit jika data kecil dan kebutuhan utama adalah CRUD.

### Auditor

Daftar menampilkan nama, NIP/identitas bila digunakan, jabatan/keahlian, unit, status, dan beban penugasan bila data tersedia. Gunakan avatar inisial netral; foto tidak wajib.

### Hari Libur

Gunakan tabel dengan tanggal, nama hari libur, kategori, keterangan, dan aksi; sediakan calendar view hanya jika fungsi perencanaan memang membutuhkannya. Filter tahun dan tombol tambah adalah prioritas.

## 18. Aksesibilitas

- Pertahankan kontras minimal WCAG AA: 4.5:1 untuk teks normal, 3:1 untuk teks besar dan komponen UI penting.
- Semua kontrol dapat dioperasikan keyboard; fokus terlihat jelas dan urutan tab logis.
- Gunakan elemen HTML semantik, label terhubung ke input, heading berurutan, `aria-label` untuk tombol ikon, dan pesan error yang dapat diumumkan pembaca layar.
- Jangan menyampaikan status hanya dengan warna: gabungkan teks, ikon, pola, atau label.
- Jangan menonaktifkan zoom browser. Hormati `prefers-reduced-motion`; transisi maksimal 150–200ms dan tidak krusial untuk memahami data.
- Link harus dapat dibedakan dari teks biasa melalui warna dan/atau underline pada hover/focus.

## 19. CSS Tokens

Tambahkan stylesheet override setelah Bootstrap dan AdminLTE, misalnya `assets/css/jxb-design-system.css`. Jangan mengubah file vendor secara langsung.

```css
:root {
  --jxb-blue-700: #063f7a;
  --jxb-blue-600: #075aa8;
  --jxb-blue-500: #1674c6;
  --jxb-blue-100: #e8f2fc;
  --jxb-red-600: #c53030;
  --jxb-red-100: #fdecec;
  --jxb-yellow-600: #a66a00;
  --jxb-yellow-400: #f4b400;
  --jxb-yellow-100: #fff6d8;
  --jxb-logo-blue: #3c62ae;
  --jxb-logo-red: #ee3a27;
  --jxb-logo-yellow: #fcb42c;
  --success-600: #18794e;
  --success-100: #e8f7ee;
  --neutral-900: #172033;
  --neutral-700: #46526a;
  --neutral-500: #6e7b91;
  --neutral-300: #d8dee8;
  --neutral-100: #f1f4f8;
  --canvas: #f6f8fb;
  --surface: #ffffff;
  --jxb-sidebar-bg: #063f7a;
  --jxb-sidebar-text: rgba(255, 255, 255, 0.8);
  --jxb-sidebar-icon: rgba(255, 255, 255, 0.65);
  --jxb-sidebar-hover: rgba(255, 255, 255, 0.08);
  --jxb-sidebar-active: rgba(255, 255, 255, 0.15);
  --jxb-sidebar-divider: rgba(255, 255, 255, 0.12);
  --radius-control: 6px;
  --radius-card: 10px;
  --shadow-card: 0 1px 2px rgba(16, 24, 40, 0.06);
  --shadow-overlay: 0 16px 40px rgba(16, 24, 40, 0.16);
  --focus-ring: 0 0 0 3px rgba(7, 90, 168, 0.15);
}

body {
  background: var(--canvas);
  color: var(--neutral-900);
}
.content-wrapper {
  background: var(--canvas);
}
.card {
  border: 1px solid var(--neutral-300);
  border-radius: var(--radius-card);
  box-shadow: var(--shadow-card);
}
.btn-primary {
  background: var(--jxb-blue-600);
  border-color: var(--jxb-blue-600);
}
.btn-primary:hover,
.btn-primary:focus {
  background: var(--jxb-blue-700);
  border-color: var(--jxb-blue-700);
}
.form-control:focus,
.custom-select:focus {
  border-color: var(--jxb-blue-600);
  box-shadow: var(--focus-ring);
}
.app-header::after {
  content: "";
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 3px;
  background: linear-gradient(90deg,
    var(--jxb-logo-blue) 0 55%,
    var(--jxb-logo-red) 55% 80%,
    var(--jxb-logo-yellow) 80% 100%);
}
.app-sidebar {
  background: var(--jxb-sidebar-bg) !important;
}
.sidebar-menu > .nav-item > .nav-link.active {
  background: var(--jxb-sidebar-active);
  color: #fff;
}
.sidebar-menu > .nav-item.active::before {
  content: "";
  position: absolute;
  left: 0;
  top: 12px;
  bottom: 12px;
  width: 3px;
  background: var(--jxb-logo-yellow);
}
```

Sesuaikan selector dengan versi AdminLTE yang digunakan. Uji semua override pada halaman nyata karena kelas AdminLTE v2 dan v3 berbeda.

## 20. Panduan Migrasi Bootstrap/AdminLTE

1. Inventarisasi halaman, komponen, dan kelas AdminLTE yang dipakai; ambil screenshot baseline sebelum perubahan.
2. Tambahkan stylesheet desain setelah CSS vendor dan aktifkan pada satu halaman pilot (Dashboard Monitoring) terlebih dahulu.
3. Buat class tambahan yang stabil, misalnya `.jxb-page-header`, `.jxb-kpi-card`, `.jxb-filter-bar`, `.jxb-status-badge`; jangan menyebarkan inline style.
4. Petakan komponen lama: `small-box` menjadi KPI card netral; `info-box` menjadi summary/status card; `card` tetap dipakai dengan token baru; `badge-*` dipetakan ke status semantik; `btn-primary` memakai biru JXB.
5. Pertahankan atribut form, class JavaScript, `data-*`, modal ID, dan markup yang bergantung pada plugin. Tambahkan wrapper/class, jangan merombak tanpa kebutuhan.
6. Terapkan shell (sidebar/topbar/content) lebih dahulu, kemudian controls, form, tabel, status, dan halaman per modul.
7. Verifikasi desktop, tablet, mobile, keyboard, dan state kosong/loading/error sebelum melanjutkan modul berikutnya.
8. Hindari menimpa Bootstrap secara global dengan selector terlalu umum jika dapat memakai class `jxb-*`; dokumentasikan pengecualian.

## 21. Do / Don't

| Do                                                | Don't                                                  |
| ------------------------------------------------- | ------------------------------------------------------ |
| Pakai biru untuk CTA dan navigasi aktif           | Membuat semua card biru, merah, dan kuning             |
| Pakai surface putih, border halus, dan whitespace | Menumpuk card bershadow di dalam card bershadow        |
| Tampilkan teks status beserta warna               | Mengandalkan warna saja untuk status                   |
| Gunakan action satu primary per area              | Memberi semua tombol gaya primary                      |
| Pertahankan tabel padat tetapi terbaca            | Memaksa seluruh kolom tabel desktop ke layar mobile    |
| Gunakan empty/loading/error yang spesifik         | Menampilkan tabel kosong tanpa penjelasan              |
| Tambahkan CSS override setelah vendor             | Mengedit file Bootstrap/AdminLTE vendor langsung       |
| Uji fungsi lama setelah tiap modul                | Mengganti struktur form/selector JS tanpa regresi test |

## 22. Checklist Implementasi

- [ ] Semua token warna, typography, spacing, radius, shadow didefinisikan di satu stylesheet.
- [ ] Primary action konsisten biru JXB; merah/kuning hanya untuk status atau perhatian.
- [ ] Sidebar, topbar, footer, dan shell responsif selesai.
- [ ] Header halaman memiliki judul, konteks, dan action yang jelas.
- [ ] KPI/status card memakai hierarchy dan status semantik.
- [ ] Tabel memiliki state sort, hover, hasil, empty, dan mobile overflow yang layak.
- [ ] Search/filter dapat diakses, menunjukkan filter aktif, dan mudah di-reset.
- [ ] Semua form memiliki label, focus state, validation/error yang jelas.
- [ ] Button, badge, alert, toast, modal, dan pagination memiliki state lengkap.
- [ ] Empty, loading, error, permission state tersedia untuk halaman data.
- [ ] Ikon memiliki makna konsisten; action icon-only punya tooltip dan label aksesibel.
- [ ] Grafik memakai palet terbatas dan menyajikan data alternatif bila perlu.
- [ ] Kontras, keyboard navigation, fokus, dan zoom sudah diuji.
- [ ] Desktop (≥992px), tablet (576–991px), dan mobile (<576px) diuji.
- [ ] Tidak ada perubahan pada database, backend, route, API, permission, atau perilaku CRUD.
- [ ] Screenshot before/after dan regresi alur utama setiap modul telah diperiksa.

---

Dokumen ini adalah acuan visual utama. Bila terdapat konflik antara keinginan dekoratif dan kejelasan informasi audit, pilih kejelasan, konsistensi, serta aksesibilitas.
