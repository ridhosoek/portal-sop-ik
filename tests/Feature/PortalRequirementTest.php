<?php

namespace Tests\Feature;

use App\Models\BrokenLinkReport;
use App\Models\Category;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\OrganizationStructure;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalRequirementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_employee_catalog_only_shows_published_and_effective_documents(): void
    {
        $this->seed();

        $employee = User::where('email', 'employee@example.com')->firstOrFail();

        $this->actingAs($employee)
            ->get(route('documents.index'))
            ->assertOk()
            ->assertSee('Pemeriksaan Awal Mesin Produksi')
            ->assertDontSee('Pengendalian Dokumen Mutu')
            ->assertDontSee('Pengajuan dan Verifikasi Pembayaran Vendor')
            ->assertDontSee('Onboarding Karyawan Baru');
    }

    public function test_login_page_presents_portal_information_and_brand_logo(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Portal Internal Indra Angkola Group')
            ->assertSee('Informasi internal perusahaan dalam satu portal')
            ->assertSee('Struktur Organisasi')
            ->assertSee('images/logo-indra-angkola.png', false);
    }

    public function test_employee_only_sees_documents_from_their_department(): void
    {
        $this->seed();

        $finance = Department::where('code', 'FIN')->firstOrFail();
        $role = Role::where('name', 'employee')->firstOrFail();
        $user = User::factory()->create([
            'name' => 'Finance Employee',
            'email' => 'finance.employee@example.com',
            'department_id' => $finance->id,
        ]);
        $user->roles()->sync([$role->id]);

        $this->actingAs($user)
            ->get(route('documents.index'))
            ->assertOk()
            ->assertSee('Pengajuan dan Verifikasi Pembayaran Vendor')
            ->assertDontSee('Pemeriksaan Awal Mesin Produksi')
            ->assertDontSee('Pengendalian Dokumen Mutu');
    }

    public function test_bod_can_see_all_published_documents_across_departments(): void
    {
        $this->seed();

        $bod = User::where('email', 'bod@example.com')->firstOrFail();

        $this->actingAs($bod)
            ->get(route('documents.index'))
            ->assertOk()
            ->assertSee('Pengendalian Dokumen Mutu')
            ->assertSee('Pemeriksaan Awal Mesin Produksi')
            ->assertSee('Pengajuan dan Verifikasi Pembayaran Vendor')
            ->assertDontSee('Onboarding Karyawan Baru');
    }

    public function test_employee_cannot_access_admin_area(): void
    {
        $this->seed();

        $employee = User::where('email', 'employee@example.com')->firstOrFail();

        $this->actingAs($employee)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_document_admin_can_create_and_publish_document_with_audit_log(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $payload = $this->validDocumentPayload();

        $this->actingAs($admin)
            ->post(route('admin.documents.store'), $payload)
            ->assertRedirect();

        $document = Document::where('document_number', 'SOP-IT-777')->firstOrFail();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'document.created',
            'auditable_id' => $document->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.documents.publish', $document))
            ->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'published',
        ]);
        $this->assertDatabaseHas('document_versions', [
            'document_id' => $document->id,
            'version' => '1.0',
            'status' => 'published',
        ]);
    }

    public function test_document_form_does_not_show_owner_or_tag_inputs(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.documents.create'))
            ->assertOk()
            ->assertDontSee('Owner')
            ->assertDontSee('Tag')
            ->assertDontSee('name="owner_name"', false)
            ->assertDontSee('name="tags[]"', false);
    }

    public function test_master_data_does_not_show_tag_management(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.master-data.index'))
            ->assertOk()
            ->assertDontSee('Tag')
            ->assertDontSee("admin/master-data/tags", false);
    }

    public function test_document_url_must_match_allowlist(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $payload = $this->validDocumentPayload([
            'url' => 'https://evil.example/document.pdf',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.documents.create'))
            ->post(route('admin.documents.store'), $payload)
            ->assertRedirect(route('admin.documents.create'))
            ->assertSessionHasErrors('url');
    }

    public function test_employee_can_report_broken_link(): void
    {
        $this->seed();

        $employee = User::where('email', 'employee@example.com')->firstOrFail();
        $document = Document::where('document_number', 'IK-OPS-014')->firstOrFail();

        $this->actingAs($employee)
            ->post(route('documents.broken-link-reports.store', $document), [
                'note' => 'Link menampilkan access denied.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('broken_link_reports', [
            'document_id' => $document->id,
            'reporter_id' => $employee->id,
            'status' => BrokenLinkReport::STATUS_OPEN,
        ]);
    }

    public function test_super_admin_can_create_user_with_role(): void
    {
        $this->seed();

        $superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();
        $employeeRole = Role::where('name', 'employee')->firstOrFail();
        $department = Department::where('code', 'IT')->firstOrFail();

        $this->actingAs($superAdmin)
            ->post(route('admin.users.store'), [
                'name' => 'User Baru',
                'email' => 'user.baru@example.com',
                'password' => 'Portal123',
                'password_confirmation' => 'Portal123',
                'status' => 'active',
                'department_id' => $department->id,
                'roles' => [$employeeRole->id],
            ])
            ->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'user.baru@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('Portal123', $user->password));
        $this->assertTrue($user->roles()->where('name', 'employee')->exists());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.created',
            'auditable_id' => $user->id,
        ]);
    }

    public function test_document_admin_can_create_users_with_non_super_admin_role(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $bodRole = Role::where('name', 'bod')->firstOrFail();
        $department = Department::where('code', 'FIN')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('User & Role', false)
            ->assertDontSee('Konfigurasi');

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Tambah User')
            ->assertSee('BOD')
            ->assertDontSee('Super Admin');

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'User BOD Baru',
                'email' => 'bod.baru@example.com',
                'password' => 'Portal123',
                'password_confirmation' => 'Portal123',
                'status' => 'active',
                'department_id' => $department->id,
                'roles' => [$bodRole->id],
            ])
            ->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'bod.baru@example.com')->firstOrFail();

        $this->assertTrue($user->roles()->where('name', 'bod')->exists());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.created',
            'auditable_id' => $user->id,
        ]);
    }

    public function test_document_admin_cannot_assign_super_admin_role(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $superAdminRole = Role::where('name', 'super-admin')->firstOrFail();
        $department = Department::where('code', 'IT')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [
                'name' => 'Calon Super Admin',
                'email' => 'calon.super@example.com',
                'password' => 'Portal123',
                'password_confirmation' => 'Portal123',
                'status' => 'active',
                'department_id' => $department->id,
                'roles' => [$superAdminRole->id],
            ])
            ->assertRedirect(route('admin.users.create'))
            ->assertSessionHasErrors('roles.0');
    }

    public function test_document_admin_can_reset_user_password(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $employee = User::where('email', 'employee@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.users.password.update', $employee), [
                'password' => 'Reset123',
                'password_confirmation' => 'Reset123',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('Reset123', $employee->fresh()->password));
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.password_reset',
            'auditable_id' => $employee->id,
        ]);
    }

    public function test_document_admin_cannot_reset_super_admin_password(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.users.password.update', $superAdmin), [
                'password' => 'Reset123',
                'password_confirmation' => 'Reset123',
            ])
            ->assertForbidden();

        $this->assertTrue(Hash::check('password', $superAdmin->fresh()->password));
    }

    public function test_document_admin_can_upload_published_organization_structure(): void
    {
        Storage::fake('local');
        $this->seed();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $department = Department::where('code', 'OPS')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.organization-structures.store'), [
                'department_id' => $department->id,
                'title' => 'Struktur Organisasi Operations',
                'summary' => 'Ringkasan struktur departemen operations.',
                'update_note' => 'Update posisi supervisor produksi.',
                'effective_at' => now()->toDateString(),
                'status' => OrganizationStructure::STATUS_PUBLISHED,
                'file' => UploadedFile::fake()->create('struktur-ops.pdf', 128, 'application/pdf'),
            ])
            ->assertRedirect();

        $structure = OrganizationStructure::where('title', 'Struktur Organisasi Operations')->firstOrFail();

        $this->assertSame(OrganizationStructure::STATUS_PUBLISHED, $structure->status);
        $this->assertSame('pdf', $structure->file_type);
        $this->assertNotNull($structure->published_at);
        Storage::disk('local')->assertExists($structure->file_path);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'organization_structure.created',
            'auditable_id' => $structure->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.organization-structures.index'))
            ->assertOk()
            ->assertSee('Kelola Struktur Organisasi')
            ->assertSee('Struktur Organisasi Operations');

        $this->actingAs($admin)
            ->get(route('admin.organization-structures.create'))
            ->assertOk()
            ->assertSee('Tambah Struktur Organisasi');

        $this->actingAs($admin)
            ->get(route('admin.organization-structures.show', $structure))
            ->assertOk()
            ->assertSee('Ringkasan struktur departemen operations.');
    }

    public function test_employee_only_sees_own_department_organization_structure(): void
    {
        Storage::fake('local');
        $this->seed();

        $employee = User::where('email', 'employee@example.com')->firstOrFail();
        $operations = Department::where('code', 'OPS')->firstOrFail();
        $finance = Department::where('code', 'FIN')->firstOrFail();
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        Storage::disk('local')->put('organization-structures/ops/ops.pdf', 'ops-structure');
        Storage::disk('local')->put('organization-structures/fin/fin.pdf', 'finance-structure');

        $ownStructure = OrganizationStructure::create([
            'department_id' => $operations->id,
            'title' => 'Struktur Operations',
            'summary' => 'Ringkasan struktur Operations.',
            'update_note' => 'Update terbaru departemen Operations.',
            'file_path' => 'organization-structures/ops/ops.pdf',
            'original_file_name' => 'ops.pdf',
            'mime_type' => 'application/pdf',
            'file_type' => 'pdf',
            'file_size' => 100,
            'effective_at' => now()->toDateString(),
            'status' => OrganizationStructure::STATUS_PUBLISHED,
            'published_at' => now(),
            'uploaded_by' => $admin->id,
        ]);

        $otherStructure = OrganizationStructure::create([
            'department_id' => $finance->id,
            'title' => 'Struktur Finance',
            'summary' => 'Ringkasan struktur Finance.',
            'update_note' => 'Update terbaru departemen Finance.',
            'file_path' => 'organization-structures/fin/fin.pdf',
            'original_file_name' => 'fin.pdf',
            'mime_type' => 'application/pdf',
            'file_type' => 'pdf',
            'file_size' => 100,
            'effective_at' => now()->toDateString(),
            'status' => OrganizationStructure::STATUS_PUBLISHED,
            'published_at' => now(),
            'uploaded_by' => $admin->id,
        ]);

        $this->actingAs($employee)
            ->get(route('organization-structure.index'))
            ->assertOk()
            ->assertSee('Struktur Operations')
            ->assertSee('Ringkasan struktur Operations.')
            ->assertDontSee('Struktur Finance');

        $this->actingAs($employee)
            ->get(route('organization-structures.file', $ownStructure))
            ->assertOk();

        $this->actingAs($employee)
            ->get(route('organization-structures.file', $otherStructure))
            ->assertNotFound();
    }

    public function test_success_flash_uses_green_alert_style(): void
    {
        $this->seed();

        $employee = User::where('email', 'employee@example.com')->firstOrFail();

        $this->actingAs($employee)
            ->withSession(['status' => 'Dokumen berhasil disimpan.'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('border-green-200 bg-green-50', false)
            ->assertSee('text-green-800', false);
    }

    public function test_warning_flash_uses_red_alert_style(): void
    {
        $this->seed();

        $employee = User::where('email', 'employee@example.com')->firstOrFail();

        $this->actingAs($employee)
            ->withSession(['warning' => 'Terjadi peringatan.'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('border-red-200 bg-red-50', false)
            ->assertSee('text-red-800', false);
    }

    public function test_validation_errors_use_red_alert_style(): void
    {
        $this->withViewErrors(['email' => 'Email wajib diisi.'])
            ->view('auth.login')
            ->assertSee('border-red-200 bg-red-50', false)
            ->assertSee('text-red-800', false)
            ->assertSee('Periksa input berikut:');
    }

    private function validDocumentPayload(array $overrides = []): array
    {
        return array_merge([
            'document_number' => 'SOP-IT-777',
            'title' => 'Pengelolaan Akses Sistem Internal',
            'type_id' => DocumentType::where('code', 'SOP')->value('id'),
            'department_id' => Department::where('code', 'IT')->value('id'),
            'category_id' => Category::where('name', 'Teknologi')->value('id'),
            'summary' => 'Prosedur pemberian, perubahan, dan pencabutan akses aplikasi internal.',
            'status' => 'draft',
            'version' => '1.0',
            'url' => 'https://docs.google.com/document/d/example-sop-it-777',
            'effective_at' => now()->toDateString(),
            'review_at' => now()->addYear()->toDateString(),
            'expired_at' => now()->addYears(2)->toDateString(),
            'change_summary' => 'Versi awal',
        ], $overrides);
    }
}
