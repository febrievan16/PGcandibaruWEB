<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'caption',
    'image_url',
    'photos',
    'category',
    'year_label',
    'sort_order',
    'is_active',
])]
class GalleryItem extends Model
{
    /**
     * In-memory cache of computed gallery slides for this model instance.
     *
     * @var array<int, array{url: string, caption: string}>|null
     */
    protected ?array $cachedSlides = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Return all photo URLs (or generated archival SVG data URIs if no image files were uploaded) for the popup viewer.
     *
     * @return array<int, array{url: string, caption: string}>
     */
    public function gallerySlides(): array
    {
        if ($this->cachedSlides !== null) {
            return $this->cachedSlides;
        }

        $urls = [];

        if (! empty($this->image_url)) {
            $urls[] = $this->image_url;
        }

        if (is_array($this->photos)) {
            foreach ($this->photos as $photoUrl) {
                if (is_string($photoUrl) && $photoUrl !== '' && ! in_array($photoUrl, $urls, true)) {
                    $urls[] = $photoUrl;
                }
            }
        }

        if (count($urls) > 0) {
            $slides = [];
            foreach ($urls as $index => $url) {
                $slides[] = [
                    'url' => $url,
                    'caption' => $this->title . ' — Foto Dokumentasi #' . ($index + 1),
                ];
            }

            return $this->cachedSlides = $slides;
        }

        // Default multi-photo archival SVG plates when a gallery item uses heritage illustrations
        $angles = [
            'Tampak Utama & Struktur Bangunan',
            'Detail Mekanisme & Konstruksi Klasik',
            'Suasana Operasional & Dokumentasi Lapangan',
        ];

        $slides = [];
        foreach ($angles as $idx => $angleLabel) {
            $slides[] = [
                'url' => $this->buildArchivalSvgDataUri($idx + 1, $angleLabel),
                'caption' => "{$this->title} — {$angleLabel}",
            ];
        }

        return $this->cachedSlides = $slides;
    }

    /**
     * Build a clean SVG data URI representing a specific archival plate angle.
     */
    protected function buildArchivalSvgDataUri(int $plateNumber, string $angleLabel): string
    {
        $title = htmlspecialchars($this->title, ENT_QUOTES, 'UTF-8');
        $category = htmlspecialchars(strtoupper($this->category), ENT_QUOTES, 'UTF-8');
        $year = htmlspecialchars($this->year_label ?? 'Arsip PG Candi Baru', ENT_QUOTES, 'UTF-8');
        $angle = htmlspecialchars($angleLabel, ENT_QUOTES, 'UTF-8');

        $graphic = match ($plateNumber) {
            1 => '<polygon points="150,230 270,130 390,230" fill="#A67C52" stroke="#6B3F1E" stroke-width="3"/><rect x="165" y="230" width="210" height="110" fill="#FAF6EE" stroke="#6B3F1E" stroke-width="3"/><path d="M450 95 L480 95 L492 340 L438 340 Z" fill="#6B3F1E"/>',
            2 => '<circle cx="360" cy="210" r="78" fill="none" stroke="#6B3F1E" stroke-width="7"/><circle cx="360" cy="210" r="20" fill="#C8A24A" stroke="#6B3F1E" stroke-width="4"/><line x1="282" y1="210" x2="438" y2="210" stroke="#6B3F1E" stroke-width="4"/><line x1="360" y1="132" x2="360" y2="288" stroke="#6B3F1E" stroke-width="4"/>',
            default => '<line x1="80" y1="290" x2="640" y2="290" stroke="#2A211A" stroke-width="5"/><rect x="160" y="195" width="160" height="90" fill="#A67C52" stroke="#6B3F1E" stroke-width="3"/><rect x="360" y="195" width="160" height="90" fill="#FAF6EE" stroke="#6B3F1E" stroke-width="3"/><circle cx="200" cy="290" r="16" fill="#6B3F1E"/><circle cx="280" cy="290" r="16" fill="#6B3F1E"/><circle cx="400" cy="290" r="16" fill="#6B3F1E"/><circle cx="480" cy="290" r="16" fill="#6B3F1E"/>',
        };

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 440">
    <rect width="720" height="440" fill="#F3EAD8"/>
    <rect x="24" y="24" width="672" height="392" fill="none" stroke="#6B3F1E" stroke-width="2"/>
    <rect x="32" y="32" width="656" height="376" fill="none" stroke="#E3D9C9" stroke-width="1"/>
    <text x="54" y="64" fill="#A67C52" font-family="sans-serif" font-size="12" font-weight="bold" letter-spacing="2">{$category} • {$year} • PELAT #{$plateNumber}</text>
    {$graphic}
    <text x="360" y="370" text-anchor="middle" fill="#2A211A" font-family="Georgia, serif" font-size="18" font-weight="bold">{$title}</text>
    <text x="360" y="395" text-anchor="middle" fill="#6B3F1E" font-family="sans-serif" font-size="13">{$angle}</text>
</svg>
SVG;

        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }
}
