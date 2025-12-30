<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Service Account Credentials Path
    |--------------------------------------------------------------------------
    |
    | This is the path to your Firebase service account JSON file.
    | Download it from Firebase Console -> Project Settings -> Service Accounts
    |
    */
    'credentials_path' => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase/credentials.json')),
];
