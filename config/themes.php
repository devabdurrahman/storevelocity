<?php

return [
    'default' => 'classic',

    'catalog' => [
        'classic' => [
            'name' => 'Classic',
            'best_for' => 'Any shop. A clean, balanced layout, good for gadgets and general goods.',
        ],
        'fresh' => [
            'name' => 'Fresh',
            'best_for' => 'Groceries and food. Compact cards with one-tap Add to cart.',
        ],
        'runway' => [
            'name' => 'Runway',
            'best_for' => 'Fashion, beauty and lifestyle. Large photos with a clean, spacious look.',
        ],
    ],

    // Shown at signup. Each type picks a starting theme and creates starter categories.
    'store_types' => [
        'fashion' => ['label' => 'Fashion and clothing', 'theme' => 'runway', 'categories' => ['Men', 'Women', 'Kids', 'Accessories']],
        'beauty' => ['label' => 'Beauty and cosmetics', 'theme' => 'runway', 'categories' => ['Skincare', 'Makeup', 'Hair care', 'Fragrance']],
        'grocery' => ['label' => 'Groceries', 'theme' => 'fresh', 'categories' => ['Fruits', 'Vegetables', 'Meat and fish', 'Dairy and eggs', 'Rice, oil and spices']],
        'food' => ['label' => 'Homemade food', 'theme' => 'fresh', 'categories' => ['Cakes and desserts', 'Snacks', 'Pickles and sauces', 'Meals']],
        'gadgets' => ['label' => 'Gadgets and electronics', 'theme' => 'classic', 'categories' => ['Phones', 'Audio', 'Smart watches', 'Chargers and cables', 'Accessories']],
        'other' => ['label' => 'Something else', 'theme' => 'classic', 'categories' => []],
    ],
];