<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'document_id',
    'version',
    'url',
    'effective_at',
    'review_at',
    'expired_at',
    'change_summary',
    'status',
    'published_at',
    'created_by',
])]
class DocumentVersion extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_SUPERSEDED = 'superseded';
    public const STATUS_ARCHIVED = 'archived';

    protected function casts(): array
    {
        return [
            'effective_at' => 'date',
            'review_at' => 'date',
            'expired_at' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
