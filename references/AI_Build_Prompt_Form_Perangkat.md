# Prompt AI — Build Aplikasi Digitalisasi Form Pemeriksaan & Perawatan Perangkat

Gunakan prompt ini untuk AI coding agent (Claude Code, Opencode, dll). Sudah disusun bertahap (per fase) supaya tidak overload dalam satu request.

---

## PROMPT UTAMA (System/Project Context) — kirim di awal sesi

text

Copy

```
Kamu akan membantu saya membangun aplikasi web "Form Perawatan & Pemeriksaan Perangkat Digital"untuk perusahaan ASRI (Agung Sedayu Group), divisi IT Operation.TUJUAN APLIKASI:Menggantikan form fisik/print "Formulir Pemeriksaan Perangkat" dan "Formulir Perawatan Perangkat"menjadi form digital berbasis web, dengan e-signature, upload evidence foto, approval berjenjang,dan riwayat perangkat yang bisa dicari/diaudit.TECH STACK:- Backend: Laravel 12- Database: MySQL- Frontend: Tailwind CSS + Livewire 3 + Alpine.js (TALL stack)- Admin panel: Filament PHP untuk modul master data & laporan- E-signature: signature_pad.js- Upload foto evidence: input capture langsung dari kamera (mobile), simpan via spatie/laravel-medialibrary- Scan barcode/QR aset: html5-qrcode- Export PDF sesuai layout form asli: barryvdh/laravel-dompdf- PWA: mendukung "Add to Home Screen" agar bisa dipakai seperti app di HP teknisiDESAIN/UI:- Tema visual: glassmorphism (blur/transparent card, "glass bottom navigation" khusus di mobile)- Palet warna: monokrom hitam & putih saja (tanpa warna aksen lain)- Wajib ada toggle Dark Mode / Light Mode, tersimpan preferensinya per user- Layout harus flexible & dinamis: responsive penuh dari mobile, tablet, sampai desktop- Gunakan Tailwind dark: variant dan CSS variable untuk theming, jangan hardcode warnaUSER ROLES:1. Admin/Developer — kelola master data & full access2. Staff/Teknisi IT — isi form, upload evidence, submit3. Pengguna (karyawan pemilik perangkat) — approve sebagai "Diketahui Oleh"4. Supervisor IT — approve tingkat kedua5. Manager IT Operation — approve finalDUA MODUL FORM UTAMA (field lengkap ada di PRD terlampir):1. Form Pemeriksaan Perangkat — dipakai saat serah terima/pengecekan awal aset (baru/lama),   mencakup: info pengguna, info perangkat, kondisi (baru/lama), pemeriksaan hardware,   pemeriksaan aplikasi, operating system, tindakan (install/repair/upgrade dll), approval 3 tingkat.2. Form Perawatan Perangkat — dipakai untuk maintenance berkala, mencakup:   info pengguna & perangkat, perawatan hardware, perawatan aplikasi, perawatan OS,   kondisi setelah perawatan (Good/Normal atau Caution/Poor), approval 3 tingkat.WORKFLOW STATUS: Draft -> Submitted -> Diketahui -> Disetujui -> Selesai (locked, read-only, bisa export PDF)Sebelum mulai coding, tolong:1. Konfirmasi pemahamanmu terhadap requirement di atas2. Usulkan struktur folder Laravel + skema database (migration) untuk kedua form + approval + attachment3. Tunggu konfirmasi saya sebelum lanjut ke implementasi kode
```

---

## FASE 1 — Setup & Database

text

Copy

```
Buatkan project Laravel 12 baru dengan:1. Auth menggunakan Laravel Breeze (Livewire stack) + Spatie Laravel-Permission untuk RBAC 5 role   di atas (Admin, Teknisi, Pengguna, Supervisor IT, Manager IT Operation)2. Migration untuk tabel:   - users (tambahan: nik, department, business_unit, site, no_telepon)   - assets (kategori, brand, tipe, nama_perangkat, no_serial, no_asset, status)   - checklist_templates & checklist_template_items (untuk item hardware/aplikasi/OS yang bisa     dikelola dinamis oleh admin, dibedakan per jenis form: pemeriksaan/perawatan)   - form_pemeriksaan (header) & form_pemeriksaan_items (detail per item + status ✓/X + keterangan)   - form_perawatan (header) & form_perawatan_items   - form_approvals (polymorphic: bisa untuk pemeriksaan maupun perawatan — role, user_id,     signature_path, approved_at, catatan)   - form_attachments (polymorphic, untuk evidence foto)3. Seeder untuk role, permission, dan checklist_template default sesuai daftar item di PRD   (Processor, Mainboard, Monitor/LCD, dst untuk pemeriksaan; Temperature Check, Physical Cleaning,   dst untuk perawatan)Setup Tailwind dengan CSS variable untuk warna (agar dark/light mode gampang di-toggle) dankonfigurasi dark mode class-based di tailwind.config.js.
```

