# ASRI Form Perangkat — Product Requirements Document

## 1. Overview

**ASRI Form Perangkat** is an internal web application for managing IT device inspection (Pemeriksaan) and maintenance (Perawatan) workflows across ASRI's multiple business locations. The system replaces manual/paper-based form processes with a digital approval chain, real-time tracking, and reporting dashboard.

### 1.1 Goals

- Digitize device inspection and maintenance form workflows
- Enforce multi-level approval chains (Diperiksa → Diketahui → Disetujui)
- Provide real-time visibility into form statuses across all sites
- Support asset lifecycle tracking with barcode identification
- Enable role-based access control for data isolation

### 1.2 Target Users

| Role | Description |
|---|---|
| Admin | Full system access, user/asset/site management, reports |
| Teknisi | Creates inspection/maintenance forms for assigned devices |
| Pengguna (End User) | Reviews and approves forms for devices assigned to them |
| Supervisor IT | Second-level approval for all forms |
| Manager IT | Final approval authority for all forms |

---

## 2. User Roles & Permissions

### 2.1 Role Matrix

| Permission | Admin | Teknisi | Pengguna | Supervisor IT | Manager IT |
|---|:---:|:---:|:---:|:---:|:---:|
| `manage-users` | ✓ | | | | |
| `manage-assets` | ✓ | | | | |
| `manage-checklist-templates` | ✓ | | | | |
| `view-all-forms` | ✓ | | | ✓ | ✓ |
| `view-reports` | ✓ | | | ✓ | ✓ |
| `create-pemeriksaan` | ✓ | ✓ | | | |
| `create-perawatan` | ✓ | ✓ | | | |
| `approve-diketahui` | ✓ | | ✓ | ✓ | ✓ |
| `approve-disetujui` | ✓ | | | ✓ | ✓ |
| `view-own-forms` | | ✓ | | | |
| `view-assigned-forms` | | | ✓ | | |

### 2.2 Data Isolation

- **Teknisi**: Can only see forms they created (`user_id = current user`)
- **Pengguna**: Can only see forms where they are the assigned approver (`pengguna_id = current user`) and assets assigned to them (`assigned_user_id = current user`)
- **Admin / Supervisor / Manager**: Full visibility across all data

---

## 3. Features

### 3.1 Authentication & Authorization

- Email + password login
- Role-based access control via Spatie Permission
- Session-based authentication with CSRF protection
- Automatic logout on session expiry
- Theme preference persistence (light/dark)

### 3.2 Form Management

#### 3.2.1 Pemeriksaan (Inspection Form)

| Field | Type | Required | Description |
|---|---|---|---|
| `nomor_form` | string | auto | Auto-generated (PBR-XXXX) |
| `user_id` | FK → users | auto | Creator (teknisi) |
| `pengguna_id` | FK → users | yes | Assigned end-user for approval |
| `asset_id` | FK → assets | yes | Device being inspected |
| `site_location` | FK → sites | yes | Site where inspection occurs |
| `location_detail` | string | no | Floor/room details |
| `kondisi` | enum | yes | `baru` / `lama` |
| `notes` | text | no | Additional notes |
| `status` | enum | auto | See workflow below |

**Checklist Categories:**
- Hardware (14 items): Processor, Mainboard, Monitor, Casing, Camera, Ports, Connectivity, Adaptor, Trackpad/Keyboard, Battery, Audio, RAM, Storage, GPU
- Application (8 items): Antivirus, ManageEngine, Office 365, OneDrive, Teams, Adobe Reader, Browser, Anydesk
- Operating System (5 items): OS Name, Hostname, User Account, Disk Usage, System Performance

#### 3.2.2 Perawatan (Maintenance Form)

| Field | Type | Required | Description |
|---|---|---|---|
| `nomor_form` | string | auto | Auto-generated (PRW-XXXX) |
| `user_id` | FK → users | auto | Creator (teknisi) |
| `pengguna_id` | FK → users | yes | Assigned end-user for approval |
| `asset_id` | FK → assets | yes | Device being maintained |
| `site_location` | FK → sites | yes | Site where maintenance occurs |
| `location_detail` | string | no | Floor/room details |
| `kondisi_akhir` | enum | yes | `good_normal` / `caution_poor` |
| `notes` | text | no | Additional notes |
| `status` | enum | auto | See workflow below |

**Checklist Categories:**
- Hardware (5 items): Temperature, Cleaning, Battery Report, Memory Test, Hardisk Test
- Application (9 items): Antivirus, Endpoint Central, Office 365, Anydesk, Browser, PDF, OneDrive, Teams, 7-Zip
- Operating System (5 items): Clear Cache, Defragment, RAM Optimization, Restore Point, Performance Check

### 3.3 Approval Workflow

```
Draft → Submitted → Diketahui → Disetujui → Selesai
  ↓         ↓           ↓           ↓
Revisi   Revisi      Revisi      Revisi
```

| Level | Approver | Trigger |
|---|---|---|
| DiperiksaOleh | Creator (teknisi) | Auto-approved on submit |
| DiketahuiOleh | Pengguna (`pengguna_id`) | Manual approval by assigned end-user |
| DisetujuiOleh | Supervisor IT / Manager IT | Manual approval by IT management |

**Rejection:** Any approver can reject with notes, returning the form to `revisi` status.

### 3.4 Asset Management

