<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $latestDocuments = Document::query()
            ->with(['type', 'department', 'category', 'activeVersion'])
            ->visibleToUser($user)
            ->latest('published_at')
            ->limit(6)
            ->get();

        return view('dashboard', [
            'latestDocuments' => $latestDocuments,
            'publishedCount' => Document::query()->visibleToUser($user)->count(),
            'typeCount' => DocumentType::query()->where('active', true)->count(),
            'departmentCount' => $this->departmentQuery($user)->count(),
            'categories' => Category::query()
                ->where('active', true)
                ->withCount(['documents' => fn ($query) => $query->visibleToUser($user)])
                ->orderBy('name')
                ->get(),
            'departments' => $this->departmentQuery($user)
                ->withCount(['documents' => fn ($query) => $query->visibleToUser($user)])
                ->orderBy('name')
                ->get(),
        ]);
    }

    private function departmentQuery($user)
    {
        return Department::query()
            ->where('active', true)
            ->when(! $user->canViewAllPublishedDocuments(), fn ($query) => $query->whereKey($user->accessibleDepartmentIds() ?: [0]));
    }
}
