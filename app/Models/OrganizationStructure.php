<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'department_id',
    'title',
    'summary',
    'update_note',
    'file_path',
    'original_file_name',
    'mime_type',
    'file_type',
    'file_size',
    'effective_at',
    'status',
    'published_at',
    'uploaded_by',
])]
class OrganizationStructure extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected function casts(): array
    {
        return [
            'effective_at' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        return $query->published()
            ->when(! $user->canReadGovernance(), fn (Builder $query) => $query->where('department_id', $user->department_id ?? 0));
    }

    public function isImage(): bool
    {
        return $this->file_type === 'image';
    }

    public function isVisibleTo(User $user): bool
    {
        if ($this->status !== self::STATUS_PUBLISHED) {
            return false;
        }

        return $user->canReadGovernance() || $this->department_id === $user->department_id;
    }
}