| Field | Type | Description |
|---|---|---|
| `kategori` | string | Laptop, Desktop, Monitor, Printer, Networking, Server, UPS, Projector |
| `brand` | string | Manufacturer |
| `tipe` | string | Model number |
| `nama_perangkat` | string | Display name |
| `no_serial` | string | Serial number |
| `no_asset` | string | Unique asset code (barcode) |
| `status` | string | Auto-determined by assignment |
| `operating_unit` | FK → sites | Operating business unit |
| `site_location_asset` | FK → sites | Physical location |
| `assigned_user_id` | FK → users | Assigned end-user (nullable) |

**Status Logic:**
- `assigned_user_id` is set → `active`
- `assigned_user_id` is null → `inactive`
- `disposed` preserved for legacy/archived assets

**Visibility Rules:**
- Admin / Supervisor / Manager / Teknisi: See all assets
- Pengguna: See only assets assigned to them

### 3.5 Site Management

Sites represent ASRI business locations across Indonesia.

| Field | Type | Description |
|---|---|---|
| `id_site` | string (3) | Primary key (e.g., A01, F01, M01) |
| `site` | string | Site name |
| `buss` | string | Business unit code |
| `id_corp` | string | Corporate ID |
| `country` | string | Country |
| `provincy` | string | Province |
| `city` | string | City |
| `address` | text | Full address |
| `url_maps` | text | Google Maps URL |

### 3.6 Dashboard & Reports

- Forms by site location (bar chart)
- Forms by asset (top 10)
- Monthly form trends (line chart)
- Date range filtering
- Operating unit filtering

### 3.7 Notification System

- Bell icon with unread count
- Real-time polling for new notifications
- Targeted notifications:
  - Diketahui: Sent to assigned `pengguna` user
  - Disetujui: Sent to all Supervisor IT, Manager IT, and Admin users
- Click-through to approval page

### 3.8 Asset Barcode

- Code 128 barcode generation via `picqer/php-barcode-generator`
- SVG format for crisp rendering
- Displayed on asset detail pages

---

## 4. Database Schema

### 4.1 Entity Relationship Summary

```
users ──┬── form_pemeriksaan ──┬── form_pemeriksaan_items
        │                      └── form_approvals (polymorphic)
        ├── form_perawatan ────┬── form_perawatan_items
        │                      └── form_approvals (polymorphic)
        ├── assets ────────────┬── form_pemeriksaan
        │                      └── form_perawatan
        └── form_approvals (polymorphic)

sites ──┬── assets (operating_unit)
        ├── assets (site_location_asset)
        ├── form_pemeriksaan (site_location)
        └── form_perawatan (site_location)

checklist_templates ──┬── checklist_template_items
                      ├── form_pemeriksaan_items (template_item_id)
                      └── form_perawatan_items (template_item_id)
```

### 4.2 Key Tables

| Table | Description |
|---|---|
| `users` | System users with roles |
| `sites` | Business locations |
| `assets` | IT devices/equipment |
| `checklist_templates` | Inspection/maintenance templates |
| `checklist_template_items` | Individual checklist items |
| `form_pemeriksaan` | Inspection form headers |
| `form_pemeriksaan_items` | Inspection checklist responses |
| `form_perawatan` | Maintenance form headers |
| `form_perawatan_items` | Maintenance checklist responses |
| `form_approvals` | Polymorphic approval records |
| `form_attachments` | Polymorphic file attachments |

---

## 5. Non-Functional Requirements

### 5.1 Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Livewire | Livewire 3 + Volt |
| Frontend | Tailwind CSS 4, Alpine.js |
| Charts | Chart.js 4 |
| Database | MySQL 8 |
| Auth | Laravel Breeze + Spatie Permission |
| Build | Vite 7 |
| Barcode | picqer/php-barcode-generator |

### 5.2 Performance

- Pagination for all list views (12-15 items per page)
- Eager loading for relationships
- Debounced search inputs (300-500ms)
- Cached chart data with event-driven refresh

### 5.3 Security

- CSRF token protection on all forms
- Role-based middleware (`role:admin`, etc.)
- Gate-based admin panel access
- No-cache headers on authenticated pages
- Secure session handling with forced logout

### 5.4 Responsive Design

- Mobile-first responsive layout
- Collapsible sidebar on mobile
- Bottom navigation bar for mobile users
- Touch-optimized signature pads

---

## 6. Deployment

### 6.1 Requirements

- PHP 8.3+
- MySQL 8.0+
- Node.js 18+
- Composer
- NPM

### 6.2 Environment

- Remote database server (configurable)
- Asset compilation via Vite
- PWA support (manifest.json, service worker ready)

---

## Appendix A: Default User Accounts

| Name | Email | Password | Role |
|---|---|---|---|
| Rizky Pratama | admin@asri.co.id | password | admin |
| Ahmad Fauzi | teknisi@asri.co.id | password | teknisi |
| Dedi Kurniawan | teknisi2@asri.co.id | password | teknisi |
| Siti Nurhaliza | user@asri.co.id | password | pengguna |
| Budi Santoso | user2@asri.co.id | password | pengguna |
| Maya Indah | user3@asri.co.id | password | pengguna |
| Andi Wijaya | supervisor@asri.co.id | password | supervisor_it |
| Dewi Kartika | manager@asri.co.id | password | manager_it |

## Appendix B: Form Number Conventions

- Pemeriksaan: `PBR-0001`, `PBR-0002`, ...
- Perawatan: `PRW-0001`, `PRW-0002`, ...
