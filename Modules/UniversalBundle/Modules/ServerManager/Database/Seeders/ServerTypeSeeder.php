<?php

namespace Modules\ServerManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

class ServerTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $this->seedServerTypesForCompany($company->id);
        }
    }

    /**
     * Seed server types for a specific company
     */
    public function seedServerTypesForCompany(int $companyId): void
    {
        // Set the company context for the seeder
        $company = Company::find($companyId);

        $serverTypes = [
            // Shared Hosting
            [
                'name' => 'Shared Hosting',
                'slug' => 'shared-hosting',
                'description' => 'Multiple websites hosted on a single server with shared resources. Cost-effective for small to medium websites.',
                'status' => 'active',
            ],
            [
                'name' => 'Shared Hosting - Basic',
                'slug' => 'shared-hosting-basic',
                'description' => 'Entry-level shared hosting with limited resources, suitable for small personal websites.',
                'status' => 'active',
            ],
            [
                'name' => 'Shared Hosting - Business',
                'slug' => 'shared-hosting-business',
                'description' => 'Business-grade shared hosting with enhanced performance and security features.',
                'status' => 'active',
            ],
            [
                'name' => 'Shared Hosting - Premium',
                'slug' => 'shared-hosting-premium',
                'description' => 'High-performance shared hosting with advanced features and priority support.',
                'status' => 'active',
            ],

            // VPS Hosting
            [
                'name' => 'VPS Hosting',
                'slug' => 'vps-hosting',
                'description' => 'Virtual Private Server with dedicated resources and full root access. Ideal for growing websites.',
                'status' => 'active',
            ],
            [
                'name' => 'VPS Hosting - Managed',
                'slug' => 'vps-hosting-managed',
                'description' => 'Fully managed VPS with server administration handled by the hosting provider.',
                'status' => 'active',
            ],
            [
                'name' => 'VPS Hosting - Unmanaged',
                'slug' => 'vps-hosting-unmanaged',
                'description' => 'Self-managed VPS where you have full control over server configuration.',
                'status' => 'active',
            ],
            [
                'name' => 'VPS Hosting - SSD',
                'slug' => 'vps-hosting-ssd',
                'description' => 'VPS with SSD storage for enhanced performance and faster data access.',
                'status' => 'active',
            ],

            // Dedicated Hosting
            [
                'name' => 'Dedicated Server',
                'slug' => 'dedicated-server',
                'description' => 'Physical server dedicated to a single client with full control and maximum performance.',
                'status' => 'active',
            ],
            [
                'name' => 'Dedicated Server - Managed',
                'slug' => 'dedicated-server-managed',
                'description' => 'Fully managed dedicated server with professional administration and support.',
                'status' => 'active',
            ],
            [
                'name' => 'Dedicated Server - Unmanaged',
                'slug' => 'dedicated-server-unmanaged',
                'description' => 'Self-managed dedicated server with complete control over hardware and software.',
                'status' => 'active',
            ],
            [
                'name' => 'Dedicated Server - High Performance',
                'slug' => 'dedicated-server-high-performance',
                'description' => 'High-performance dedicated server with premium hardware and optimized configuration.',
                'status' => 'active',
            ],

            // Cloud Hosting
            [
                'name' => 'Cloud Hosting',
                'slug' => 'cloud-hosting',
                'description' => 'Scalable hosting solution using cloud infrastructure with pay-as-you-go pricing.',
                'status' => 'active',
            ],
            [
                'name' => 'Cloud VPS',
                'slug' => 'cloud-vps',
                'description' => 'Virtual private server hosted on cloud infrastructure with scalable resources.',
                'status' => 'active',
            ],
            [
                'name' => 'Cloud Dedicated',
                'slug' => 'cloud-dedicated',
                'description' => 'Dedicated server resources in a cloud environment with high availability.',
                'status' => 'active',
            ],
            [
                'name' => 'Multi-Cloud Hosting',
                'slug' => 'multi-cloud-hosting',
                'description' => 'Hosting distributed across multiple cloud providers for redundancy and performance.',
                'status' => 'active',
            ],

            // WordPress Hosting
            [
                'name' => 'WordPress Hosting',
                'slug' => 'wordpress-hosting',
                'description' => 'Specialized hosting optimized for WordPress websites with pre-installed WordPress.',
                'status' => 'active',
            ],
            [
                'name' => 'WordPress Hosting - Managed',
                'slug' => 'wordpress-hosting-managed',
                'description' => 'Fully managed WordPress hosting with automatic updates, backups, and security.',
                'status' => 'active',
            ],
            [
                'name' => 'WordPress Hosting - WooCommerce',
                'slug' => 'wordpress-hosting-woocommerce',
                'description' => 'WordPress hosting optimized for WooCommerce e-commerce websites.',
                'status' => 'active',
            ],

            // Application Hosting
            [
                'name' => 'Application Hosting',
                'slug' => 'application-hosting',
                'description' => 'Hosting specialized for web applications with support for various programming languages.',
                'status' => 'active',
            ],
            [
                'name' => 'Node.js Hosting',
                'slug' => 'nodejs-hosting',
                'description' => 'Hosting optimized for Node.js applications with built-in Node.js support.',
                'status' => 'active',
            ],
            [
                'name' => 'Python Hosting',
                'slug' => 'python-hosting',
                'description' => 'Hosting with Python support for Django, Flask, and other Python frameworks.',
                'status' => 'active',
            ],
            [
                'name' => 'PHP Hosting',
                'slug' => 'php-hosting',
                'description' => 'Hosting optimized for PHP applications with various PHP versions support.',
                'status' => 'active',
            ],
            [
                'name' => 'Java Hosting',
                'slug' => 'java-hosting',
                'description' => 'Hosting with Java support for enterprise applications and web services.',
                'status' => 'active',
            ],
            [
                'name' => 'Ruby Hosting',
                'slug' => 'ruby-hosting',
                'description' => 'Hosting with Ruby support for Ruby on Rails and other Ruby applications.',
                'status' => 'active',
            ],

            // Database Hosting
            [
                'name' => 'Database Hosting',
                'slug' => 'database-hosting',
                'description' => 'Dedicated hosting for database servers with optimized performance.',
                'status' => 'active',
            ],
            [
                'name' => 'MySQL Hosting',
                'slug' => 'mysql-hosting',
                'description' => 'Hosting optimized for MySQL database servers with high availability.',
                'status' => 'active',
            ],
            [
                'name' => 'PostgreSQL Hosting',
                'slug' => 'postgresql-hosting',
                'description' => 'Hosting optimized for PostgreSQL database servers with advanced features.',
                'status' => 'active',
            ],
            [
                'name' => 'MongoDB Hosting',
                'slug' => 'mongodb-hosting',
                'description' => 'Hosting optimized for MongoDB NoSQL database with scalable storage.',
                'status' => 'active',
            ],

            // Email Hosting
            [
                'name' => 'Email Hosting',
                'slug' => 'email-hosting',
                'description' => 'Dedicated hosting for email servers with spam protection and security.',
                'status' => 'active',
            ],
            [
                'name' => 'Exchange Hosting',
                'slug' => 'exchange-hosting',
                'description' => 'Microsoft Exchange hosting for business email and collaboration.',
                'status' => 'active',
            ],
            [
                'name' => 'Google Workspace Hosting',
                'slug' => 'google-workspace-hosting',
                'description' => 'Google Workspace hosting for Gmail, Drive, and collaboration tools.',
                'status' => 'active',
            ],

            // CDN and Edge
            [
                'name' => 'CDN Hosting',
                'slug' => 'cdn-hosting',
                'description' => 'Content Delivery Network hosting for global content distribution.',
                'status' => 'active',
            ],
            [
                'name' => 'Edge Computing',
                'slug' => 'edge-computing',
                'description' => 'Edge computing hosting for processing data closer to end users.',
                'status' => 'active',
            ],

            // Game Hosting
            [
                'name' => 'Game Server Hosting',
                'slug' => 'game-server-hosting',
                'description' => 'Specialized hosting for online game servers with low latency.',
                'status' => 'active',
            ],
            [
                'name' => 'Minecraft Server Hosting',
                'slug' => 'minecraft-server-hosting',
                'description' => 'Hosting optimized for Minecraft game servers with mod support.',
                'status' => 'active',
            ],

            // Development and Testing
            [
                'name' => 'Development Hosting',
                'slug' => 'development-hosting',
                'description' => 'Hosting environment for development and testing purposes.',
                'status' => 'active',
            ],
            [
                'name' => 'Staging Hosting',
                'slug' => 'staging-hosting',
                'description' => 'Staging environment hosting for testing before production deployment.',
                'status' => 'active',
            ],
            [
                'name' => 'Testing Environment',
                'slug' => 'testing-environment',
                'description' => 'Dedicated hosting for software testing and quality assurance.',
                'status' => 'active',
            ],

            // Backup and Storage
            [
                'name' => 'Backup Hosting',
                'slug' => 'backup-hosting',
                'description' => 'Dedicated hosting for backup storage and disaster recovery.',
                'status' => 'active',
            ],
            [
                'name' => 'File Storage Hosting',
                'slug' => 'file-storage-hosting',
                'description' => 'Hosting optimized for file storage and sharing services.',
                'status' => 'active',
            ],

            // Enterprise Solutions
            [
                'name' => 'Enterprise Hosting',
                'slug' => 'enterprise-hosting',
                'description' => 'Enterprise-grade hosting with advanced security and compliance features.',
                'status' => 'active',
            ],
            [
                'name' => 'Colocation Hosting',
                'slug' => 'colocation-hosting',
                'description' => 'Colocation services where you own the hardware but use provider infrastructure.',
                'status' => 'active',
            ],
            [
                'name' => 'Hybrid Hosting',
                'slug' => 'hybrid-hosting',
                'description' => 'Combination of on-premises and cloud hosting for flexible infrastructure.',
                'status' => 'active',
            ],

            // Specialized Hosting
            [
                'name' => 'Reseller Hosting',
                'slug' => 'reseller-hosting',
                'description' => 'Hosting package for resellers to sell hosting services to their clients.',
                'status' => 'active',
            ],
            [
                'name' => 'White Label Hosting',
                'slug' => 'white-label-hosting',
                'description' => 'Hosting services branded under your own company name.',
                'status' => 'active',
            ],
            [
                'name' => 'High Availability Hosting',
                'slug' => 'high-availability-hosting',
                'description' => 'Hosting with redundant systems for maximum uptime and reliability.',
                'status' => 'active',
            ],
            [
                'name' => 'Load Balanced Hosting',
                'slug' => 'load-balanced-hosting',
                'description' => 'Hosting with load balancing for distributing traffic across multiple servers.',
                'status' => 'active',
            ],
        ];

        foreach ($serverTypes as $serverTypeData) {
            DB::table('server_types')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'slug' => $serverTypeData['slug']
                ],
                [
                    'name' => $serverTypeData['name'],
                    'description' => $serverTypeData['description'],
                    'status' => $serverTypeData['status'],
                ]
            );
        }
    }
}
