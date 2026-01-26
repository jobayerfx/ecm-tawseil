<?php

namespace Modules\ServerManager\Database\factories;

use Modules\ServerManager\Entities\ServerHosting;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServerHostingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServerHosting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $hostingNames = [
            'Production Server', 'Staging Environment', 'Development Server',
            'Client Website', 'E-commerce Platform', 'Blog Server', 'API Server',
            'Database Server', 'File Server', 'Backup Server', 'Load Balancer',
            'CDN Server', 'Mail Server', 'DNS Server', 'Application Server'
        ];

        $providers = [
            'Amazon Web Services (AWS)' => 'https://aws.amazon.com',
            'Google Cloud Platform (GCP)' => 'https://cloud.google.com',
            'Microsoft Azure' => 'https://azure.microsoft.com',
            'DigitalOcean' => 'https://digitalocean.com',
            'Linode' => 'https://linode.com',
            'Vultr' => 'https://vultr.com',
            'Heroku' => 'https://heroku.com',
            'Vercel' => 'https://vercel.com',
            'Netlify' => 'https://netlify.com',
            'Bluehost' => 'https://bluehost.com',
            'HostGator' => 'https://hostgator.com',
            'SiteGround' => 'https://siteground.com',
            'A2 Hosting' => 'https://a2hosting.com',
            'InMotion Hosting' => 'https://inmotionhosting.com'
        ];

        $serverTypes = [
            'Shared Hosting', 'VPS', 'Dedicated Server', 'Cloud Server',
            'WordPress Hosting', 'E-commerce Hosting', 'Reseller Hosting',
            'Colocation', 'Managed Hosting', 'Unmanaged Hosting'
        ];

        $controlPanels = [
            'cPanel' => 'https://cpanel.com',
            'Plesk' => 'https://plesk.com',
            'DirectAdmin' => 'https://directadmin.com',
            'Webmin' => 'https://webmin.com',
            'ISPConfig' => 'https://ispconfig.org',
            'Froxlor' => 'https://froxlor.org',
            'VestaCP' => 'https://vestacp.com',
            'CyberPanel' => 'https://cyberpanel.net'
        ];

        $sslTypes = [
            'Let\'s Encrypt', 'Comodo SSL', 'DigiCert', 'GeoTrust',
            'GlobalSign', 'Thawte', 'Symantec', 'GoDaddy SSL'
        ];

        // $backupFrequencies = ['daily', 'weekly', 'monthly', 'on-demand'];
        $billingCycles = ['monthly', 'quarterly', 'semi-annually', 'annually'];
        $statuses = ['active', 'suspended', 'pending', 'cancelled', 'expired'];

        $selectedProvider = fake()->randomElement(array_keys($providers));
        $selectedControlPanel = fake()->randomElement(array_keys($controlPanels));

        return [
            'name' => fake()->randomElement($hostingNames),
            'domain_name' => fake()->domainName(),
            'hosting_provider' => $selectedProvider,
            'provider_url' => $providers[$selectedProvider],
            'server_type' => fake()->randomElement($serverTypes),
            'ip_address' => fake()->ipv4(),
            'username' => fake()->userName(),
            'password' => fake()->password(),
            'control_panel' => $selectedControlPanel,
            'control_panel_url' => $controlPanels[$selectedControlPanel],
            'cpanel_url' => 'https://' . fake()->domainName() . ':2083',
            'project' => fake()->optional(0.8)->company(),
            'client' => fake()->optional(0.6)->company(),
            'ftp_host' => fake()->domainName(),
            'ftp_username' => fake()->userName(),
            'ftp_password' => fake()->password(),
            'database_host' => fake()->domainName(),
            'database_name' => fake()->word() . '_db',
            'database_username' => fake()->userName(),
            'database_password' => fake()->password(),
            'purchase_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'renewal_date' => fake()->dateTimeBetween('now', '+2 years'),
            'monthly_cost' => fake()->randomFloat(2, 5, 200),
            'annual_cost' => fake()->randomFloat(2, 60, 2400),
            'billing_cycle' => fake()->randomElement($billingCycles),
            'status' => fake()->randomElement($statuses),
            'notes' => fake()->optional(0.7)->paragraph(),
            'ssl_certificate_info' => fake()->optional(0.8)->sentence(),
            'ssl_certificate' => fake()->optional(0.8)->boolean(),
            'ssl_expiry_date' => fake()->optional(0.8)->dateTimeBetween('now', '+2 years'),
            'ssl_type' => fake()->optional(0.8)->randomElement($sslTypes),
            // 'backup_info' => fake()->optional(0.6)->sentence(),
            // 'backup_frequency' => fake()->optional(0.8)->randomElement($backupFrequencies),
            // 'last_backup_date' => fake()->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'expiry_notification' => fake()->boolean(),
            'notification_days_before' => fake()->randomElement([7, 14, 30, 60]),
            'notification_time_unit' => 'days',
            'server_location' => fake()->city() . ', ' . fake()->country(),
            'disk_space' => fake()->randomElement(['10GB', '25GB', '50GB', '100GB', '250GB', '500GB', '1TB']),
            'bandwidth' => fake()->randomElement(['1TB', '2TB', '5TB', '10TB', 'Unlimited']),
            'database_limit' => fake()->randomElement(['5', '10', '25', '50', '100', 'Unlimited']),
            'email_limit' => fake()->randomElement(['100', '500', '1000', '5000', 'Unlimited']),
        ];
    }
}
