<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissions = collect([
            'view-published-documents' => 'Lihat dokumen published',
            'view-all-published-documents' => 'Lihat semua dokumen published lintas departemen',
            'view-governance-data' => 'Lihat metadata governance',
            'manage-documents' => 'Kelola dokumen',
            'manage-organization-structures' => 'Kelola struktur organisasi',
            'publish-documents' => 'Publish dan archive dokumen',
            'manage-master-data' => 'Kelola master data',
            'resolve-broken-links' => 'Selesaikan laporan link',
            'view-audit-trail' => 'Lihat audit trail',
            'manage-users' => 'Kelola user dan role',
            'manage-settings' => 'Kelola konfigurasi',
        ])->map(fn (string $displayName, string $name) => Permission::firstOrCreate(
            ['name' => $name],
            ['display_name' => $displayName]
        ));

        $roles = [
            'employee' => [
                'display_name' => 'Employee',
                'permissions' => ['view-published-documents'],
            ],
            'bod' => [
                'display_name' => 'BOD',
                'permissions' => ['view-published-documents', 'view-all-published-documents'],
            ],
            'document-admin' => [
                'display_name' => 'Document Admin',
                'permissions' => ['view-published-documents', 'view-all-published-documents', 'view-governance-data', 'manage-documents', 'manage-organization-structures', 'publish-documents', 'manage-master-data', 'resolve-broken-links', 'view-audit-trail', 'manage-users'],
            ],
            'super-admin' => [
                'display_name' => 'Super Admin',
                'permissions' => $permissions->keys()->all(),
            ],
            'auditor' => [
                'display_name' => 'Auditor',
                'permissions' => ['view-published-documents', 'view-all-published-documents', 'view-governance-data', 'view-audit-trail'],
            ],
        ];

        foreach ($roles as $name => $payload) {
            $role = Role::firstOrCreate(
                ['name' => $name],
                ['display_name' => $payload['display_name']]
            );

            $role->permissions()->sync($permissions->only($payload['permissions'])->pluck('id'));
        }

        $departments = collect([
            ['code' => 'QA', 'name' => 'Quality Assurance'],
            ['code' => 'OPS', 'name' => 'Operations'],
            ['code' => 'HR', 'name' => 'Human Resources'],
            ['code' => 'IT', 'name' => 'Information Technology'],
            ['code' => 'FIN', 'name' => 'Finance'],
        ])->map(fn (array $data) => Department::firstOrCreate(['code' => $data['code']], $data));

        $types = collect([
            ['code' => 'SOP', 'name' => 'Standard Operating Procedure'],
            ['code' => 'IK', 'name' => 'Instruksi Kerja'],
        ])->map(fn (array $data) => DocumentType::firstOrCreate(['code' => $data['code']], $data));

        $categories = collect(['Mutu', 'Operasional', 'Keselamatan', 'SDM', 'Teknologi'])
            ->map(fn (string $name) => Category::firstOrCreate(['name' => $name], ['active' => true]));

        $tags = collect(['audit', 'produksi', 'onboarding', 'keamanan', 'review-tahunan'])
            ->map(fn (string $name) => Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]));

        $users = collect([
            ['name' => 'Super Admin', 'email' => 'superadmin@example.com', 'role' => 'super-admin', 'department' => 'IT'],
            ['name' => 'Dewi Admin Dokumen', 'email' => 'admin@example.com', 'role' => 'document-admin', 'department' => 'QA'],
            ['name' => 'Dimas BOD', 'email' => 'bod@example.com', 'role' => 'bod', 'department' => null],
            ['name' => 'Bima Employee', 'email' => 'employee@example.com', 'role' => 'employee', 'department' => 'OPS'],
            ['name' => 'Rani Auditor', 'email' => 'auditor@example.com', 'role' => 'auditor', 'department' => 'QA'],
        ])->map(function (array $data) use ($departments): User {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'department_id' => $data['department'] ? $departments->firstWhere('code', $data['department'])->id : null,
                ]
            );

            $user->roles()->sync(Role::where('name', $data['role'])->pluck('id'));

            return $user;
        });

        $admin = $users->firstWhere('email', 'admin@example.com');

        $published = [
            [
                'document_number' => 'SOP-QA-001',
                'title' => 'Pengendalian Dokumen Mutu',
                'type' => 'SOP',
                'department' => 'QA',
                'category' => 'Mutu',
                'owner_name' => 'Quality Assurance',
                'summary' => 'Prosedur pengendalian penerbitan, review, distribusi, dan arsip dokumen mutu perusahaan.',
                'version' => '1.0',
                'url' => 'https://docs.google.com/document/d/example-sop-qa-001',
                'tags' => ['audit', 'review-tahunan'],
            ],
            [
                'document_number' => 'IK-OPS-014',
                'title' => 'Pemeriksaan Awal Mesin Produksi',
                'type' => 'IK',
                'department' => 'OPS',
                'category' => 'Operasional',
                'owner_name' => 'Operations',
                'summary' => 'Instruksi kerja pemeriksaan awal mesin sebelum shift produksi dimulai.',
                'version' => '2.1',
                'url' => 'https://docs.google.com/document/d/example-ik-ops-014',
                'tags' => ['produksi', 'keamanan'],
            ],
            [
                'document_number' => 'SOP-FIN-003',
                'title' => 'Pengajuan dan Verifikasi Pembayaran Vendor',
                'type' => 'SOP',
                'department' => 'FIN',
                'category' => 'Operasional',
                'owner_name' => 'Finance',
                'summary' => 'Prosedur pengajuan, pemeriksaan dokumen pendukung, dan verifikasi pembayaran vendor.',
                'version' => '1.0',
                'url' => 'https://docs.google.com/document/d/example-sop-fin-003',
                'tags' => ['audit', 'review-tahunan'],
            ],
        ];

        foreach ($published as $payload) {
            $document = Document::updateOrCreate(
                ['document_number' => $payload['document_number']],
                [
                    'title' => $payload['title'],
                    'type_id' => $types->firstWhere('code', $payload['type'])->id,
                    'department_id' => $departments->firstWhere('code', $payload['department'])->id,
                    'category_id' => $categories->firstWhere('name', $payload['category'])->id,
                    'owner_name' => $payload['owner_name'],
                    'summary' => $payload['summary'],
                    'status' => Document::STATUS_PUBLISHED,
                    'published_at' => now()->subDays(10),
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );

            $document->versions()->updateOrCreate(
                ['version' => $payload['version']],
                [
                    'url' => $payload['url'],
                    'effective_at' => now()->subMonth()->toDateString(),
                    'review_at' => now()->addMonths(11)->toDateString(),
                    'expired_at' => now()->addMonths(23)->toDateString(),
                    'change_summary' => 'Versi awal katalog portal',
                    'status' => DocumentVersion::STATUS_PUBLISHED,
                    'published_at' => now()->subDays(10),
                    'created_by' => $admin->id,
                ]
            );

            $document->tags()->sync($tags->whereIn('name', $payload['tags'])->pluck('id'));
        }

        $draft = Document::updateOrCreate(
            ['document_number' => 'SOP-HR-002'],
            [
                'title' => 'Onboarding Karyawan Baru',
                'type_id' => $types->firstWhere('code', 'SOP')->id,
                'department_id' => $departments->firstWhere('code', 'HR')->id,
                'category_id' => $categories->firstWhere('name', 'SDM')->id,
                'owner_name' => 'Human Resources',
                'summary' => 'Draft prosedur onboarding karyawan baru.',
                'status' => Document::STATUS_DRAFT,
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        $draft->versions()->updateOrCreate(
            ['version' => '0.1'],
            [
                'url' => 'https://docs.google.com/document/d/example-sop-hr-002',
                'effective_at' => null,
                'review_at' => null,
                'expired_at' => null,
                'change_summary' => 'Draft awal untuk review HR',
                'status' => DocumentVersion::STATUS_DRAFT,
                'created_by' => $admin->id,
            ]
        );

        Setting::updateOrCreate(['key' => 'allowed_document_hosts'], ['value' => '*.sharepoint.com,drive.google.com,docs.google.com,dms.internal,files.internal', 'type' => 'string']);
        Setting::updateOrCreate(['key' => 'allowed_intranet_hosts'], ['value' => '*.internal,*.local,localhost', 'type' => 'string']);
        Setting::updateOrCreate(['key' => 'admin_idle_timeout_minutes'], ['value' => '30', 'type' => 'integer']);

        AuditLog::firstOrCreate([
            'action' => 'system.seeded',
            'auditable_type' => null,
            'auditable_id' => null,
        ], [
            'actor_id' => $admin->id,
            'new_values' => ['source' => 'DOC.docx system requirement'],
        ]);
    }
}
