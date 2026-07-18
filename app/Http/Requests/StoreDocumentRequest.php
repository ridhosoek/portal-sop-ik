<?php

namespace App\Http\Requests;

use App\Rules\AllowedDocumentUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageDocuments() ?? false;
    }

    public function rules(): array
    {
        return [
            'document_number' => ['required', 'string', 'max:100', 'unique:documents,document_number'],
            'title' => ['required', 'string', 'max:255'],
            'type_id' => ['required', 'exists:document_types,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'version' => ['required', 'string', 'max:30'],
            'url' => ['required', 'url', 'max:2048', new AllowedDocumentUrl()],
            'effective_at' => ['required_if:status,published', 'nullable', 'date'],
            'review_at' => ['nullable', 'date', 'after_or_equal:effective_at'],
            'expired_at' => ['nullable', 'date', 'after_or_equal:effective_at'],
            'change_summary' => ['nullable', 'string'],
            'tags' => ['array'],
            'tags.*' => ['exists:tags,id'],
        ];
    }
}
