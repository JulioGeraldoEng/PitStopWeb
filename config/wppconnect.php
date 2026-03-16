<?php
// config/wppconnect.php

return [
    /*
    |--------------------------------------------------------------------------
    | WPPConnect Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para integração com o WPPConnect Server
    |
    */

    'url' => env('WPPCONNECT_URL', 'http://localhost:21465'),
    'secret_key' => env('WPPCONNECT_SECRET_KEY', 'THISISMYSECURETOKEN'),
    'session' => env('WPPCONNECT_SESSION', 'pitstop'),
    
    // Token de autorização (usando secret_key)
    'token' => env('WPPCONNECT_SECRET_KEY'),
    
    // Timeout para requisições (segundos)
    'timeout' => 30,
];