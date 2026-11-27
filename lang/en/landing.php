<?php

return [
    'meta' => [
        'title' => ':brand: your online store, built for Bangladeshi sellers',
        'description' => 'Open your own online store in minutes. Products with variations, cash on delivery, Steadfast courier, Google SEO and more. Free for :days days, then ৳:price a month.',
    ],

    'nav' => [
        'features' => 'Features',
        'pricing' => 'Pricing',
        'faq' => 'Questions',
        'login' => 'Log in',
        'cta' => 'Start free trial',
        'switch' => 'বাংলা',
    ],

    'hero' => [
        'title' => 'Take orders in your own store, not your inbox',
        'subtitle' => 'Add your products and share one link. Customers see the price, sizes and delivery charge, then place the order themselves.',
        'cta' => 'Start free trial',
        'secondary' => 'See what you get',
        'note' => 'Every feature free for :days days, then ৳:price a month.',
    ],

    'demo' => [
        'store' => 'Rahima Fashion',
        'url' => 'rahima-fashion.:domain',
        'products' => [
            ['name' => 'Cotton panjabi', 'price' => '৳1,200'],
            ['name' => 'Jamdani saree', 'price' => '৳3,500'],
            ['name' => 'Jute bag', 'price' => '৳800'],
            ['name' => 'Leather sandals', 'price' => '৳1,100'],
        ],
        'desk_title' => 'Orders today',
        'desk_live' => 'Live',
        'orders' => [
            ['no' => '#12', 'name' => 'Rahima', 'total' => '৳1,260', 'status' => 'New order', 'tone' => 'new'],
            ['no' => '#11', 'name' => 'Karim', 'total' => '৳2,450', 'status' => 'Confirmed', 'tone' => 'confirmed'],
            ['no' => '#10', 'name' => 'Nasrin', 'total' => '৳890', 'status' => 'With courier', 'tone' => 'shipped'],
        ],
    ],

    'inbox' => [
        'title' => 'Stop answering the same questions all day',
        'text' => 'Almost every message in your inbox asks for the same few things. Your store page answers them before anyone has to ask.',
        'inbox_label' => 'Your inbox today',
        'store_label' => 'Your store page',
        'chat' => ['How much is this?', 'Is size L available?', "What's the delivery charge?", 'Is cash on delivery okay?', 'Please inbox me'],
        'product' => 'Cotton panjabi',
        'price' => '৳1,200',
        'old' => '৳1,500',
        'badge' => '-20%',
        'size' => 'Size',
        'sizes' => ['S', 'M', 'L', 'XL'],
        'sold_out' => 'XL',
        'delivery' => ['Inside Dhaka ৳60', 'Outside Dhaka ৳120', 'Cash on delivery'],
        'button' => 'Add to cart',
    ],

    'features_title' => 'Everything you need to run the store',
    'features' => [
        [
            'title' => 'A store that looks like yours',
            'text' => 'Pick a theme made for what you sell, set your colour and logo, and put a banner on your home page.',
            'points' => [
                'Three themes: Classic, Fresh and Runway',
                'Your brand colour, logo and announcement bar',
                'Home page banner, featured categories and product shelves',
            ],
        ],
        [
            'title' => 'Products customers can choose from',
            'text' => "Show every size, colour or weight with its own stock and price, so nobody orders something you don't have.",
            'points' => [
                'Up to five photos, descriptions and sale prices',
                'Variations like size and colour, each with its own stock',
                'A media library that keeps all your pictures in one place',
            ],
        ],
        [
            'title' => 'Orders you can trust',
            'text' => 'Every order lands in one list with its status. Stock goes down by itself, and comes back if you cancel.',
            'points' => [
                'Cash on delivery with separate inside and outside Dhaka charges',
                'Free delivery over an amount you choose',
                "Block fake-order numbers and see each customer's earlier orders",
            ],
        ],
        [
            'title' => 'Send parcels from the order itself',
            'text' => 'Connect your own Steadfast account and book a parcel without leaving the order page.',
            'points' => [
                'Send one order, or many at once',
                'Tracking code and delivery status come back to your dashboard',
                'Request a return and see your Steadfast balance',
            ],
        ],
        [
            'title' => 'Get found, and see what works',
            'text' => 'Search engines and social networks read your store properly, and you can see where your visitors come from.',
            'points' => [
                'Google-ready titles, descriptions and a sitemap for every store',
                'Good-looking previews when you share a link on Facebook or WhatsApp',
                'Facebook Pixel and Google Analytics connected with your own IDs',
            ],
        ],
        [
            'title' => 'Run it with your team',
            'text' => 'Give staff their own logins, each with only the access they need.',
            'points' => [
                'Owner, manager and order staff roles',
                "A dashboard with today's orders, sales and low stock",
                'A WhatsApp chat button on your store',
            ],
        ],
    ],

    'mock' => [
        'themes' => ['Classic', 'Fresh', 'Runway'],
        'photos' => 'Photos',
        'size' => 'Size',
        'colour' => 'Colour',
        'colours' => ['Red', 'Blue'],
        'variations' => '12 variations, each with its own stock',
        'sale' => '৳1,200',
        'sale_old' => '৳1,500',
        'orders_head' => ['Order', 'Area', 'Total', 'Status'],
        'orders' => [
            ['#14', 'Sumi', 'Inside Dhaka', '৳1,120', 'Pending'],
            ['#13', 'Jashim', 'Outside Dhaka', '৳2,840', 'Confirmed'],
            ['#12', 'Rahima', 'Inside Dhaka', '৳1,260', 'Shipped'],
        ],
        'warn' => '3 of this customer\'s 4 earlier orders were cancelled',
        'courier_order' => '#11 Karim',
        'tracking' => 'Tracking code',
        'tracking_code' => 'SF48K2M9QX',
        'steps' => ['Booked', 'On the way', 'Delivered'],
        'google' => 'On Google',
        'share' => 'When you share a link',
        'seo_title' => 'Cotton panjabi | Rahima Fashion',
        'seo_desc' => 'Soft cotton panjabi in five sizes. Cash on delivery all over Bangladesh.',
        'connected' => 'Connected',
        'pixel' => 'Facebook Pixel',
        'analytics' => 'Google Analytics',
        'roles_head' => ['Owner', 'Manager', 'Order staff'],
        'roles' => [
            ['Orders', 1, 1, 1],
            ['Products and categories', 1, 1, 0],
            ['Store settings and team', 1, 0, 0],
        ],
        'yes' => 'Yes',
        'no' => 'No',
    ],

    'more' => [
        'title' => 'And the small things a shop needs',
        'items' => [
            'Search for your customers',
            'Works well on any phone',
            'Quick cart and checkout',
            'Customer notes on every order',
            'Low stock warnings',
            'An announcement bar for offers',
            'Hide products until they are ready',
            'Related products on every product page',
            'Delivery charge by area at checkout',
            'A separate store address for every seller',
        ],
    ],

    'steps_title' => 'Open your store in three steps',
    'steps' => [
        ['title' => 'Create your account', 'text' => 'Sign up with your email, then choose your store name and address.'],
        ['title' => 'Add your products', 'text' => 'Upload photos, set prices and stock, and pick a theme.'],
        ['title' => 'Share your link and sell', 'text' => 'Send your store link to customers and manage the orders from your dashboard.'],
    ],

    'pricing' => [
        'title' => 'Simple pricing',
        'text' => 'Every account starts with the free trial. When it ends, the monthly plan keeps your store running.',
        'trial_tag' => ':days days free',
        'trial_name' => 'Free trial',
        'trial_price' => '৳0',
        'trial_period' => 'for :days days',
        'trial_text' => 'Everything included, so you can set up a real store and take real orders.',
        'trial_cta' => 'Start free trial',
        'monthly_name' => 'Monthly plan',
        'monthly_price' => '৳:price',
        'monthly_period' => 'a month',
        'monthly_text' => 'Everything in the trial, for as long as you want to keep selling.',
        'monthly_note' => 'Starts when your free trial ends.',
        'included_title' => 'Everything is included in both',
        'included' => [
            'Your own store address',
            'Three themes with your colours and logo',
            'Products with photos, variations and sale prices',
            'Cash on delivery with inside and outside Dhaka charges',
            'Order management with automatic stock',
            'Fake-order protection and customer history',
            'Steadfast courier booking and tracking',
            'Team accounts with roles',
            'A media library for all your pictures',
            'Google search tools, sitemap and share previews',
            'Facebook Pixel and Google Analytics',
            'A dashboard with sales and low-stock numbers',
        ],
    ],

    'soon' => [
        'title' => 'On the way',
        'items' => [
            'bKash online payments',
            'Your own domain name for your store',
            'A Bangla version of the storefront for your customers',
            'Customer delivery history from Steadfast',
        ],
    ],

    'faq_title' => 'Questions',
    'faq' => [
        ['q' => 'How does the free trial work?', 'a' => 'Create your store and use every feature for :days days. After that, the monthly plan is ৳:price.'],
        ['q' => 'Do I need technical skills?', 'a' => 'No. If you can use Facebook, you can add products and manage orders.'],
        ['q' => 'How do customers pay?', 'a' => 'Right now customers pay cash on delivery. Online payment with bKash is on the way.'],
        ['q' => 'Do I have to use Steadfast?', 'a' => 'No. Steadfast booking is optional. You can use any courier and still manage your orders here.'],
        ['q' => 'Can my staff use it too?', 'a' => 'Yes. Add managers or order staff with their own logins and limited access.'],
        ['q' => 'Can I use my own domain name?', 'a' => 'Not yet. For now every store gets its own address on our platform, and custom domains are on the way.'],
        ['q' => "Is my store's information kept private?", 'a' => "Each store's products, orders and customers are kept separate from every other store, and only your own team can open them."],
    ],

    'final' => [
        'title' => 'Open your store and take your first order',
        'text' => 'Free for :days days. Then ৳:price a month.',
        'cta' => 'Start free trial',
    ],

    'footer' => [
        'rights' => 'All rights reserved.',
    ],
];