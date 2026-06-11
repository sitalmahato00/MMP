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
    'url'              => env('APP_URL'),   // ← driven entirely by APP_URL in .env
    'locale'           => 'en_US',
    'language'         => 'en',

    /*
    |--------------------------------------------------------------------------
    | Default Meta
    |--------------------------------------------------------------------------
    */
    'default_title'       => 'Manmohan Memorial Polytechnic | Technical Education in Nepal',
    'title_suffix'        => ' — Manmohan Memorial Polytechnic',
    'default_description' => 'Manmohan Memorial Polytechnic (MMP) is the leading CTEVT-affiliated technical college in Morang, Koshi Province, Nepal. Offering diploma programs in Engineering, IT, Electrical, Civil, Mechanical & Electronics.',
    'default_keywords'    => 'Manmohan Memorial Polytechnic, MMP, technical college Nepal, CTEVT, diploma engineering, Koshi Province, Morang Nepal, polytechnic Nepal, engineering college Biratnagar',
    'default_author'      => 'Manmohan Memorial Polytechnic',
    'default_robots'      => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',

    /*
    |--------------------------------------------------------------------------
    | Default OG Image
    |--------------------------------------------------------------------------
    */
    'default_og_image'       => '/images/seo-og-default.jpg',
    'default_og_image_width'  => 1200,
    'default_og_image_height' => 630,

    /*
    |--------------------------------------------------------------------------
    | Organization / Local Business
    | All URLs are derived from APP_URL — never hardcoded.
    |--------------------------------------------------------------------------
    */
    'organization' => [
        'name'           => env('SEO_SITE_NAME', 'Manmohan Memorial Polytechnic'),
        'alternate_name' => env('SEO_SHORT_NAME', 'MMP'),
        'url'            => env('APP_URL'),                      // e.g. https://www.mmp.edu.np
        'logo'           => env('APP_URL') . '/brand-logo',     // served by the brand-logo route
        'founded'        => '2008',
        'telephone'      => ['+977-21-590696', '+977-21-590697'],
        'email'          => env('CONTACT_EMAIL', 'info@mmp.edu.np'),
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
            // 'https://www.facebook.com/your-page',
        ],
        'same_as' => [
            // 'https://www.facebook.com/your-page',
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
    | Search (SearchAction schema)
    | Derived from APP_URL so it always matches the deployed domain.
    |--------------------------------------------------------------------------
    */
    'search_url' => env('APP_URL') . '/search?q={search_term_string}',

    /*
    |--------------------------------------------------------------------------
    | Twitter
    |--------------------------------------------------------------------------
    */
    'twitter_site'    => env('TWITTER_SITE',    '@mmp_edu_np'),
    'twitter_creator' => env('TWITTER_CREATOR', '@mmp_edu_np'),

];
