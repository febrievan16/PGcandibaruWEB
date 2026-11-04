<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

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

