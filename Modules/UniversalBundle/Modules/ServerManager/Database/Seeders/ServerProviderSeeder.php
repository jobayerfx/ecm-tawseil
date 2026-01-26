<?php

namespace Modules\ServerManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

class ServerProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $this->seedProvidersForCompany($company->id);
        }
    }

    /**
     * Seed providers for a specific company
     */
    public function seedProvidersForCompany(int $companyId): void
    {
        // Set the company context for the seeder
        $company = Company::find($companyId);

        $providers = [
            // Cloud Hosting Providers
            [
                'name' => 'Amazon Web Services (AWS)',
                'url' => 'https://aws.amazon.com',
                'type' => 'hosting',
                'description' => 'Leading cloud computing platform offering EC2, S3, RDS, and 200+ services',
                'status' => 'active',
            ],
            [
                'name' => 'Google Cloud Platform (GCP)',
                'url' => 'https://cloud.google.com',
                'type' => 'hosting',
                'description' => 'Google\'s cloud computing platform with Compute Engine, Cloud Storage, and AI services',
                'status' => 'active',
            ],
            [
                'name' => 'Microsoft Azure',
                'url' => 'https://azure.microsoft.com',
                'type' => 'hosting',
                'description' => 'Microsoft\'s cloud platform with virtual machines, databases, and enterprise services',
                'status' => 'active',
            ],
            [
                'name' => 'DigitalOcean',
                'url' => 'https://digitalocean.com',
                'type' => 'hosting',
                'description' => 'Developer-friendly cloud platform with droplets, managed databases, and Kubernetes',
                'status' => 'active',
            ],
            [
                'name' => 'Linode',
                'url' => 'https://linode.com',
                'type' => 'hosting',
                'description' => 'Cloud hosting provider with high-performance SSD servers and global data centers',
                'status' => 'active',
            ],
            [
                'name' => 'Vultr',
                'url' => 'https://vultr.com',
                'type' => 'hosting',
                'description' => 'High-performance cloud infrastructure with global data centers and competitive pricing',
                'status' => 'active',
            ],
            [
                'name' => 'Heroku',
                'url' => 'https://heroku.com',
                'type' => 'hosting',
                'description' => 'Platform as a Service (PaaS) for deploying and managing applications',
                'status' => 'active',
            ],
            [
                'name' => 'Vercel',
                'url' => 'https://vercel.com',
                'type' => 'hosting',
                'description' => 'Platform for static sites and serverless functions with automatic deployments',
                'status' => 'active',
            ],
            [
                'name' => 'Netlify',
                'url' => 'https://netlify.com',
                'type' => 'hosting',
                'description' => 'All-in-one platform for web projects with continuous deployment and CDN',
                'status' => 'active',
            ],

            // Traditional Web Hosting Providers
            [
                'name' => 'Bluehost',
                'url' => 'https://bluehost.com',
                'type' => 'both',
                'description' => 'Popular web hosting provider with shared hosting, VPS, and domain registration',
                'status' => 'active',
            ],
            [
                'name' => 'HostGator',
                'url' => 'https://hostgator.com',
                'type' => 'both',
                'description' => 'Web hosting company offering shared, VPS, dedicated hosting and domains',
                'status' => 'active',
            ],
            [
                'name' => 'GoDaddy',
                'url' => 'https://godaddy.com',
                'type' => 'both',
                'description' => 'World\'s largest domain registrar and web hosting provider',
                'status' => 'active',
            ],
            [
                'name' => 'Namecheap',
                'url' => 'https://namecheap.com',
                'type' => 'both',
                'description' => 'Domain registrar and web hosting provider with competitive pricing',
                'status' => 'active',
            ],
            [
                'name' => 'SiteGround',
                'url' => 'https://siteground.com',
                'type' => 'hosting',
                'description' => 'Web hosting provider known for excellent customer support and performance',
                'status' => 'active',
            ],
            [
                'name' => 'A2 Hosting',
                'url' => 'https://a2hosting.com',
                'type' => 'hosting',
                'description' => 'High-performance web hosting with SSD storage and turbo servers',
                'status' => 'active',
            ],
            [
                'name' => 'InMotion Hosting',
                'url' => 'https://inmotionhosting.com',
                'type' => 'hosting',
                'description' => 'Web hosting provider with business-focused hosting solutions',
                'status' => 'active',
            ],
            [
                'name' => 'DreamHost',
                'url' => 'https://dreamhost.com',
                'type' => 'both',
                'description' => 'Web hosting and domain registration with unlimited bandwidth and storage',
                'status' => 'active',
            ],
            [
                'name' => 'Hostinger',
                'url' => 'https://hostinger.com',
                'type' => 'both',
                'description' => 'Budget-friendly web hosting with domain registration and website builder',
                'status' => 'active',
            ],
            [
                'name' => 'GreenGeeks',
                'url' => 'https://greengeeks.com',
                'type' => 'hosting',
                'description' => 'Eco-friendly web hosting provider with renewable energy',
                'status' => 'active',
            ],

            // Domain Registrars
            [
                'name' => 'Google Domains',
                'url' => 'https://domains.google',
                'type' => 'domain',
                'description' => 'Google\'s domain registration service with privacy protection',
                'status' => 'active',
            ],
            [
                'name' => 'Cloudflare Registrar',
                'url' => 'https://cloudflare.com',
                'type' => 'domain',
                'description' => 'Domain registration with no markup pricing and enhanced security',
                'status' => 'active',
            ],
            [
                'name' => 'Hover',
                'url' => 'https://hover.com',
                'type' => 'domain',
                'description' => 'Domain registrar focused on simplicity and customer service',
                'status' => 'active',
            ],
            [
                'name' => 'Porkbun',
                'url' => 'https://porkbun.com',
                'type' => 'domain',
                'description' => 'Domain registrar with competitive pricing and free WHOIS privacy',
                'status' => 'active',
            ],
            [
                'name' => 'Namesilo',
                'url' => 'https://namesilo.com',
                'type' => 'domain',
                'description' => 'Domain registrar with low prices and free WHOIS privacy protection',
                'status' => 'active',
            ],
            [
                'name' => 'Uniregistry',
                'url' => 'https://uniregistry.com',
                'type' => 'domain',
                'description' => 'Domain registrar with marketplace for buying and selling domains',
                'status' => 'active',
            ],

            // Managed WordPress Hosting
            [
                'name' => 'WP Engine',
                'url' => 'https://wpengine.com',
                'type' => 'hosting',
                'description' => 'Managed WordPress hosting platform with enterprise-grade performance',
                'status' => 'active',
            ],
            [
                'name' => 'Kinsta',
                'url' => 'https://kinsta.com',
                'type' => 'hosting',
                'description' => 'Premium managed WordPress hosting powered by Google Cloud Platform',
                'status' => 'active',
            ],
            [
                'name' => 'Flywheel',
                'url' => 'https://getflywheel.com',
                'type' => 'hosting',
                'description' => 'Managed WordPress hosting designed for designers and creative agencies',
                'status' => 'active',
            ],
            [
                'name' => 'Pressable',
                'url' => 'https://pressable.com',
                'type' => 'hosting',
                'description' => 'Managed WordPress hosting with WooCommerce optimization',
                'status' => 'active',
            ],

            // Enterprise Hosting
            [
                'name' => 'Rackspace',
                'url' => 'https://rackspace.com',
                'type' => 'hosting',
                'description' => 'Managed cloud computing company with enterprise hosting solutions',
                'status' => 'active',
            ],
            [
                'name' => 'OVHcloud',
                'url' => 'https://ovhcloud.com',
                'type' => 'hosting',
                'description' => 'European cloud provider with global data centers and enterprise solutions',
                'status' => 'active',
            ],
            [
                'name' => 'Hetzner',
                'url' => 'https://hetzner.com',
                'type' => 'hosting',
                'description' => 'German hosting provider with dedicated servers and cloud hosting',
                'status' => 'active',
            ],
            [
                'name' => 'Scaleway',
                'url' => 'https://scaleway.com',
                'type' => 'hosting',
                'description' => 'European cloud provider with bare metal servers and managed databases',
                'status' => 'active',
            ],

            // CDN and Edge Computing
            [
                'name' => 'Cloudflare',
                'url' => 'https://cloudflare.com',
                'type' => 'hosting',
                'description' => 'CDN, DNS, DDoS protection, and edge computing services',
                'status' => 'active',
            ],
            [
                'name' => 'Fastly',
                'url' => 'https://fastly.com',
                'type' => 'hosting',
                'description' => 'Edge cloud platform with real-time CDN and edge computing',
                'status' => 'active',
            ],
            [
                'name' => 'Akamai',
                'url' => 'https://akamai.com',
                'type' => 'hosting',
                'description' => 'Global CDN and cloud security services for enterprise',
                'status' => 'active',
            ],

            // Email Hosting
            [
                'name' => 'Google Workspace',
                'url' => 'https://workspace.google.com',
                'type' => 'hosting',
                'description' => 'Business email, cloud storage, and productivity tools',
                'status' => 'active',
            ],
            [
                'name' => 'Microsoft 365',
                'url' => 'https://microsoft365.com',
                'type' => 'hosting',
                'description' => 'Business email, Office apps, and cloud services',
                'status' => 'active',
            ],
            [
                'name' => 'Zoho Mail',
                'url' => 'https://zoho.com/mail',
                'type' => 'hosting',
                'description' => 'Business email hosting with custom domain support',
                'status' => 'active',
            ],

            // Database Hosting
            [
                'name' => 'MongoDB Atlas',
                'url' => 'https://mongodb.com/atlas',
                'type' => 'hosting',
                'description' => 'Cloud database service for MongoDB with global clusters',
                'status' => 'active',
            ],
            [
                'name' => 'PlanetScale',
                'url' => 'https://planetscale.com',
                'type' => 'hosting',
                'description' => 'MySQL-compatible serverless database platform',
                'status' => 'active',
            ],
            [
                'name' => 'Supabase',
                'url' => 'https://supabase.com',
                'type' => 'hosting',
                'description' => 'Open source Firebase alternative with PostgreSQL database',
                'status' => 'active',
            ],

            // Additional Cloud Providers
            [
                'name' => 'IBM Cloud',
                'url' => 'https://ibm.com/cloud',
                'type' => 'hosting',
                'description' => 'IBM\'s cloud computing platform with AI and enterprise services',
                'status' => 'active',
            ],
            [
                'name' => 'Oracle Cloud',
                'url' => 'https://oracle.com/cloud',
                'type' => 'hosting',
                'description' => 'Oracle\'s cloud infrastructure with autonomous database services',
                'status' => 'active',
            ],
            [
                'name' => 'Alibaba Cloud',
                'url' => 'https://alibabacloud.com',
                'type' => 'hosting',
                'description' => 'Leading cloud provider in Asia Pacific with global presence',
                'status' => 'active',
            ],
            [
                'name' => 'Tencent Cloud',
                'url' => 'https://cloud.tencent.com',
                'type' => 'hosting',
                'description' => 'Tencent\'s cloud computing platform with gaming and social media focus',
                'status' => 'active',
            ],
        ];

        foreach ($providers as $providerData) {
            DB::table('server_providers')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'name' => $providerData['name']
                ],
                [
                    'url' => $providerData['url'],
                    'type' => $providerData['type'],
                    'description' => $providerData['description'],
                    'status' => $providerData['status'],
                ]
            );
        }
    }
}