## FASE 2 — Modul Form Pemeriksaan Perangkat

text

Copy

```
Buatkan Livewire component untuk form Pemeriksaan Perangkat dengan:- Multi-step / accordion section: Info Pengguna -> Info Perangkat -> Kondisi -> Pemeriksaan Hardware  -> Pemeriksaan Aplikasi -> Operating System -> Tindakan -> Review & Submit- Auto-fill info pengguna dari user login (bisa diubah manual jika mengisikan untuk user lain)- Fitur scan barcode/QR aset (html5-qrcode) untuk auto-fill info perangkat dari tabel assets- Setiap item checklist hardware/aplikasi: toggle status Baik/Tidak Baik + textarea keterangan +  tombol upload foto evidence (pakai kamera di mobile)- Autosave draft setiap perubahan (biar tidak hilang kalau koneksi putus)- Nomor form auto-generate format: 00X/PMR/{no_asset}/{ddmmyy}- Tampilan mengikuti tema glassmorphism hitam-putih dengan dark/light toggle- Setelah submit, redirect ke halaman signature untuk e-sign "Diperiksa Oleh"
```

## FASE 3 — Modul Form Perawatan Perangkat

text

Copy

```
Ulangi pola yang sama seperti Fase 2 sebelumnya, tapi untuk Form Perawatan Perangkat dengan section:Info Pengguna -> Info Perangkat -> Perawatan Hardware -> Perawatan Aplikasi ->Perawatan Operating System -> Kondisi Setelah Perawatan (Good/Normal atau Caution/Poor) ->Catatan Tambahan -> Review & Submit -> e-sign "Perawatan Oleh"
```

## FASE 4 — Approval Berjenjang & Notifikasi

text

Copy

```
Buatkan flow approval:1. Setelah form disubmit teknisi, sistem kirim notifikasi in-app ke user terkait untuk approve   sebagai "Diketahui Oleh" (khusus form perawatan: ke Supervisor IT)2. Setelah itu, notifikasi ke Manager IT Operation untuk approve final "Disetujui Oleh"3. Halaman approval menampilkan ringkasan form (read-only) + signature pad untuk approve,   atau tombol reject dengan catatan alasan (form balik ke teknisi jadi status "Revisi")4. Setelah approve final, status jadi "Selesai" dan form terkunci (read-only permanen)5. Buatkan notification bell component (Livewire) di navbar untuk approval pending
```

## FASE 5 — Dashboard, Riwayat, & Export

text

Copy

```
1. Buatkan halaman detail aset yang menampilkan timeline seluruh histori form pemeriksaan &   perawatan untuk aset tersebut2. Buatkan dashboard dengan ringkasan: jumlah form per status, distribusi kondisi   (good/caution/poor), grafik tren perawatan per bulan3. Buatkan fitur export PDF per form yang layoutnya mengikuti format form fisik asli   (gunakan barryvdh/laravel-dompdf), termasuk area tanda tangan yang menampilkan gambar   signature yang sudah di-capture4. Buatkan halaman search & filter form berdasarkan user, department, site, kategori, tanggal, status
```

## FASE 6 — PWA & Polish

text

Copy

```
1. Tambahkan manifest.json + service worker sederhana agar aplikasi bisa di-"Add to Home Screen"   di mobile2. Review seluruh halaman untuk memastikan konsisten: efek glass (backdrop-blur, border   translucent), transisi dark/light mode smooth, kontras warna tetap jelas terbaca di kedua mode3. Uji responsive di breakpoint mobile (bottom nav glass), tablet, dan desktop (sidebar nav)
```

---

### Catatan Penggunaan

-   Kirim **PROMPT UTAMA** di awal sesi baru dengan AI coding agent, sertakan juga file PRD  
    (`PRD_Form_Perangkat_Digital.md`) sebagai referensi lengkap requirement.
-   Kirim tiap **FASE** secara berurutan, satu per satu, setelah fase sebelumnya selesai & direview.
-   Sesuaikan nama package/library jika di lingkungan development Anda ada preferensi lain.