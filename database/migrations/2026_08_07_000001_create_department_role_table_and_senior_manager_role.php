<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_role', function (Blueprint $table): void {
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['department_id', 'role_id']);
        });

        $permissionId = $this->updateOrCreatePermission('view-published-documents', 'Lihat dokumen published');
        $roleId = $this->updateOrCreateRole(
            'senior-manager',
            'Senior Manager',
            'Senior Manager dengan cakupan akses beberapa departemen atau divisi.'
        );

        DB::table('permission_role')->insertOrIgnore([
            'permission_id' => $permissionId,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $roleId = DB::table('roles')->where('name', 'senior-manager')->value('id');

        if ($roleId) {
            DB::table('permission_role')->where('role_id', $roleId)->delete();
            DB::table('role_user')->where('role_id', $roleId)->delete();
            DB::table('department_role')->where('role_id', $roleId)->delete();
            DB::table('roles')->where('id', $roleId)->delete();
        }

        Schema::dropIfExists('department_role');
    }

    private function updateOrCreatePermission(string $name, string $displayName): int
    {
        $permissionId = DB::table('permissions')->where('name', $name)->value('id');
        $timestamp = now();

        if ($permissionId) {
            DB::table('permissions')->where('id', $permissionId)->update([
                'display_name' => $displayName,
                'updated_at' => $timestamp,
            ]);

            return (int) $permissionId;
        }

        return DB::table('permissions')->insertGetId([
            'name' => $name,
            'display_name' => $displayName,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    private function updateOrCreateRole(string $name, string $displayName, string $description): int
    {
        $roleId = DB::table('roles')->where('name', $name)->value('id');
        $timestamp = now();

        if ($roleId) {
            DB::table('roles')->where('id', $roleId)->update([
                'display_name' => $displayName,
                'description' => $description,
                'updated_at' => $timestamp,
            ]);

            return (int) $roleId;
        }

        return DB::table('roles')->insertGetId([
            'name' => $name,
            'display_name' => $displayName,
            'description' => $description,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
};
