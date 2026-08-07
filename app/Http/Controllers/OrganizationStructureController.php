<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\OrganizationStructure;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrganizationStructureController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $departments = Department::query()
            ->where('active', true)
            ->when(! $user->canViewAllPublishedDocuments(), fn ($query) => $query->whereKey($user->accessibleDepartmentIds() ?: [0]))
            ->orderBy('name')
            ->get();

        $requestedDepartmentId = (int) $request->query('department_id');
        $departmentId = $requestedDepartmentId && $departments->contains('id', $requestedDepartmentId)
            ? $requestedDepartmentId
            : (int) ($departments->first()?->id ?? 0);

        $currentStructure = null;
        $history = collect();

        if ($departmentId) {
            $query = OrganizationStructure::query()
                ->with(['department', 'uploader'])
                ->visibleToUser($user)
                ->where('department_id', $departmentId)
                ->latest('effective_at')
                ->latest();

            $currentStructure = (clone $query)->first();
            $history = $query->limit(8)->get();
        }

        return view('organization-structures.index', [
            'currentStructure' => $currentStructure,
            'history' => $history,
            'departments' => $departments,
            'selectedDepartmentId' => $departmentId,
            'canSelectDepartment' => $departments->count() > 1,
        ]);
    }

    public function file(Request $request, OrganizationStructure $organizationStructure): BinaryFileResponse
    {
        $user = $request->user();

        abort_unless($organizationStructure->isVisibleTo($user) || $user->canManageDocuments(), 404);
        abort_unless(Storage::disk('local')->exists($organizationStructure->file_path), 404);

        AuditLogger::record('organization_structure.opened', $organizationStructure, [], [
            'department_id' => $organizationStructure->department_id,
            'title' => $organizationStructure->title,
        ]);

        return response()->file(Storage::disk('local')->path($organizationStructure->file_path), [
            'Content-Type' => $organizationStructure->mime_type,
            'Content-Disposition' => 'inline; filename="'.$organizationStructure->original_file_name.'"',
        ]);
    }
}
