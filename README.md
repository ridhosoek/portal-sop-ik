# Portal SOP & IK

Aplikasi internal untuk katalog metadata SOP dan Instruksi Kerja sesuai `DOC.docx` system requirement.

Lihat `docs/requirement-mapping.md` untuk mapping requirement ke modul implementasi.

## Cakupan MVP

- Login/logout internal.
- Role-based access control: Employee, Document Admin, Super Admin, Auditor.
- Katalog dokumen published yang masih berlaku.
- Search, filter, sort, pagination, detail dokumen, dan buka URL resmi di tab baru.
- Admin CRUD metadata dokumen, publish/archive, dan draft versi baru.
- Master data departemen, kategori, jenis dokumen, dan tag.
- Laporan link bermasalah dan penyelesaiannya oleh admin.
- Audit trail untuk login, dokumen, publish/archive, user-role, settings, dan laporan link.
- URL allowlist untuk mencegah open redirect dan domain dokumen tidak sah.
- Seeder akun demo, master awal, dan contoh dokumen SOP/IK.
- Feature test untuk acceptance criteria inti.

## Kebutuhan Runtime

Laravel 13 memerlukan PHP 8.3 atau lebih baru. Mesin lokal yang terdeteksi saat implementasi masih memakai PHP 8.1.25, sehingga aplikasi sudah disiapkan sebagai Laravel 13-ready tetapi perlu dijalankan dengan PHP 8.3+.

## Setup

```bash
cd portal-sop-ik
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Jika menggunakan database selain SQLite, ubah variabel `DB_*` di `.env` sebelum menjalankan migration.

## Akun Demo

Semua akun memakai password `password`.

| Role | Email |
| --- | --- |
| Super Admin | superadmin@example.com |
| Document Admin | admin@example.com |
| Employee | employee@example.com |
| Auditor | auditor@example.com |

## Test

```bash
php artisan test
```

Test utama berada di `tests/Feature/PortalRequirementTest.php`.
