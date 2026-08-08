# ASRI Form Perangkat

Internal web application for managing IT device inspection (Pemeriksaan) and maintenance (Perawatan) workflows across ASRI's multiple business locations.

## Tech Stack

- **Backend:** Laravel 12, PHP 8.3+
- **Frontend:** Livewire 3 + Volt, Tailwind CSS 4, Alpine.js
- **Database:** MySQL 8.0+
- **Build Tool:** Vite 7
- **Charts:** Chart.js 4
- **Auth:** Laravel Breeze + Spatie Permission
- **Barcode:** picqer/php-barcode-generator

## Requirements

- PHP 8.3 or higher
- MySQL 8.0 or higher
- Node.js 18 or higher
- Composer
- NPM

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd forms-asri
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install NPM dependencies

```bash
npm install
```

### 4. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure your database connection:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forms_asri
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Database setup

```bash
php artisan migrate
```

### 6. Seed the database

```bash
php artisan db:seed
```

This will create:
- 5 user roles with permissions
- 6 checklist templates (3 for inspection, 3 for maintenance)
- 16 ASRI business sites
- 8 user accounts across all roles
- 21 IT assets with site assignments
- 20 sample inspection forms (2 per status)
- 20 sample maintenance forms (2 per status)
- Approval records for all submitted forms

### 7. Build frontend assets

```bash
npm run build
```

For development with hot reload:

```bash
npm run dev
```

### 8. Start the development server

```bash
php artisan serve
```

Visit [http://localhost:8000](http://localhost:8000)

## Default Accounts

| Name | Email | Password | Role |
|---|---|---|---|
| Rizky Pratama | admin@asri.co.id | password | Admin |
| Ahmad Fauzi | teknisi@asri.co.id | password | Teknisi |
| Dedi Kurniawan | teknisi2@asri.co.id | password | Teknisi |
| Siti Nurhaliza | user@asri.co.id | password | Pengguna |
| Budi Santoso | user2@asri.co.id | password | Pengguna |
| Maya Indah | user3@asri.co.id | password | Pengguna |
| Andi Wijaya | supervisor@asri.co.id | password | Supervisor IT |
| Dewi Kartika | manager@asri.co.id | password | Manager IT |

## Project Structure

```
forms-asri/
├── app/
│   ├── Enums/                    # FormStatus, ApprovalLevel, etc.
│   ├── Livewire/
│   │   ├── Admin/                # Admin panel (Sites, Assets, Users)
│   │   ├── Approval/             # Form review & approval
│   │   ├── Assets/               # Public asset views
│   │   ├── Dashboard/            # Reports & analytics
│   │   ├── Forms/                # Form search & listing
│   │   ├── Layout/               # Navbar, sidebar, notifications
│   │   ├── Pages/                # Form creation (Pemeriksaan, Perawatan)
│   │   └── Profile/              # User profile & signature
│   └── Models/                   # Eloquent models
├── database/
│   ├── factories/                # Model factories
│   ├── migrations/               # Database migrations
│   └── seeders/                  # Database seeders
├── resources/
│   ├── css/                      # Tailwind CSS
│   ├── js/                       # JavaScript (Chart.js, HTML5QRCode)
│   └── views/
│       ├── components/           # Blade components & layouts
│       └── livewire/             # Livewire & Volt views
├── routes/
│   └── web.php                   # Application routes
└── public/
    └── vendor/                   # Chart.js UMD build
```

## Features

### Form Management
- Create inspection (Pemeriksaan) and maintenance (Perawatan) forms
- Digital checklist with real-time status tracking
- File attachments for photo evidence
- Digital signature support

### Approval Workflow
- Multi-level approval chain: Diperiksa → Diketahui → Disetujui
- Role-based approval routing
- Rejection with notes for revision
- Real-time notification system

### Asset Management
- Full asset lifecycle tracking
- Barcode generation (Code 128)
- Site location mapping
- User assignment with visibility control

### Dashboard & Reports
- Forms by site location
- Top assets by form count
- Monthly trends
- Date range and operating unit filtering

### Responsive Design
- Mobile-first responsive layout
- Dark/light theme support
- Touch-optimized signature pads
- PWA-ready with manifest.json

## Roles & Permissions

| Role | Key Permissions |
|---|---|
| Admin | Full access, manage users/assets/sites, view reports |
| Teknisi | Create forms, view own forms |
| Pengguna | Approve forms assigned to them, view assigned assets |
| Supervisor IT | Approve all forms, view reports |
| Manager IT | Final approval authority, view reports |

## Documentation

- [Product Requirements Document](PRD.md)

## License

Proprietary - ASRI Internal Use Only
