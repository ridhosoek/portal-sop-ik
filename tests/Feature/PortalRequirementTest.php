<?php

namespace Tests\Feature;

use App\Models\BrokenLinkReport;
use App\Models\Category;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_document_admin_cannot_create_users(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertForbidden();
    }

    private function validDocumentPayload(array $overrides = []): array
    {
        return array_merge([
            'document_number' => 'SOP-IT-777',
            'title' => 'Pengelolaan Akses Sistem Internal',
            'type_id' => DocumentType::where('code', 'SOP')->value('id'),
            'department_id' => Department::where('code', 'IT')->value('id'),
            'category_id' => Category::where('name', 'Teknologi')->value('id'),
            'owner_name' => 'Information Technology',
            'summary' => 'Prosedur pemberian, perubahan, dan pencabutan akses aplikasi internal.',
            'status' => 'draft',
            'version' => '1.0',
            'url' => 'https://docs.google.com/document/d/example-sop-it-777',
            'effective_at' => now()->toDateString(),
            'review_at' => now()->addYear()->toDateString(),
            'expired_at' => now()->addYears(2)->toDateString(),
            'change_summary' => 'Versi awal',
            'tags' => [],
        ], $overrides);
    }
}
