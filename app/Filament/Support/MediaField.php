<?php

namespace App\Filament\Support;

use App\Models\Media;
use App\Support\MediaLibrary;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaField
{
    /** An image upload that stores into the media library. Chain more options on it as usual. */
    public static function upload(string $name, bool $multiple = false): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->multiple($multiple)
            ->disk('public')
            ->visibility('public')
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxSize(1024)
            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(MediaLibrary::class)->store($file))
            // Taking an image out of a field must never delete it from the library
            ->deleteUploadedFileUsing(fn () => null);
    }

    /** A "Choose from media library" button that fills the given upload field. */
    public static function picker(string $field, bool $multiple = false, int $max = 1): Action
    {
        return Action::make('pick_' . str_replace('.', '_', $field))
            ->label('Choose from media library')
            ->icon('heroicon-o-photo')
            ->color('gray')
            ->slideOver()
            ->modalHeading('Media library')
            ->modalSubmitActionLabel('Use selected')
            ->schema([
                CheckboxList::make('selected')
                    ->hiddenLabel()
                    ->options(fn () => self::options())
                    ->allowHtml()
                    ->searchable()
                    ->noSearchResultsMessage('Nothing found. Upload images in the Media library first.')
                    ->columns(2)
                    ->required()
                    ->maxItems($multiple ? $max : 1)
                    ->helperText($multiple ? "Choose up to {$max} images." : 'Choose one image.'),
            ])
            ->action(function (array $data, Get $get, Set $set) use ($field, $multiple, $max) {
                // Re-checked on the server: only THIS store's own media can ever be chosen
                $paths = Media::where('store_id', Filament::getTenant()->id)
                    ->whereIn('id', Arr::wrap($data['selected'] ?? []))
                    ->pluck('path')
                    ->all();

                if (! $paths) {
                    return;
                }

                if (! $multiple) {
                    $set($field, [(string) Str::uuid() => $paths[0]]);
                } else {
                    $current = array_filter((array) ($get($field) ?? []));
                    $existing = array_values(array_filter($current, 'is_string'));

                    foreach ($paths as $path) {
                        if (! in_array($path, $existing, true)) {
                            $current[(string) Str::uuid()] = $path;
                        }
                    }

                    if (count($current) > $max) {
                        Notification::make()->danger()->title("You can use up to {$max} images here.")->send();

                        return;
                    }

                    $set($field, $current);
                }

                Notification::make()->success()->title('Added. Remember to save.')->send();
            });
    }

    private static function options(): array
    {
        return Media::where('store_id', Filament::getTenant()->id)
            ->latest()
            ->limit(200)
            ->get()
            ->mapWithKeys(fn (Media $media) => [
                $media->id => '<div style="display:flex;flex-direction:column;gap:6px;width:100%;">'
                    . '<img src="' . e($media->url()) . '" alt="" loading="lazy" '
                    . 'style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:6px;background:#f3f4f6;">'
                    . '<span style="font-size:12px;line-height:1.3;overflow-wrap:anywhere;">' . e($media->name) . '</span>'
                    . '</div>',
            ])
            ->all();
    }
}