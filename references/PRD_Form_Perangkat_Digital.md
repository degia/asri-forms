# PRD — Aplikasi Digitalisasi Form Pemeriksaan & Perawatan Perangkat
**ASRI (Agung Sedayu Group) — IT Operation**

Versi 1.0 | 24 Juli 2026

---

## 1. Latar Belakang

Saat ini proses **Pemeriksaan Perangkat** (serah terima/onboarding aset) dan **Perawatan Perangkat** (maintenance berkala) di ASRI masih menggunakan form fisik (print) yang diisi manual oleh Staff/Teknisi IT Operation, lalu ditandatangani berjenjang (Pengguna → Supervisor IT → Manager IT Operation). Proses ini:

- Boros kertas dan ruang penyimpanan arsip.
- Sulit dicari kembali (evidence tersebar, rawan hilang/rusak).
- Tidak ada histori digital per aset/per user yang bisa dianalisis.
- Approval berjenjang bergantung pada tanda tangan basah → lambat jika PIC tidak di tempat.

Aplikasi ini mendigitalkan kedua form tersebut menjadi form database berbasis web yang bisa diakses dari desktop maupun mobile (termasuk saat teknisi di lapangan), lengkap dengan e-signature, upload evidence foto, dan riwayat approval.

## 2. Tujuan

1. Menghilangkan ketergantungan pada form kertas (paperless).
2. Menyimpan evidence pemeriksaan/perawatan perangkat secara terstruktur & searchable.
3. Mempercepat proses approval berjenjang secara digital.
4. Menyediakan riwayat (history) kondisi tiap perangkat dari waktu ke waktu untuk kebutuhan audit dan pengambilan keputusan (replacement, upgrade, dsb).
5. Tetap bisa menghasilkan output PDF dengan layout mengikuti format form resmi (untuk kebutuhan cetak/arsip formal bila diperlukan).

## 3. Ruang Lingkup (Scope)

Dua jenis form akan didigitalkan sesuai lampiran existing:

| Form | Kode Existing | Fungsi |
|---|---|---|
| **Formulir Pemeriksaan Perangkat** | FM-ASRI/ITE/08-00 | Pemeriksaan kondisi perangkat (baru/lama), hardware, aplikasi, tindakan, dan approval saat serah terima/pengecekan aset |
| **Formulir Perawatan Perangkat** | FM/ASRI/ITE/09-00 | Maintenance berkala: cek hardware, aplikasi, OS, kondisi setelah perawatan |

Di luar scope v1 (dicatat sebagai future work): modul asset lifecycle penuh (pengadaan, disposal), integrasi payroll/HR, notifikasi WhatsApp/email otomatis (bisa masuk fase 2).

## 4. User Roles

| Role | Deskripsi Akses |
|---|---|
| **Staff/Teknisi IT** | Membuat & mengisi form pemeriksaan/perawatan, upload evidence foto, submit untuk approval |
| **Pengguna (User/Karyawan)** | Melihat & menandatangani (approve) form terkait perangkat miliknya sebagai "Diketahui Oleh" |
| **Supervisor IT** | Review & approve/reject form (tingkat "Diketahui Oleh" untuk form perawatan) |
| **Manager IT Operation** | Approval final ("Disetujui Oleh") |
| **Admin/Developer** | Kelola master data (user, perangkat, kategori, template checklist), kelola role & akses, lihat seluruh laporan |
| **Auditor/Viewer** *(opsional fase 2)* | Akses read-only ke seluruh histori form untuk keperluan audit |

## 5. Functional Requirements

### 5.1 Modul Form Pemeriksaan Perangkat
Field mengikuti form fisik, meliputi:
- **Informasi Pengguna**: Nama User, NIK, Department, Site/Business Unit, No. Telepon, Alamat Email (auto-fill dari master data user bila sudah terdaftar)
- **Informasi Perangkat**: Kategori, Brand, Tipe, Nama Perangkat, No. Serial, No. Asset (auto-fill/lookup dari master aset via barcode/QR scan)
- **Kondisi Perangkat**: Baru / Lama (+ penjelasan kerusakan jika lama)
- **Pemeriksaan Hardware**: Processor, Mainboard, Monitor/LCD, Casing, Camera, Port USB/Charger, Connectivity, Adaptor/PSU, Trackpad/Keyboard/Mouse, Battery (kapasitas desain vs full charge + otomatis hitung %), Audio, Memory, Disk, Graphic Card, dll — tiap item punya status (✓ Baik / X Tidak Baik) + keterangan
- **Pemeriksaan Aplikasi**: Antivirus, Manage Engine, Office365, OneDrive, Teams, Adobe Reader, Browser, Anydesk, dll (checklist dinamis, bisa ditambah item lain)
- **Operating System**: Nama OS, Nama Perangkat (hostname), User Account Profile, Disk Capacity Usage, Kinerja Sistem
- **Tindakan**: checklist multi-select (Install/Repair/Reset OS, Create/Delete Account, Delete/Backup Data, Install/Uninstall Application, Service/Ganti Sparepart, Upgrade, Penggantian Unit, Others)
- **Catatan & Rekomendasi**: free text
- **Approval berjenjang 3 tingkat** dengan e-signature + tanggal: Diperiksa Oleh (Teknisi) → Diketahui Oleh (User) → Disetujui Oleh (Supervisor/Manager)

