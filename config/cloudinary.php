<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Cloudinary image and video management service.
    | Used for uploading, storing, and optimizing media assets.
    |
    */

    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Image Optimization Settings
    |--------------------------------------------------------------------------
    */

    'image' => [
        'format' => 'webp', // Default format for optimization
        'quality' => 'auto', // Auto-detect quality
        'responsive' => true,
        'crop' => 'fill', // Auto crop strategy
        'gravity' => 'auto', // Auto detect subject
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Presets
    |--------------------------------------------------------------------------
    */

    'upload_preset' => [
        'car_images' => env('CLOUDINARY_CAR_PRESET', 'khanza_cars'),
        'service_images' => env('CLOUDINARY_SERVICE_PRESET', 'khanza_services'),
        'profile_images' => env('CLOUDINARY_PROFILE_PRESET', 'khanza_profiles'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Folder Organization
    |--------------------------------------------------------------------------
    */

    'folder' => [
        'cars' => 'khanza/garage/cars',
        'services' => 'khanza/services',
        'profiles' => 'khanza/profiles',
        'testimonials' => 'khanza/testimonials',
    ],

    /*
    |--------------------------------------------------------------------------
    | File Size Limits (in bytes)
    |--------------------------------------------------------------------------
    */

    'max_file_size' => [
        'image' => 5 * 1024 * 1024, // 5MB
        'video' => 50 * 1024 * 1024, // 50MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Formats
    |--------------------------------------------------------------------------
    */

    'allowed_formats' => [
        'image' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'video' => ['mp4', 'webm', 'mov'],
    ],

    /*
    |--------------------------------------------------------------------------
    | CDN Configuration
    |--------------------------------------------------------------------------
    */

    'cdn' => [
        'secure' => true, // Use HTTPS
        'cache_control' => 'max-age=31536000', // 1 year cache
    ],
];
