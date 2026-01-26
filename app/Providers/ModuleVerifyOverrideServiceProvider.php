<?php

namespace App\Providers;

use Froiden\Envato\Functions\EnvatoUpdate;
use Illuminate\Support\ServiceProvider;

class ModuleVerifyOverrideServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Override curl response handler safely
        EnvatoUpdate::curl( function ($postData) {

            $ch = curl_init('https://envato.froid.works/verify-module');

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($postData),
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                return [
                    'status' => 'error',
                    'message' => 'Verification server unreachable'
                ];
            }

            curl_close($ch);

            $decoded = json_decode($result, true);

            if (!is_array($decoded)) {
                return [
                    'status' => 'error',
                    'message' => 'License server rejected this domain'
                ];
            }

            return $decoded;
        });
    }
}
