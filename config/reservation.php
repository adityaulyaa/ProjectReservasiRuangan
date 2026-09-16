<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Jam Operasional
    |--------------------------------------------------------------------------
    */

    'open_time' => env('RESERVATION_OPEN_TIME', '07:00'),
    'close_time' => env('RESERVATION_CLOSE_TIME', '20:00'),
    'slot_minutes' => env('RESERVATION_SLOT_MINUTES', 30),
    'cancel_hours_before' => env('RESERVATION_CANCEL_HOURS_BEFORE', 2),
    'max_duration_hours' => env('RESERVATION_MAX_DURATION_HOURS', 8),

    /*
    |--------------------------------------------------------------------------
    | Upload Foto Laporan
    |--------------------------------------------------------------------------
    */

    'photo_max_kb' => env('REPORT_PHOTO_MAX_KB', 2048),
    'photo_mimes' => ['jpg', 'jpeg', 'png'],

    /*
    |--------------------------------------------------------------------------
    | Kategori Laporan Kerusakan
    |--------------------------------------------------------------------------
    */

    'report_categories' => [
        'listrik',
        'ac',
        'furniture',
        'plumbing',
        'it',
        'lainnya',
    ],

];
