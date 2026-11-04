<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable([
    'company_name',
    'tagline',
    'established_year',
    'history',
    'vision',
    'mission',
    'address',
    'phone',
    'whatsapp',
    'email',
    'operating_hours',
    'map_embed_url',
    'hero_image',
])]
class CompanyProfile extends Model
{
    public const CACHE_KEY = 'gulatour.company_profile.v2';

    /**
     * Flush cached company profile whenever the record is updated or deleted.
     */
    protected static function booted(): void
    {
        static::saved(function (): void {
            Cache::forget(self::CACHE_KEY);
            Cache::forget('gulatour.company_profile');
        });

        static::deleted(function (): void {
            Cache::forget(self::CACHE_KEY);
            Cache::forget('gulatour.company_profile');
        });
    }

    /**
     * Retrieve the singleton CompanyProfile using plain array caching (compatible with serializable_classes = false).
     */
    public static function current(): ?self
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (is_array($cached)) {
            return (new self)->newFromBuilder($cached);
        }

        $model = self::query()->first();

        if ($model !== null) {
            Cache::put(self::CACHE_KEY, $model->getAttributes(), 3600);
        }

        return $model;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'established_year' => 'integer',
            'mission' => 'array',
        ];
    }
}

