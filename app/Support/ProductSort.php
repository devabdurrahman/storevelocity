<?php

namespace App\Support;

class ProductSort
{
    public const DEFAULT = 'newest';

    /** The only sorts a visitor can choose. Anything else becomes the default. */
    public static function options(): array
    {
        return [
            'newest' => 'Newest',
            'price_asc' => 'Price: low to high',
            'price_desc' => 'Price: high to low',
            'name_asc' => 'Name: A to Z',
        ];
    }

    public static function from(?string $value): string
    {
        return $value !== null && array_key_exists($value, self::options()) ? $value : self::DEFAULT;
    }

    /** Puts the chosen order on a products query. */
    public static function apply($query, string $sort): void
    {
        match ($sort) {
            'price_asc' => self::byPrice($query, 'asc'),
            'price_desc' => self::byPrice($query, 'desc'),
            'name_asc' => $query->orderBy('products.name')->orderByDesc('products.id'),
            default => $query->orderByDesc('products.created_at')->orderByDesc('products.id'),
        };
    }

    /**
     * Sorts by what a customer pays: the lowest price of the available variations for
     * products that have them, otherwise the product's own selling price.
     */
    private static function byPrice($query, string $direction): void
    {
        $query->select('products.*')->selectRaw(<<<'SQL'
            CASE
                WHEN products.has_variants = 1
                 AND EXISTS (SELECT 1 FROM product_variants v WHERE v.product_id = products.id AND v.is_active = 1)
                THEN (SELECT MIN(COALESCE(v.price, products.price)) FROM product_variants v WHERE v.product_id = products.id AND v.is_active = 1)
                ELSE products.price
            END AS sort_price
        SQL);

        $query->orderBy('sort_price', $direction)->orderByDesc('products.id');
    }
}