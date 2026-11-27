<?php

namespace App\Console\Commands;

use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportMedia extends Command
{
    protected $signature = 'media:import';
    protected $description = 'Register images already in storage in the media library';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $count = 0;

        foreach (Store::cursor() as $store) {
            foreach ($disk->allFiles("stores/{$store->id}") as $path) {
                if (! preg_match('/\.(jpe?g|png|webp)$/i', $path)) {
                    continue;
                }

                $dimensions = @getimagesize($disk->path($path)) ?: [null, null];

                $media = $store->media()->firstOrCreate(
                    ['path' => $path],
                    [
                        'disk' => 'public',
                        'name' => 'Imported image',
                        'mime' => $disk->mimeType($path) ?: 'image/jpeg',
                        'size' => $disk->size($path),
                        'width' => $dimensions[0],
                        'height' => $dimensions[1],
                    ]
                );

                $count += $media->wasRecentlyCreated ? 1 : 0;
            }
        }

        $this->info("Imported {$count} images.");

        return self::SUCCESS;
    }
}