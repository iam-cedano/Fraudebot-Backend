<?php

namespace App\Models;

use App\Domain\Report\ReportEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'was_sucessful',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::deleted(function (Report $report) {
            ReportProduct::query()->where('report_id', $report->id)->delete();
        });

        static::restoring(function (Report $report) {
            ReportProduct::onlyTrashed()->where('report_id', $report->id)->restore();
        });
    }

    /**
     * Get the products associated with the report.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'reports_products')
            ->using(ReportProduct::class)
            ->withTimestamps()
            ->withPivot('deleted_at')
            ->wherePivotNull('deleted_at');
    }

    /**
     * Get the user that owns the report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organizations_reports')
            ->using(OrganizationReport::class)
            ->withTimestamps()
            ->withPivot('deleted_at')
            ->wherePivotNull('deleted_at');
    }

    public function scammers(): BelongsToMany
    {
        return $this->belongsToMany(Scammer::class, 'scammers_reports')
            ->using(ScammerReport::class)
            ->withTimestamps()
            ->withPivot('deleted_at')
            ->wherePivotNull('deleted_at');
    }

    /**
     * Convert the model to a domain entity.
     */
    public function toEntity(): ReportEntity
    {
        return new ReportEntity(
            id: $this->id,
            userId: $this->user_id,
            title: $this->title,
            description: $this->description,
            wasSucessful: $this->was_sucessful,
            isActive: $this->is_active,
        );
    }
}