### 5.2 Modul Form Perawatan Perangkat
- **Informasi Pengguna & Perangkat** (sama seperti di atas)
- **Perawatan Hardware**: Temperature Check, Physical Cleaning, Battery Report, Memory Test, Hardisk Test (status + keterangan)
- **Perawatan Aplikasi**: Application Standard IT Check, Antivirus Kaspersky (lisensi & proteksi aktif), Manage Engine Endpoint Central (terkoneksi server)
- **Perawatan Operating System**: Clear Cache, Defragment, Optimized RAM, Check Restore Point, Performance Check
- **Kondisi Setelah Perawatan**: Good/Normal atau Caution/Poor + catatan tambahan
- **Approval berjenjang**: Perawatan Oleh (Teknisi) → Diketahui Oleh (Supervisor IT) → Disetujui Oleh (Manager IT Operation)

### 5.3 Fitur Umum (Shared)
- **Nomor form otomatis** mengikuti format existing (`No: 005/PMR/O99-FIN-NB061/240726`), auto-generate & unik
- **Upload evidence foto** langsung dari kamera device (khusus mobile) per item pemeriksaan
- **E-signature capture** (gambar tanda tangan via touch/mouse) untuk tiap approval level
- **Scan barcode/QR aset** untuk auto-fill data perangkat dari master asset
- **Riwayat per perangkat**: semua histori pemeriksaan & perawatan tampil di halaman detail aset (timeline)
- **Status workflow**: Draft → Submitted → Diketahui → Disetujui → Selesai (dengan notifikasi in-app tiap perubahan status)
- **Export PDF** hasil isian mengikuti layout form asli (untuk arsip/cetak bila dibutuhkan compliance)
- **Search & filter**: berdasarkan user, department, site, kategori perangkat, rentang tanggal, status kondisi
- **Dashboard & laporan**: jumlah pemeriksaan/perawatan per periode, distribusi kondisi (good/caution/poor), device yang butuh tindak lanjut

## 6. Non-Functional Requirements

- **Responsive & Mobile-First**: layout optimal di HP (teknisi mengisi form langsung di lokasi), tablet, dan desktop
- **Tema visual**: Glassmorphism ("glass bottom" nav & card), palet **hitam & putih (monokrom)**, dengan **Dark/Light Mode toggle**, terasa fleksibel & dinamis (micro-interaction, smooth transition)
- **PWA-ready**: bisa "Add to Home Screen", idealnya mendukung cache offline sebagian (form bisa diisi offline lalu sync saat online kembali) — mengingat teknisi kadang di area signal lemah
- **Performance**: form besar (banyak field) tetap ringan, autosave draft agar tidak kehilangan isian
- **Security**: RBAC per role, audit trail siapa mengubah apa & kapan, e-signature tidak bisa diedit setelah approve (immutable setelah locked)
- **Accessibility**: kontras warna cukup untuk mode hitam-putih (WCAG AA minimum)

## 7. Rekomendasi Tech Stack — UI & Frontend

Backend sudah ditentukan: **Laravel 12 + MySQL**. Berikut rekomendasi frontend, dengan dua opsi tergantung prioritas Anda:

### Opsi A — TALL Stack (direkomendasikan, konsisten dengan aplikasi asset management Anda yang lain)
| Layer | Tools |
|---|---|
| CSS Framework | **Tailwind CSS 3/4** — utility-first, gampang bikin efek glassmorphism (`backdrop-blur`, `bg-white/10`) dan dark mode (`dark:` variant) |
| Interaktivitas | **Livewire 3** + **Alpine.js** — reactive component tanpa perlu API terpisah, cocok untuk form kompleks dengan banyak field & validasi real-time |
| Admin/CRUD backend | **Filament PHP 3/4** — untuk panel admin (kelola master user, aset, template checklist, laporan) — hemat waktu development |
| E-signature | `signature_pad.js` (via Livewire wire:ignore) |
| Camera/Upload foto | HTML5 `<input capture="environment">` + `spatie/laravel-medialibrary` untuk storage evidence |
| Barcode/QR scan | `html5-qrcode` (JS library, akses kamera device) |
| Export PDF | `barryvdh/laravel-dompdf` atau `spatie/laravel-pdf` — generate ulang layout sesuai form asli |
| PWA | `laravel-pwa` package / manual manifest.json + service worker |

