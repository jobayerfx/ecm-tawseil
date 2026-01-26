<?php

namespace Modules\ServerManager\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class DnsLookupService
{
    protected $apiKey;
    protected $baseUrl;

        public function __construct()
    {
        // Load configuration from the module config
        $this->apiKey = config('servermanager.dns_api_key', null);
        $this->baseUrl = config('servermanager.dns_api_url', 'https://dns.google.com/resolve');
    }

    /**
     * Get DNS records for a domain
     */
    public function getDnsRecords($domain)
    {
        try {
            // Clean the domain name
            $domain = $this->cleanDomain($domain);

            // Get various DNS records
            $records = [
                'A' => $this->getARecords($domain),
                'AAAA' => $this->getAAAARecords($domain),
                'CNAME' => $this->getCNAMERecords($domain),
                'MX' => $this->getMXRecords($domain),
                'TXT' => $this->getTXTRecords($domain),
                'NS' => $this->getNSRecords($domain),
                'SOA' => $this->getSOARecords($domain),
                'PTR' => $this->getPTRRecords($domain),
            ];

            // Calculate summary statistics
            $summary = $this->calculateSummary($records);

            return [
                'success' => true,
                'domain' => $domain,
                'records' => $records,
                'summary' => $summary,
                'timestamp' => now()->toISOString(),
            ];

        } catch (Exception $e) {
            Log::error('DNS lookup failed for domain: ' . $domain, [
                'error' => $e->getMessage(),
                'domain' => $domain
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'domain' => $domain,
                'timestamp' => now()->toISOString(),
            ];
        }
    }

    /**
     * Get A records (IPv4 addresses)
     */
    protected function getARecords($domain)
    {
        return $this->queryDNS($domain, 'A');
    }

    /**
     * Get AAAA records (IPv6 addresses)
     */
    protected function getAAAARecords($domain)
    {
        return $this->queryDNS($domain, 'AAAA');
    }

    /**
     * Get CNAME records
     */
    protected function getCNAMERecords($domain)
    {
        return $this->queryDNS($domain, 'CNAME');
    }

    /**
     * Get MX records (mail servers)
     */
    protected function getMXRecords($domain)
    {
        return $this->queryDNS($domain, 'MX');
    }

    /**
     * Get TXT records
     */
    protected function getTXTRecords($domain)
    {
        return $this->queryDNS($domain, 'TXT');
    }

    /**
     * Get NS records (nameservers)
     */
    protected function getNSRecords($domain)
    {
        return $this->queryDNS($domain, 'NS');
    }

    /**
     * Get SOA records
     */
    protected function getSOARecords($domain)
    {
        return $this->queryDNS($domain, 'SOA');
    }

    /**
     * Get PTR records
     */
    protected function getPTRRecords($domain)
    {
        return $this->queryDNS($domain, 'PTR');
    }

    /**
     * Query DNS using Google DNS API
     */
    protected function queryDNS($domain, $type)
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl, [
                'name' => $domain,
                'type' => $type,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['Answer'])) {
                    return array_map(function($answer) {
                        return [
                            'name' => $answer['name'],
                            'type' => $answer['type'],
                            'ttl' => $answer['TTL'],
                            'data' => $answer['data'],
                        ];
                    }, $data['Answer']);
                }
            }

            // If Google DNS API fails, try Cloudflare DNS API
            return $this->queryCloudflareDNS($domain, $type);
        } catch (Exception $e) {
            Log::warning("Google DNS API failed for {$domain} type {$type}: " . $e->getMessage());
            return $this->queryCloudflareDNS($domain, $type);
        }
    }

    /**
     * Query DNS using Cloudflare DNS API
     */
    protected function queryCloudflareDNS($domain, $type)
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'application/dns-json',
                ])
                ->get('https://cloudflare-dns.com/dns-query', [
                    'name' => $domain,
                    'type' => $type,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['Answer'])) {
                    return array_map(function($answer) {
                        return [
                            'name' => $answer['name'],
                            'type' => $answer['type'],
                            'ttl' => $answer['TTL'],
                            'data' => $answer['data'],
                        ];
                    }, $data['Answer']);
                }
            }

            // If Cloudflare fails, try OpenDNS API
            return $this->queryOpenDNS($domain, $type);
        } catch (Exception $e) {
            Log::warning("Cloudflare DNS API failed for {$domain} type {$type}: " . $e->getMessage());
            return $this->queryOpenDNS($domain, $type);
        }
    }

    /**
     * Query DNS using OpenDNS API
     */
    protected function queryOpenDNS($domain, $type)
    {
        try {
            $response = Http::timeout(10)->get('https://dns.opendns.com/resolve', [
                'name' => $domain,
                'type' => $type,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['Answer'])) {
                    return array_map(function($answer) {
                        return [
                            'name' => $answer['name'],
                            'type' => $answer['type'],
                            'ttl' => $answer['TTL'],
                            'data' => $answer['data'],
                        ];
                    }, $data['Answer']);
                }
            }

            // If all APIs fail, return empty array
            Log::error("All DNS APIs failed for {$domain} type {$type}");
            return [];
        } catch (Exception $e) {
            Log::error("OpenDNS API failed for {$domain} type {$type}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clean domain name
     */
    protected function cleanDomain($domain)
    {
        // Remove protocol if present
        $domain = preg_replace('/^https?:\/\//', '', $domain);

        // Remove path if present
        $domain = parse_url($domain, PHP_URL_HOST) ?: $domain;

        // Remove www. prefix if present
        $domain = preg_replace('/^www\./', '', $domain);

        return strtolower(trim($domain));
    }

    /**
     * Calculate summary statistics
     */
    protected function calculateSummary($records)
    {
        $totalRecords = 0;
        $nameserverCount = 0;
        $mailServerCount = 0;
        $ipCount = 0;

        foreach ($records as $type => $typeRecords) {
            $totalRecords += count($typeRecords);

            switch ($type) {
                case 'NS':
                    $nameserverCount = count($typeRecords);
                    break;
                case 'MX':
                    $mailServerCount = count($typeRecords);
                    break;
                case 'A':
                case 'AAAA':
                    $ipCount += count($typeRecords);
                    break;
            }
        }

        return [
            'total_records' => $totalRecords,
            'nameserver_count' => $nameserverCount,
            'mail_server_count' => $mailServerCount,
            'ip_count' => $ipCount,
        ];
    }

    /**
     * Get WHOIS information (optional)
     */
    public function getWhoisInfo($domain)
    {
        try {
            // You can integrate with WHOIS APIs here
            // For now, return basic info
            return [
                'success' => true,
                'registrar' => 'Unknown',
                'creation_date' => null,
                'expiry_date' => null,
                'updated_date' => null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if domain is accessible
     */
    public function checkDomainAccessibility($domain)
    {
        try {
            $response = Http::timeout(5)->get("http://{$domain}");
            return [
                'success' => true,
                'status_code' => $response->status(),
                'accessible' => $response->successful(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'accessible' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get formatted DNS records for display
     */
    public function getFormattedDnsRecords($domain)
    {
        $dnsData = $this->getDnsRecords($domain);

        if (!$dnsData['success']) {
            return $dnsData;
        }

        // Format records for display
        $formattedRecords = [];
        foreach ($dnsData['records'] as $type => $records) {
            if (!empty($records)) {
                $formattedRecords[$type] = array_map(function($record) {
                    return [
                        'name' => $record['name'],
                        'type' => $record['type'],
                        'ttl' => $record['ttl'],
                        'data' => $record['data'],
                        'formatted_data' => $this->formatRecordData($record['type'], $record['data']),
                    ];
                }, $records);
            }
        }

        return [
            'success' => true,
            'domain' => $domain,
            'records' => $formattedRecords,
            'summary' => $dnsData['summary'],
            'timestamp' => $dnsData['timestamp'],
        ];
    }

    /**
     * Check DNS health for a domain
     */
    public function checkDnsHealth($domain)
    {
        $dnsData = $this->getDnsRecords($domain);
        $accessibility = $this->checkDomainAccessibility($domain);

        $health = [
            'domain' => $domain,
            'dns_resolution' => $dnsData['success'],
            'web_accessibility' => $accessibility['accessible'],
            'issues' => [],
            'recommendations' => [],
        ];

        // Check for common issues
        if (!$dnsData['success']) {
            $health['issues'][] = 'DNS resolution failed';
            $health['recommendations'][] = 'Check domain configuration and nameservers';
        }

        if (!$accessibility['accessible']) {
            $health['issues'][] = 'Website not accessible';
            $health['recommendations'][] = 'Check web server configuration and firewall settings';
        }

        if (empty($dnsData['records']['A']) && empty($dnsData['records']['AAAA'])) {
            $health['issues'][] = 'No IP address records found';
            $health['recommendations'][] = 'Add A or AAAA records pointing to your web server';
        }

        if (empty($dnsData['records']['MX'])) {
            $health['issues'][] = 'No mail server records found';
            $health['recommendations'][] = 'Add MX records for email functionality';
        }

        return $health;
    }

    /**
     * Format record data for display
     */
    protected function formatRecordData($type, $data)
    {
        switch ($type) {
            case 'MX':
                // MX records have priority and server
                if (preg_match('/(\d+)\s+(.+)/', $data, $matches)) {
                    return "Priority: {$matches[1]}, Server: {$matches[2]}";
                }
                return $data;

            case 'SOA':
                // SOA records have multiple parts
                $parts = explode(' ', $data);
                if (count($parts) >= 7) {
                    return "Primary NS: {$parts[0]}, Admin: {$parts[1]}, Serial: {$parts[2]}";
                }
                return $data;

            case 'TXT':
                // TXT records might be quoted
                return trim($data, '"');

            default:
                return $data;
        }
    }
}
