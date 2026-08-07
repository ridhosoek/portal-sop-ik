<?php

namespace App\Http\Requests;

use App\Rules\AllowedDocumentUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageDocuments() ?? false;
    }

    public function rules(): array
    {
        $document = $this->route('document');

        return [
            'document_number' => ['required', 'string', 'max:100', Rule::unique('documents', 'document_number')->ignore($document)],
            'title' => ['required', 'string', 'max:255'],
            'type_id' => ['required', 'exists:document_types,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'summary' => ['nullable', 'string'],
            'version' => ['required', 'string', 'max:30'],
            'url' => ['required', 'url', 'max:2048', new AllowedDocumentUrl()],
            'effective_at' => ['nullable', 'date'],
            'review_at' => ['nullable', 'date', 'after_or_equal:effective_at'],
            'expired_at' => ['nullable', 'date', 'after_or_equal:effective_at'],
            'change_summary' => ['nullable', 'string'],
        ];
    }
}
