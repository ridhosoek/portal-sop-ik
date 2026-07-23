<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->updateOrCreatePermission(
            'view-all-published-documents',
            'Lihat semua dokumen published lintas departemen'
        );

        $this->updateOrCreateRole('bod', 'BOD', 'Board of Directors dengan akses baca semua SOP dan IK published.');

        foreach (['bod', 'document-admin', 'super-admin', 'auditor'] as $roleName) {
            $this->attachPermission($roleName, 'view-all-published-documents');
        }

        $this->attachPermission('document-admin', 'manage-users');
    }

    public function down(): void
    {
        $bodRoleId = DB::table('roles')->where('name', 'bod')->value('id');

        if ($bodRoleId) {
            DB::table('permission_role')->where('role_id', $bodRoleId)->delete();
            DB::table('role_user')->where('role_id', $bodRoleId)->delete();
            DB::table('roles')->where('id', $bodRoleId)->delete();
        }

        $viewAllPermissionId = DB::table('permissions')->where('name', 'view-all-published-documents')->value('id');

        if ($viewAllPermissionId) {
            $roleIds = DB::table('roles')
                ->whereIn('name', ['document-admin', 'super-admin', 'auditor'])
                ->pluck('id');

            DB::table('permission_role')
                ->where('permission_id', $viewAllPermissionId)
                ->whereIn('role_id', $roleIds)
                ->delete();
        }

        $manageUsersPermissionId = DB::table('permissions')->where('name', 'manage-users')->value('id');
        $documentAdminRoleId = DB::table('roles')->where('name', 'document-admin')->value('id');

        if ($manageUsersPermissionId && $documentAdminRoleId) {
            DB::table('permission_role')
                ->where('permission_id', $manageUsersPermissionId)
                ->where('role_id', $documentAdminRoleId)
                ->delete();
        }
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

    private function attachPermission(string $roleName, string $permissionName): void
    {
        $roleId = DB::table('roles')->where('name', $roleName)->value('id');
        $permissionId = DB::table('permissions')->where('name', $permissionName)->value('id');

        if (! $roleId || ! $permissionId) {
            return;
        }

        DB::table('permission_role')->insertOrIgnore([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
