<?php

return [
    'hosting' => [
        'subject' => 'Hosting Expiry Notification',
        'greeting' => 'Hello :name',
        'message' => 'Your hosting :hostingName with domain :domainName is expiring in :daysUntilExpiry days. Please renew it before :expiryDate.',
        'footer' => 'Thank you for using our services.',
    ],
    'domain' => [
        'subject' => 'Domain Expiry Notification',
        'greeting' => 'Hello :name',
        'message' => 'Your domain :domainName is expiring in :daysUntilExpiry days. Please renew it before :expiryDate.',
        'footer' => 'Thank you for using our services.',
    ]
];