**Kelebihan**: satu bahasa (PHP + sedikit JS), development cepat, konsisten dengan stack aplikasi asset management Anda sebelumnya sehingga komponen desain (glass card, dark/light toggle, dsb) bisa dipakai ulang lintas aplikasi.

### Opsi B — Inertia.js + Vue 3 (jika ingin UX lebih "app-like" & animasi lebih halus)
| Layer | Tools |
|---|---|
| CSS Framework | Tailwind CSS |
| Frontend Framework | **Vue 3** (Composition API) via **Inertia.js** — SPA experience tanpa bikin REST API terpisah |
| State/Form | `vee-validate` atau native Vue reactive form + Inertia form helper |
| E-signature | `vue3-signature` atau `signature_pad.js` dibungkus komponen Vue |
| Animasi | `@vueuse/motion` atau Framer-Motion-like untuk transisi glass/dark-light yang lebih smooth |
| Icon | `lucide-vue-next` (monokrom, cocok tema hitam-putih) |
| PDF/QR/Media | sama seperti Opsi A (tetap pakai package Laravel di backend) |

**Kelebihan**: interaksi lebih smooth/native-app-feel, lebih mudah membuat transisi dark/light & glass effect yang halus, cocok kalau ke depan mau dikembangkan jadi mobile app (Capacitor/Ionic wrap dari Vue codebase).

> **Rekomendasi saya**: Opsi A (TALL Stack + Filament) — karena selaras dengan stack aplikasi asset management ASRI yang sudah Anda bangun, sehingga desain system (glass component, dark/light) dan bahkan sebagian logic master data (user, aset) bisa di-share/reuse antar aplikasi, dan tim maintenance cukup menguasai satu paradigma (PHP-centric).

## 8. Struktur Data (Ringkas)

Tabel inti (selain master user, aset yang mungkin sudah ada di sistem asset management):

- `form_pemeriksaan` — header (no form, user_id, asset_id, kondisi, tanggal, status workflow)
- `form_pemeriksaan_items` — detail per item hardware/aplikasi/OS (nama item, kategori item, status, keterangan)
- `form_perawatan` — header (mirip di atas)
- `form_perawatan_items` — detail per item hardware/aplikasi/OS
- `form_approvals` — histori approval (form_id, form_type, role, user_id, signature_path, approved_at, catatan)
- `form_attachments` — evidence foto per item/form
- `checklist_templates` + `checklist_template_items` — supaya item checklist (hardware/aplikasi) bisa dikelola dinamis oleh admin tanpa ubah kode

## 9. Alur Kerja (Workflow)

```
Teknisi buat/isi form (Draft)
        ↓
   Submit (upload evidence, e-sign "Diperiksa/Perawatan Oleh")
        ↓
   Notifikasi ke User/Supervisor → e-sign "Diketahui Oleh"
        ↓
   Notifikasi ke Manager IT Operation → e-sign "Disetujui Oleh"
        ↓
   Status: Selesai (locked, bisa export PDF, masuk histori aset)
```

## 10. Fase Pengembangan (Usulan)

1. **Fase 0** — Setup project Laravel 12, struktur database, autentikasi & RBAC
2. **Fase 1** — Master data (user, aset, kategori, checklist template) + integrasi dengan sistem asset management (jika ada)
3. **Fase 2** — Modul Form Pemeriksaan Perangkat (CRUD, e-signature, upload evidence)
4. **Fase 3** — Modul Form Perawatan Perangkat
5. **Fase 4** — Workflow approval berjenjang + notifikasi in-app
6. **Fase 5** — Dashboard, laporan, histori per aset, export PDF
7. **Fase 6** — PWA/offline support + polish UI (dark/light, glassmorphism, responsive QA di berbagai device)

## 11. Metrik Keberhasilan

- % pengurangan penggunaan form kertas (target 100% untuk site yang sudah pakai aplikasi)
- Waktu rata-rata proses approval (dari submit sampai disetujui) menurun dibanding proses manual
- Semua histori perangkat bisa ditelusuri dalam < 3 klik dari halaman detail aset
