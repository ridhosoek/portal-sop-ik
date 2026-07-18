# Requirement Mapping - Portal SOP & IK

Sumber: `../DOC.docx`, System Requirement Portal Dokumen SOP & IK, versi 1.0, 16 Juli 2026.

## Keputusan Implementasi

- Project dibuat sebagai aplikasi Laravel 13 di folder `portal-sop-ik`.
- Fase 1 mengikuti batasan dokumen: aplikasi menyimpan metadata dan URL resmi, bukan file dokumen.
- RBAC dibuat custom dengan tabel `roles`, `permissions`, dan pivot, agar MVP tidak bergantung pada paket pihak ketiga.
- Login lokal dipakai untuk MVP; SSO/LDAP tetap fase lanjutan.
- SQLite menjadi default lokal; PostgreSQL/MySQL dapat dipakai melalui konfigurasi `.env`.
- URL dokumen divalidasi dengan HTTPS dan allowlist domain untuk mencegah open redirect.

## Mapping Fungsional

| Requirement | Implementasi |
| --- | --- |
| FR-AUTH-001 login email/password | `AuthenticatedSessionController`, view `auth.login` |
| FR-AUTH-002 role/permission | tabel RBAC, `EnsureUserHasRole`, helper `User::hasRole()` |
| FR-AUTH-003 logout aman | route `POST /logout`, session invalidate dan regenerate token |
| FR-AUTH-004 kelola user/role | `Admin\UserRoleController`, view `admin.users.index` |
| FR-DOC-001 katalog published | `Document::scopeVisibleToEmployees()` dan `DocumentCatalogController@index` |
| FR-DOC-002 search | `Document::scopeSearch()` untuk nomor, judul, ringkasan, tag |
| FR-DOC-003 filter | filter jenis, departemen, kategori, tahun efektif |
| FR-DOC-004 detail metadata | view `documents.show` dan `admin.documents.show` |
| FR-DOC-005 buka URL aman | `DocumentCatalogController@open`, `AllowedDocumentUrl` |
| FR-DOC-006 laporan link rusak | `BrokenLinkReport`, form detail dokumen, admin resolve |
| FR-ADM-001 CRUD metadata | `Admin\DocumentController` |
| FR-ADM-002 field wajib | `StoreDocumentRequest`, `UpdateDocumentRequest` |
| FR-ADM-003 validasi URL allowlist | `AllowedDocumentUrl`, `config/sop.php`, settings |
| FR-ADM-004 draft dan publish | status draft/published, route publish |
| FR-ADM-005 histori versi | `document_versions`, draft versi baru, superseded |
| FR-ADM-006 tanggal efektif/review/expired | kolom versi dan validasi Form Request |
| FR-ADM-007 master data | `MasterDataController` |
| FR-ADM-008 daftar laporan link | `Admin\BrokenLinkReportController` |
| FR-DASH-001 dashboard pengguna | `DashboardController`, view `dashboard` |
| FR-DASH-002 dashboard admin | `Admin\DashboardController`, KPI status dan laporan |
| FR-AUD-001 audit event minimum | `AuditLogger`, `AuditLog` |
| FR-AUD-002 detail audit | actor, action, subject, old/new values, IP, user agent |

## Data dan Governance

- Entitas utama sudah dimodelkan: users, roles, permissions, documents, document_versions, document_types, departments, categories, tags, broken_link_reports, audit_logs, settings.
- `documents` memakai soft delete dan status archive sebagai pengganti hapus permanen.
- Employee hanya melihat dokumen published yang effective dan belum expired.
- Admin dan auditor dapat membaca metadata governance, draft/archived, audit, dan laporan sesuai role.

## Acceptance Criteria yang Ditest

- Employee hanya melihat dokumen published/effective.
- Employee tidak dapat mengakses area admin.
- Document Admin dapat membuat dan publish dokumen.
- URL dokumen harus sesuai allowlist.
- Employee dapat melaporkan link rusak.

## Fase Lanjutan

- Export XLSX/CSV.
- Reminder email/scheduler untuk review dan expired.
- Link checker otomatis.
- SSO/LDAP/Microsoft Entra ID.
- Approval workflow bertingkat.
- Import metadata massal.
- Performance tuning full-text search untuk 50.000 metadata.
