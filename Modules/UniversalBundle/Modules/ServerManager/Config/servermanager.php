<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Server Manager Module Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the Server Manager module.
    |
    */

    // DNS Service Configuration
    'dns_api_key' => env('DNS_API_KEY', null),
    'dns_api_url' => env('DNS_API_URL', 'https://dns.google.com/resolve'),

    // Alternative DNS APIs (you can switch between these)
    //free, public DNS APIs (no api key required)
    'dns_apis' => [
        'google' => [
            'url' => 'https://dns.google.com/resolve', // Google DNS API
            'requires_key' => false,
        ],
        'cloudflare' => [
            'url' => 'https://cloudflare-dns.com/dns-query', // Cloudflare DNS API
            'requires_key' => false,
        ],
        'opendns' => [
            'url' => 'https://dns.opendns.com/resolve', // OpenDNS API
            'requires_key' => false,
        ],
    ],

    // DNS Record Types to fetch
    'dns_record_types' => [
        'A',      // IPv4 addresses
        'AAAA',   // IPv6 addresses
        'CNAME',  // Canonical name
        'MX',     // Mail exchange
        'TXT',    // Text records
        'NS',     // Nameservers
        'SOA',    // Start of authority
        'PTR',    // Pointer records
    ],

    // DNS Lookup Settings
    'dns_timeout' => env('DNS_TIMEOUT', 10), // seconds
    'dns_cache_duration' => env('DNS_CACHE_DURATION', 300), // seconds (5 minutes)

    // Module Settings
    'module_name' => 'ServerManager',
    'version' => '1.0.0',

    // Feature Flags
    'features' => [
        'dns_lookup' => true,
        'dns_health_check' => true,
        'whois_lookup' => false, // Set to true if you want WHOIS functionality
        'ssl_checker' => false,  // Set to true if you want SSL certificate checking
    ],
];
