<?php

return [
    // config here
    'key' => 'CpmLVtjV8diGbhEsVD3IWoVOn31pRpmupEcxMCgtXp9LGpe39F',
    // return DateInterval https://www.php.net/manual/en/class.dateinterval.php
    'expire_in' => [
        'token' =>  'P1D', 
        'refresh_token' => 'P1M',
        'personal_token' => 'P1M'
    ],
    'scopes' => [
        'public' => 'read all public resource'
    ],
    'use_otp_grant' => false,
    'provider' => 'default', // connection provider
    'user_table' => 'users', // user table 
    'find_by' => 'email' // username check
];
