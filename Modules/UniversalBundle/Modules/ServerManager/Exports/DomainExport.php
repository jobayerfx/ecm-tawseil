<?php

namespace Modules\ServerManager\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\ServerManager\Entities\ServerDomain;
use Carbon\Carbon;

class DomainExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $domains;
    protected $startDate;
    protected $endDate;
    protected $exportAll;
    protected $dateFilter;

    public function __construct($startDate = null, $endDate = null, $exportAll = false, $dateFilter = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->exportAll = $exportAll;
        $this->dateFilter = $dateFilter;

        $query = ServerDomain::where('company_id', company()->id)
            ->with(['assignedTo', 'createdBy', 'updatedBy', 'hosting', 'project', 'client']);

        if (!$this->exportAll && $this->startDate && $this->endDate) {
            if ($this->dateFilter == 'created_at') {
                $query->whereBetween('created_at', [$this->startDate->startOfDay(), $this->endDate->endOfDay()]);
            } else if ($this->dateFilter == 'expiry_date') {
                $query->whereBetween('expiry_date', [$this->startDate->startOfDay(), $this->endDate->endOfDay()]);
            }
        }

        $this->domains = $query->get();
    }

    public function collection()
    {
        return $this->domains;
    }

    public function headings(): array
    {
        return [
            'ID',
            __('servermanager::app.domain.domainName'),
            __('servermanager::app.domain.provider'),
            __('servermanager::app.domain.providerUrl'),
            __('servermanager::app.domain.domainType'),
            __('servermanager::app.domain.hosting'),
            __('servermanager::app.domain.registrationDate'),
            __('servermanager::app.domain.expiryDate'),
            __('servermanager::app.domain.username'),
            __('servermanager::app.domain.price'),
            __('servermanager::app.domain.plan'),
            __('servermanager::app.domain.status'),
            __('servermanager::app.domain.registrarUrl'),
            __('servermanager::app.domain.registrarUsername'),
            __('servermanager::app.domain.registrarStatus'),
            __('servermanager::app.domain.project'),
            __('servermanager::app.domain.client'),
            __('servermanager::app.domain.dnsProvider'),
            __('servermanager::app.domain.dnsStatus'),
            __('servermanager::app.domain.nameservers'),
            __('servermanager::app.domain.dnsRecords'),
            __('servermanager::app.domain.autoRenewal'),
            __('servermanager::app.domain.whoisProtection'),
            __('servermanager::app.domain.expiryNotification'),
            __('servermanager::app.domain.notificationDaysBefore'),
            __('servermanager::app.domain.notificationTimeUnit'),
            __('servermanager::app.domain.notes'),
        ];
    }

    public function map($domain): array
    {
        return [
            $domain->id,
            $domain->domain_name,
            $domain->domain_provider,
            $domain->provider_url,
            ucfirst($domain->domain_type),
            $domain->hosting ? $domain->hosting->name : '',
            $domain->registration_date ? $domain->registration_date->format(company()->date_format) : '',
            $domain->expiry_date ? $domain->expiry_date->format(company()->date_format) : '',
            $domain->username,
            $domain->annual_cost,
            $domain->billing_cycle,
            ucfirst($domain->status),
            $domain->registrar_url,
            $domain->registrar_username,
            ucfirst($domain->registrar_status),
            $domain->project ? $domain->project->project_name : '',
            $domain->client ? ($domain->client->company_name ?: $domain->client->user->name) : '',
            $domain->dns_provider,
            ucfirst($domain->dns_status),
            $domain->nameservers,
            $domain->dns_records,
            $domain->auto_renewal == 'enabled' ? 'Yes' : 'No',
            $domain->whois_protection == 'enabled' ? 'Yes' : 'No',
            $domain->expiry_notification ? 'Yes' : 'No',
            $domain->notification_days_before,
            $domain->notification_time_unit,
            $domain->notes,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
