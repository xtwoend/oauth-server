<?php

use Carbon\Carbon;


return [
    // config here
    'key' => 'CpmLVtjV8diGbhEsVD3IWoVOn31pRpmupEcxMCgtXp9LGpe39F',
    'expire_in' => [
        'token' =>  Carbon::now()->addDays(7),
        'refresh_token' => Carbon::now()->addDays(30),
        'personal_token' => Carbon::now()->addDays(30)
    ],
    'scopes' => [
        'public' => 'read all public resource'
    ],
    'use_otp_grant' => false,
    'provider' => 'default', // connection provider
    'user_table' => 'users', // user table 
    'find_by' => 'email' // username check
];
