<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ctevt_result' => [
        'check_url' => env('CTEVT_CHECK_URL', 'https://itms.ctevt.org.np:5580/check_results'),
        'url' => env('CTEVT_RESULT_URL', 'https://itms.ctevt.org.np:5580/search_results'),
    ],

    'ctevt_notice' => [
        'general_url' => env('CTEVT_GENERAL_NOTICE_URL', 'https://itms.ctevt.org.np:5580/notices'),
        'result_url' => env('CTEVT_RESULT_NOTICE_URL', 'https://itms.ctevt.org.np:5580/notices/result'),
        'feed_url' => env('CTEVT_NOTICE_FEED_URL', 'https://itms.ctevt.org.np:5580/notices/get-ajax-notices'),
    ],

];
