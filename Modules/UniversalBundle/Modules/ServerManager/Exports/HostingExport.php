<?php

namespace Modules\ServerManager\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\ServerManager\Entities\ServerHosting;
use Carbon\Carbon;

class HostingExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $hostings;
    protected $startDate;
    protected $endDate;
    protected $dateFilter;
    protected $exportAll;

    public function __construct($startDate = null, $endDate = null, $exportAll = false, $dateFilter = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->exportAll = $exportAll;
        $this->dateFilter = $dateFilter;

        $query = ServerHosting::where('company_id', company()->id)
            ->with(['assignedTo', 'createdBy', 'updatedBy', 'project', 'client', 'clientDetails']);

        if (!$this->exportAll && $this->startDate && $this->endDate) {
            if ($this->dateFilter == 'purchase_date') {
                $query->whereBetween('purchase_date', [$this->startDate->startOfDay(), $this->endDate->endOfDay()]);
            } else if ($this->dateFilter == 'renewal_date') {
                $query->whereBetween('renewal_date', [$this->startDate->startOfDay(), $this->endDate->endOfDay()]);
            }
        }

        $this->hostings = $query->get();
    }

    public function collection()
    {
        return $this->hostings;
    }

    public function headings(): array
    {
        return [
            'ID',
            __('servermanager::app.hosting.name'),
            __('servermanager::app.hosting.provider'),
            __('servermanager::app.hosting.serverType'),
            __('servermanager::app.hosting.serverLocation'),
            __('servermanager::app.hosting.ipAddress'),
            __('servermanager::app.hosting.cpanelUrl'),
            __('servermanager::app.hosting.username'),
            __('servermanager::app.hosting.purchaseDate'),
            __('servermanager::app.hosting.expiryDate'),
            __('servermanager::app.hosting.price'),
            __('servermanager::app.hosting.plan'),
            __('servermanager::app.hosting.project'),
            __('servermanager::app.hosting.client'),
            // __('servermanager::app.hosting.diskSpace'),
            // __('servermanager::app.hosting.bandwidth'),
            // __('servermanager::app.hosting.databaseLimit'),
            // __('servermanager::app.hosting.emailLimit'),
            __('servermanager::app.hosting.ftpUsername'),
            __('servermanager::app.hosting.sslCertificate'),
            __('servermanager::app.hosting.expiryNotification'),
            __('servermanager::app.hosting.notificationDaysBefore'),
            __('servermanager::app.hosting.notificationTimeUnit'),
            __('servermanager::app.hosting.description'),
        ];
    }

    public function map($hosting): array
    {
        return [
            $hosting->id,
            $hosting->name,
            $hosting->hosting_provider,
            ucfirst($hosting->server_type),
            $hosting->server_location,
            $hosting->ip_address,
            $hosting->cpanel_url,
            $hosting->username,
            $hosting->purchase_date ? $hosting->purchase_date->format(company()->date_format) : '',
            $hosting->renewal_date ? $hosting->renewal_date->format(company()->date_format) : '',
            $hosting->annual_cost,
            $hosting->billing_cycle,
            $hosting->project,
            $hosting->client ? $hosting->clientDetails->company_name : '',
            // $hosting->disk_space,
            // $hosting->bandwidth,
            // $hosting->database_limit,
            // $hosting->email_limit,
            $hosting->ftp_username,
            $hosting->ssl_certificate ? 'Yes' : 'No',
            $hosting->expiry_notification ? 'Yes' : 'No',
            $hosting->notification_days_before,
            $hosting->notification_time_unit,
            $hosting->notes,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
