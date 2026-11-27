<?php

namespace App\Support;

use App\Models\Store;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaLibrary
{
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    /** Saves an uploaded image into the current store's library and returns its path. */
    public function store(TemporaryUploadedFile $file): string
    {
        $store = Filament::getTenant();
        abort_unless($store instanceof Store, 403);

        // The real file type, read from the file itself, not from its name
        $mime = $file->getMimeType();
        abort_unless(in_array($mime, self::ALLOWED_TYPES, true), 422, 'Only JPG, PNG and WebP images are allowed.');

        $this->assertWithinLimits($store, (int) $file->getSize());

        $path = $file->store("stores/{$store->id}/media", 'public');

        $dimensions = @getimagesize(Storage::disk('public')->path($path)) ?: [null, null];

        $store->media()->create([
            'user_id' => auth()->id(),
            'disk' => 'public',
            'path' => $path,
            'name' => $this->cleanName($file->getClientOriginalName()),
            'mime' => $mime,
            'size' => Storage::disk('public')->size($path),
            'width' => $dimensions[0],
            'height' => $dimensions[1],
        ]);

        return $path;
    }

    public function usedBytes(Store $store): int
    {
        return (int) $store->media()->sum('size');
    }

    /**
     * The single place where plan limits will go once pricing exists, for example:
     * a total storage allowance, a file count, or a per-file size cap.
     */
    public function assertWithinLimits(Store $store, int $incomingBytes): void
    {
        // 1 MB limit (1024 * 1024 bytes)
        abort_if($incomingBytes > 1048576, 422, 'The image size cannot exceed 1 MB.');
    }

    private function cleanName(string $original): string
    {
        $name = pathinfo($original, PATHINFO_FILENAME);
        $name = trim((string) preg_replace('/[^\pL\pN\s._-]+/u', '', $name));

        return Str::limit($name !== '' ? $name : 'Image', 100, '');
    }
}