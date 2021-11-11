<?php

return [
    // config here
    'key' => 'CpmLVtjV8diGbhEsVD3IWoVOn31pRpmupEcxMCgtXp9LGpe39F',
    'scopes' => [
        'public' => 'read all public resource'
    ],
    'use_otp_grant' => false,
    'provider' => 'default', // connection provider
    'user_table' => 'users', // user table 
    'find_by' => 'email' // username check
];
