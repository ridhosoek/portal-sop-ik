<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'document_number',
    'title',
    'type_id',
    'department_id',
    'category_id',
    'owner_user_id',
    'owner_name',
    'summary',
    'status',
    'published_at',
    'archived_at',
    'created_by',
    'updated_by',
])]
class Document extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_ARCHIVED = 'archived';

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'type_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->latest('created_at');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(DocumentVersion::class)->latestOfMany();
    }

    public function activeVersion(): HasOne
    {
        return $this->hasOne(DocumentVersion::class)
            ->where('status', DocumentVersion::STATUS_PUBLISHED)
            ->latestOfMany('published_at');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function brokenLinkReports(): HasMany
    {
        return $this->hasMany(BrokenLinkReport::class);
    }

    public function scopePublishedAndEffective(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->whereHas('versions', function (Builder $query) use ($today): void {
                $query->where('status', DocumentVersion::STATUS_PUBLISHED)
                    ->whereDate('effective_at', '<=', $today)
                    ->where(function (Builder $query) use ($today): void {
                        $query->whereNull('expired_at')
                            ->orWhereDate('expired_at', '>=', $today);
                    });
            });
    }

    public function scopeVisibleToEmployees(Builder $query): Builder
    {
        return $query->publishedAndEffective();
    }

    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        $query->publishedAndEffective();

        if ($user->canViewAllPublishedDocuments()) {
            return $query;
        }

        if (! $user->department_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('department_id', $user->department_id);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term): void {
            $query->where('document_number', 'like', "%{$term}%")
                ->orWhere('title', 'like', "%{$term}%")
                ->orWhere('summary', 'like', "%{$term}%")
                ->orWhereHas('tags', fn (Builder $tagQuery) => $tagQuery->where('name', 'like', "%{$term}%"));
        });
    }

    public function isVisibleToEmployee(): bool
    {
        return self::query()
            ->whereKey($this->getKey())
            ->visibleToEmployees()
            ->exists();
    }

    public function isVisibleTo(User $user): bool
    {
        return self::query()
            ->whereKey($this->getKey())
            ->visibleToUser($user)
            ->exists();
    }
}
