<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierCheck extends Model
{
    protected $fillable = ['phone', 'total', 'success', 'cancel', 'checked_at'];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /** Looks a saved check up once per request, however many table rows need it. */
    public static function lookup(int $storeId, string $phone): ?self
    {
        static $memo = [];

        return $memo[$storeId . '|' . $phone] ??= self::where('store_id', $storeId)->where('phone', $phone)->first();
    }

    public function ratio(): ?int
    {
        return $this->total > 0 ? (int) round($this->success / $this->total * 100) : null;
    }

    /** Guidance only: good, medium, poor, or little (not enough history to judge). */
    public function level(): string
    {
        if ($this->total < 3) {
            return 'little';
        }

        $ratio = $this->ratio();

        return $ratio >= 80 ? 'good' : ($ratio >= 60 ? 'medium' : 'poor');
    }

    public function summary(): string
    {
        if ($this->total === 0) {
            return 'No parcels yet';
        }

        $text = $this->ratio() . '% delivered (' . $this->success . ' of ' . $this->total . ')';

        return $this->total < 3 ? 'Little history: ' . $text : $text;
    }

    public function isFresh(): bool
    {
        return $this->checked_at->gt(now()->subDay());
    }
}