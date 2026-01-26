<?php

namespace Modules\ServerManager\Database\factories;

use Modules\ServerManager\Entities\ServerDomain;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServerDomainFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServerDomain::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $domainTypes = ['com', 'net', 'org', 'io', 'co', 'tech', 'app', 'dev'];
        $domainNames = [
            'example', 'mycompany', 'business', 'startup', 'tech', 'digital',
            'web', 'app', 'service', 'platform', 'solution', 'enterprise',
            'global', 'local', 'online', 'webapp', 'portal', 'dashboard'
        ];

        $providers = [
            'GoDaddy' => 'https://godaddy.com',
            'Namecheap' => 'https://namecheap.com',
            'Google Domains' => 'https://domains.google',
            'Cloudflare' => 'https://cloudflare.com',
            'Hover' => 'https://hover.com',
            'Porkbun' => 'https://porkbun.com',
            'Name.com' => 'https://name.com',
            'Domain.com' => 'https://domain.com'
        ];

        $registrars = [
            'GoDaddy' => 'https://godaddy.com',
            'Namecheap' => 'https://namecheap.com',
            'Google Domains' => 'https://domains.google',
            'Cloudflare' => 'https://cloudflare.com',
            'Hover' => 'https://hover.com',
            'Porkbun' => 'https://porkbun.com'
        ];

        $dnsProviders = [
            'Cloudflare', 'GoDaddy', 'Namecheap', 'Google Cloud DNS',
            'AWS Route 53', 'DigitalOcean', 'Vercel', 'Netlify'
        ];

        $billingCycles = ['monthly', 'quarterly', 'semi-annually', 'annually'];
        $statuses = ['active', 'expired', 'pending', 'suspended', 'transferring'];
        $registrarStatuses = ['active', 'expired', 'pending', 'suspended', 'locked'];
        $dnsStatuses = ['active', 'pending', 'error', 'inactive'];

        $selectedProvider = fake()->randomElement(array_keys($providers));
        $selectedRegistrar = fake()->randomElement(array_keys($registrars));
        $domainName = fake()->randomElement($domainNames) . '.' . fake()->randomElement($domainTypes);

        return [
            'domain_name' => $domainName,
            'domain_provider' => $selectedProvider,
            'provider_url' => $providers[$selectedProvider],
            'domain_type' => fake()->randomElement($domainTypes),
            'registrar' => $selectedRegistrar,
            'registrar_url' => $registrars[$selectedRegistrar],
            'registrar_username' => fake()->userName(),
            'registrar_password' => fake()->password(),
            'registrar_status' => fake()->randomElement($registrarStatuses),
            'registration_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'expiry_date' => fake()->dateTimeBetween('now', '+2 years'),
            'renewal_date' => fake()->dateTimeBetween('now', '+1 year'),
            'username' => fake()->userName(),
            'password' => fake()->password(),
            'annual_cost' => fake()->randomFloat(2, 10, 50),
            'billing_cycle' => fake()->randomElement($billingCycles),
            'status' => fake()->randomElement($statuses),
            'dns_provider' => fake()->randomElement($dnsProviders),
            'dns_status' => fake()->randomElement($dnsStatuses),
            'nameservers' => [
                'ns1.' . fake()->domainName(),
                'ns2.' . fake()->domainName(),
                'ns3.' . fake()->domainName()
            ],
            'dns_records' => [
                [
                    'type' => 'A',
                    'name' => '@',
                    'value' => fake()->ipv4(),
                    'ttl' => 3600
                ],
                [
                    'type' => 'CNAME',
                    'name' => 'www',
                    'value' => '@',
                    'ttl' => 3600
                ],
                [
                    'type' => 'MX',
                    'name' => '@',
                    'value' => 'mail.' . fake()->domainName(),
                    'priority' => 10,
                    'ttl' => 3600
                ]
            ],
            'whois_protection' => fake()->boolean(),
            'auto_renewal' => fake()->boolean(),
            'notes' => fake()->optional(0.7)->paragraph(),
            'expiry_notification' => fake()->boolean(),
            'notification_days_before' => fake()->randomElement([7, 14, 30, 60]),
            'notification_time_unit' => 'days',
        ];
    }
}
