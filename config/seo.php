<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site Identity
    |--------------------------------------------------------------------------
    */
    'site_name'        => env('SEO_SITE_NAME',  'Manmohan Memorial Polytechnic'),
    'short_name'       => env('SEO_SHORT_NAME', 'MMP'),
    'tagline'          => 'Best Technical College in Koshi Province, Nepal',
    'url'              => env('APP_URL', 'https://www.mmp.edu.np'),
    'locale'           => 'en_US',
    'language'         => 'en',

    /*
    |--------------------------------------------------------------------------
    | Default Meta
    |--------------------------------------------------------------------------
    */
    'default_title'       => 'Manmohan Memorial Polytechnic | Best Technical College — Koshi Province, Nepal',
    'title_suffix'        => ' | MMP — Manmohan Memorial Polytechnic',
    'default_description' => 'Manmohan Memorial Polytechnic (MMP) is the leading CTEVT-affiliated technical college in Morang, Koshi Province, Nepal. Offering diploma programs in Engineering, IT, Electrical, Civil, Mechanical & Electronics.',
    'default_keywords'    => 'Manmohan Memorial Polytechnic, MMP, technical college Nepal, CTEVT, diploma engineering, Koshi Province, Morang Nepal, polytechnic Nepal, mmp.edu.np, engineering college Biratnagar',
    'default_author'      => 'Manmohan Memorial Polytechnic',
    'default_robots'      => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',

    /*
    |--------------------------------------------------------------------------
    | Default OG Image
    |--------------------------------------------------------------------------
    */
    'default_og_image'      => '/images/seo-og-default.jpg',
    'default_og_image_width'  => 1200,
    'default_og_image_height' => 630,

    /*
    |--------------------------------------------------------------------------
    | Organization / Local Business
    |--------------------------------------------------------------------------
    */
    'organization' => [
        'name'           => 'Manmohan Memorial Polytechnic',
        'alternate_name' => 'MMP',
        'url'            => 'https://www.mmp.edu.np',
        'logo'           => 'https://www.mmp.edu.np/brand-logo',
        'founded'        => '2008',
        'telephone'      => ['+977-21-590696', '+977-21-590697'],
        'email'          => 'info@mmp.edu.np',
        'address' => [
            'street'    => 'Budhiganga-4',
            'locality'  => 'Morang',
            'region'    => 'Koshi Province',
            'postal'    => '',
            'country'   => 'NP',
        ],
        'geo' => [
            'latitude'  => '26.6353',
            'longitude' => '87.2823',
        ],
        'opening_hours' => [
            'Sun-Fri 06:00-17:00',
        ],
        'social_profiles' => [
            // Add actual URLs when available
            // 'https://www.facebook.com/mmp.edu.np',
        ],
        'same_as' => [
            // 'https://www.facebook.com/mmp.edu.np',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Verification Codes
    |--------------------------------------------------------------------------
    */
    'google_verification' => env('GOOGLE_SITE_VERIFICATION', ''),
    'bing_verification'   => env('BING_SITE_VERIFICATION',   ''),

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */
    'search_url' => 'https://www.mmp.edu.np/search?q={search_term_string}',

    /*
    |--------------------------------------------------------------------------
    | Twitter
    |--------------------------------------------------------------------------
    */
    'twitter_site'    => '@mmp_edu_np',
    'twitter_creator' => '@mmp_edu_np',

];
